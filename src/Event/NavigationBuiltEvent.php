<?php
namespace Iterica\Navigation\Event;

use Iterica\Navigation\Node\ScopeNode;
use Symfony\Contracts\EventDispatcher\Event;

class NavigationBuiltEvent extends Event {
    /**
     * @Event("Iterica\Navigation\Event\NavigationBuiltEvent")
     */
    public const ON_NAVIGATION_BUILT = 'iterica.navigation.built';

    /**
     * @var ScopeNode
     */
    private $rootNode;

    /**
     * @var string
     */
    private $scope;

    public function __construct(ScopeNode $node, $scope)
    {

        $this->rootNode = $node;
        $this->scope = $scope;
    }

    /**
     * @return ScopeNode
     */
    public function getRootNode(): ScopeNode
    {
        return $this->rootNode;
    }

    /**
     * @param ScopeNode $rootNode
     */
    public function setRootNode(ScopeNode $rootNode): void
    {
        $this->rootNode = $rootNode;
    }

    /**
     * @return string
     */
    public function getScope(): string
    {
        return $this->scope;
    }

    /**
     * @param string $scope
     */
    public function setScope(string $scope): void
    {
        $this->scope = $scope;
    }
}
