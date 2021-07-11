<?php
declare(strict_types=1);

namespace Iterica\Navigation\Node;

interface NodeInterface
{
    /**
     * @param Node $child
     * @return Node
     */
    public function addChild(Node $child);

    /**
     * @param string $key
     * @return Node|null
     */
    public function getChild(string $key);

    /**
     * @return Node[]|null
     */
    public function getChildren();

    /**
     * @return bool
     */
    public function hasChildren();

    /**
     * @return ScopeNode
     */
    public function resolveRoot(): ScopeNode;

    /**
     * @return string|null
     */
    public function getPath(): ?string;
}
