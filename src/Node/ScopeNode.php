<?php
declare(strict_types=1);

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

    public function __construct(string $scope)
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
    public function setKey(string $key): void
    {
        $this->key = $key;
    }

    /**
     * @return ScopeNode
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

    public function resolveRoot(): ScopeNode
    {
        return $this;
    }

    /**
     * @return Node|null
     */
    public function getActiveNode(): ?Node
    {
        foreach ($this->nodeList as $node){
            if ($node->isActive()) {
                return $node;
            }
        }

        return null;
    }

    /**
     * @param Node $node
     */
    public function addToNodeList(Node $node): void
    {
        $this->nodeList[$node->getPath()] = $node;
    }

    /**
     * @return array|Node[]|null
     */
    public function getNodeList(): ?array
    {
        return $this->nodeList;
    }

    /**
     * @return string|null
     */
    public function getPath(): ?string
    {
        return null;
    }

    /**
     * @param string $path
     * @return Node|null
     * @throws \Exception
     */
    public function getNodeByPath(string $path): ?Node
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
    public function hasChildren(): bool
    {
        return count($this->childNodes) > 0;
    }
}
