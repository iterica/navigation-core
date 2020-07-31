<?php
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
}
