<?php
namespace Iterica\Navigation\Node;

class ScopeNode implements NodeInterface
{
    /**
     * @var ScopeNode|null
     */
    protected $root;

    /** @var string $key */
    protected string $key;

    /** @var Node[]|null $childNodes */
    protected array $childNodes = [];

    /** @var Node|null */
    protected ?Node $activeNode = null;

    /** @var Node[]|null */
    protected array $nodeList = [];

    /** @var boolean $routesResolved */
    protected bool $routesResolved = false;

    public function __construct($scope)
    {
        $this->key = $scope;
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @param string $key
     */
    public function setKey(string $key)
    {
        $this->key = $key;
    }

    /**
     * @return Node|ScopeNode
     */
    public function getRoot(): ScopeNode
    {
        return $this->root;
    }

    /**
     * @param ScopeNode $root
     * @return ScopeNode
     */
    public function setRoot(ScopeNode $root): ScopeNode
    {
        $this->root = $root;
        return $this;
    }

    public function resolveRoot()
    {
        return $this;
    }

    /**
     * @return Node|ScopeNode|null
     */
    public function getActiveNode()
    {
        foreach ($this->nodeList as $node){
            if ($node->isActive()) {
                return $node;
            }
        }

        return;
    }

    /**
     * @param Node $node
     */
    public function addToNodeList(Node $node)
    {
        $this->nodeList[$node->getPath()] = $node;
        $this->routesResolved = false;
    }

    public function getNodeList()
    {
        return $this->nodeList;
    }

    public function getPath()
    {
        return;
    }

    public function getNodeByPath($path)
    {
        if (!isset($this->nodeList[$path])) {
            throw new \Exception("Node not found in navigation tree with path {$path}");
        }

        return $this->nodeList[$path];
    }

    /**
     * @param Node $child
     * @return Node
     */
    public function addChild(Node $child)
    {
        $child->setParent($this);
        $this->childNodes[$child->getKey()] = $child;
        $this->addToNodeList($child);

        return $this->childNodes[$child->getKey()];
    }

    /**
     * @return Node[]|null
     */
    public function getChildren()
    {
        return $this->childNodes;
    }

    /**
     * @param string $key
     * @return Node
     */
    public function getChild(string $key)
    {
        return $this->childNodes[$key];
    }

    /**
     * @return bool
     */
    public function hasChildren()
    {
        return count($this->childNodes) > 0;
    }
}
