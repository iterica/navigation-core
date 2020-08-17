<?php
declare(strict_types=1);

namespace Iterica\Navigation;

use Exception;
use IteratorAggregate;
use Iterica\Navigation\Event\NavigationBuiltEvent;
use Iterica\Navigation\Exception\ScopeNotFoundException;
use Iterica\Navigation\Extension\NavigationExtensionInterface;
use Iterica\Navigation\Node\Node;
use Iterica\Navigation\Node\NodeInterface;
use Iterica\Navigation\Node\ScopeNode;
use Iterica\Navigation\Request\RequestInterface;
use Iterica\Navigation\Router\RouteResolverInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Contracts\Translation\TranslatorInterface;

class Navigation
{
    /** @var ScopeNode[] */
    private array $scopes = [];

    /** @var EventDispatcherInterface */
    private EventDispatcherInterface $eventDispatcher;

    /** @var PropertyAccessor */
    private PropertyAccessor $propertyAccessor;

    /** @var array|NavigationExtensionInterface[] */
    private array $extensions;

    /** @var callable|null */
    private $optionsConfigurator;

    /** @var callable|null */
    private $expressionConfigurator;

    /** @var ExpressionLanguage */
    private ExpressionLanguage $expressionLanguage;

    /**
     * Navigation constructor.
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->propertyAccessor = new PropertyAccessor();
        $this->expressionLanguage = new ExpressionLanguage();
    }

    /**
     * @param array $scopes
     * @throws Exception
     */
    public function configureScopes(array $scopes): void
    {
        $this->scopes = [];

        foreach ($scopes as $scope => $tree) {
            $scopeNode = $this->addScope($scope);
            $this->buildNavigationTree($scopeNode, $tree, $scopes);

            $this->eventDispatcher->dispatch(
                new NavigationBuiltEvent($scopeNode, $scope)
            );
        }
    }

    /**
     * @param NodeInterface $node
     * @param array $tree
     * @param array $origin
     * @throws Exception
     */
    protected function buildNavigationTree(NodeInterface $node, array $tree, array $origin): void
    {
        foreach ($tree as $key => $child) {
            if ($key === 'includes' && is_string($child)) {
                // Transform to readable propertyaccessor string
                $propertyKey = '['.str_replace('.', '][', $child).']';
                if ($this->propertyAccessor->isReadable($origin, $propertyKey)) {
                    $parts = explode('.', $child);
                    $key = end($parts);
                    $child = $this->propertyAccessor->getValue($origin, $propertyKey);
                } else {
                    throw new Exception('Property path '.$child.' for inclusion of menu is not readable');
                }
            }

            $childNode = $this->createNode($key, $child);

            $parent = $node->addChild($childNode);

            foreach ($this->extensions as $extension) {
                $extension->processNode($childNode);
            }

            if (isset($child['inline']) && is_array($child['inline'])) {
                $parent->setInlineNodes($child['inline']);
            }

            if (isset($child['children']) && is_array($child['children'])) {
                $this->buildNavigationTree($parent, $child['children'], $origin);
            }
        }
    }

    /**
     * @param string $scope
     * @return ScopeNode
     */
    public function addScope(string $scope): ScopeNode
    {
        return $this->scopes[$scope] = new ScopeNode($scope);
    }

    /**
     * @param string $key
     * @param array $config
     * @return Node
     */
    public function createNode(string $key, array $config = []): Node
    {
        return new Node($key, $config, $this->getOptionsConfigurator(), $this->getExpressionConfigurator());
    }

    /**
     * @param string $scope
     * @param string $path
     * @return Node
     * @throws \Exception
     */
    public function getNode(string $scope, string $path): ?Node
    {
        return $this->getScope($scope)->getNodeByPath($path);
    }

    /**
     * @param string $scope
     * @return ScopeNode
     * @throws \Exception
     */
    public function getScope(string $scope): ScopeNode
    {
        if (!isset($this->scopes[$scope])) {
            throw new ScopeNotFoundException($scope);
        }

        return $this->scopes[$scope];
    }

    /**
     * @param string $scope
     * @return Node|null
     * @throws ScopeNotFoundException
     */
    public function getActiveNode(string $scope): ?Node
    {
        return $this->getScope($scope)->getActiveNode();
    }

    /**
     * @return callable
     */
    private function getOptionsConfigurator(): callable
    {
        if ($this->optionsConfigurator === null) {
            $this->optionsConfigurator = function (OptionsResolver $resolver): void {
                foreach ($this->extensions as $extension) {
                    $extension->configureOptions($resolver);
                }
            };
        }

        return $this->optionsConfigurator;
    }

    /**
     * @param IteratorAggregate $extensions
     */
    public function setExtensions(IteratorAggregate $extensions): void
    {
        foreach ($extensions as $extension) {
            $this->extensions[] = $extension;
        }

        $this->optionsConfigurator = null;
        $this->expressionConfigurator = null;
    }

    /**
     * @param NavigationExtensionInterface $extension
     */
    public function addExtension(NavigationExtensionInterface $extension): void
    {
        $this->extensions[] = $extension;
        $this->optionsConfigurator = null;
        $this->expressionConfigurator = null;
    }

    /**
     * @return callable
     */
    public function getExpressionConfigurator(): callable
    {
        if ($this->expressionConfigurator === null) {
            $context = [];

            foreach ($this->extensions as $extension) {
                $context += $extension->getExpressionContext();
                $extension->configureExpressionLanguage($this->expressionLanguage);
            }

            $this->expressionConfigurator = function (&$options) use ($context): void {
                if (is_string($options['hidden'])) {
                    $options['hidden'] = $this->expressionLanguage->evaluate($options['hidden'], $context) !== false;
                }
            };
        }

        return $this->expressionConfigurator;
    }
}
