<?php
namespace Iterica\Navigation\Node;

use Iterica\Navigation\Exception\NodeNotFoundException;

class ScopeNode implements NodeInterface
{
    /**
     * @var ScopeNode|null
     */
    protected $root;

    /** @var string $key */
    protected string $key;

    /** @var Node[]|array $childNodes */
    protected array $childNodes = [];

    /** @var Node|null */
    protected ?Node $activeNode = null;

    /** @var Node[]|array */
    protected array $nodeList = [];

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
    }

    /**
     * @return array|Node[]|null
     */
    public function getNodeList()
    {
        return $this->nodeList;
    }

    /**
     * @return void
     */
    public function getPath(): void
    {
        return;
    }

    /**
     * @param $path
     * @return Node|mixed
     * @throws \Exception
     */
    public function getNodeByPath($path): ?Node
    {
        if (!isset($this->nodeList[$path])) {
            throw new NodeNotFoundException(sprintf("Node not found in navigation tree with path %s", $path));
        }

        return $this->nodeList[$path];
    }

    /**
     * @param Node $child
     * @return Node
     */
    public function addChild(Node $child): Node
    {
        $child->setParent($this);
        $this->childNodes[$child->getKey()] = $child;
        $this->addToNodeList($child);

        return $this->childNodes[$child->getKey()];
    }

    /**
     * @return Node[]|array
     */
    public function getChildren(): array
    {
        return $this->childNodes;
    }

    /**
     * @param string $key
     * @return Node|null
     */
    public function getChild(string $key): ?Node
    {
        return $this->childNodes[$key] ?? null;
    }

    /**
     * @return bool
     */
    public function hasChildren()
    {
        return count($this->childNodes) > 0;
    }
}
