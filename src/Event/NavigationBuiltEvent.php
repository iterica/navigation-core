<?php
declare(strict_types=1);

namespace Iterica\Navigation\Event;

use Iterica\Navigation\Node\ScopeNode;

final class NavigationBuiltEvent
{
    /**
     * @var ScopeNode
     */
    private ScopeNode $scopeNode;

    /**
     * @var string
     */
    private string $scope;

    public function __construct(ScopeNode $node, string $scope)
    {
        $this->scopeNode = $node;
        $this->scope = $scope;
    }

    /**
     * @return ScopeNode
     */
    public function getScopeNode(): ScopeNode
    {
        return $this->scopeNode;
    }

    /**
     * @return string
     */
    public function getScope(): string
    {
        return $this->scope;
    }
}
