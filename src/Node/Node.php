<?php
namespace Iterica\Navigation\Node;

use Iterica\Navigation\Exception\RootNodeNotFoundException;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Node implements NodeInterface
{
    /** @var string $key */
    protected string $key;

    /** @var Node|ScopeNode|NodeInterface $parent */
    protected NodeInterface $parent;

    /** @var bool */
    protected bool $active = false;

    /** @var bool */
    protected bool $activeChild = false;

    /** @var Node[]|array */
    protected array $childNodes = [];

    /** @var Node[]|array */
    protected array $inlineNodes = [];

    /** @var array */
    protected array $options;

    /** @var string|null */
    protected ?string $url = null;

    /**
     * Node constructor.
     * @param string $key
     * @param array $options
     * @param callable $optionsConfigurator
     * @param callable|null $expressionProcessor
     */
    public function __construct(
        string $key,
        array $options,
        callable $optionsConfigurator = null,
        callable $expressionProcessor = null
    ) {
        $this->key = $key;

        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);

        if (is_callable($optionsConfigurator)) {
            $optionsConfigurator($resolver);
        }

        $this->options = $resolver->resolve($options);

        if (is_callable($expressionProcessor)) {
            $expressionProcessor($this->options);
        }
    }

    /**
     * @param Node $child
     * @return Node
     */
    public function addChild(Node $child): Node
    {
        $child->setParent($this);
        $this->childNodes[$child->getKey()] = $child;
        $this->resolveRoot()->addToNodeList($child);

        return $this->childNodes[$child->getKey()];
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
     * @return $this
     */
    public function setKey(string $key): Node
    {
        $this->key = $key;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getLabel(): ?string
    {
        return $this->options['label'];
    }

    /**
     * @param string|null $label
     * @return $this
     */
    public function setLabel(string $label = null)
    {
        $this->options['label'] = $label;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->options['title'] ?? $this->options['label'];
    }

    /**
     * @param string|null $title
     * @return $this
     */
    public function setTitle(string $title = null): Node
    {
        $this->options['title'] = $title;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getButtonClass(): ?string
    {
        return $this->options['buttonClass'];
    }

    /**
     * @param string|null $buttonClass
     * @return Node
     */
    public function setButtonClass(string $buttonClass = null): Node
    {
        $this->options['buttonClass'] = $buttonClass;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getIcon(): ?string
    {
        return $this->options['icon'];
    }

    /**
     * @param string|null $icon
     * @return $this
     */
    public function setIcon(string $icon = null)
    {
        $this->options['icon'] = $icon;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->options['url'];
    }

    public function setUrl($url)
    {
        $this->options['url'] = $url;

        return $this;
    }

    /**
     * @return int
     */
    public function getPriority()
    {
        return $this->options['priority'];
    }

    /**
     * @param int $priority
     * @return $this
     */
    public function setPriority($priority)
    {
        $this->options['priority'] = $priority;

        return $this;
    }

    /**
     * @return Node[]|null
     */
    public function getChildren()
    {
        return $this->childNodes;
    }

    /**
     * @param array $children
     */
    public function setChildren(array $children = null)
    {
        $this->childNodes = $children;
    }

    /**
     * @param string $key
     * @return Node|null
     */
    public function getChild(string $key): ?Node
    {
        return $this->childNodes[$key];
    }

    /**
     * @return bool
     */
    public function hasChildren(): bool
    {
        return count($this->childNodes) > 0;
    }

    /**
     * @return Node|ScopeNode
     */
    public function getParent(): NodeInterface
    {
        return $this->parent;
    }

    /**
     * @param Node|ScopeNode|NodeInterface $parent
     */
    public function setParent(NodeInterface $parent)
    {
        $this->parent = $parent;
    }

    /**
     * @param boolean $hidden
     * @return Node $this
     */
    public function setHidden(bool $hidden)
    {
        $this->options['hidden'] = $hidden;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isHidden(): bool
    {
        if ($this->options['hidden'] === true) {
            return true;
        }

        if ($this->options['url'] !== null) {
            return false;
        }

        if ($this->hasChildren() === true && $this->hasVisibleChildren() === false) {
            return true;
        }

        return false;
    }

    /**
     * @return boolean
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * @param boolean $active
     */
    public function setActive($active)
    {
        $this->active = $active;

        if ($active === true && !($this->getParent() instanceof ScopeNode)) {
            $this->getParent()->setActiveChild(true);
        }
    }

    /**
     * @return boolean
     */
    public function hasActiveChildren()
    {
        return $this->activeChild;
    }

    /**
     * @param boolean $activeChild
     * @return $this
     */
    public function setActiveChild($activeChild = false)
    {
        $this->activeChild = $activeChild;

        if ($activeChild) {
            $this->active = false;
        }

        if (!($this->getParent() instanceof ScopeNode)) {
            $this->getParent()->setActiveChild($activeChild);
        }

        return $this;
    }

    /**
     * @return ScopeNode
     * @throws \Exception
     */
    public function resolveRoot()
    {
        if (!$this->parent || !($this->parent instanceof NodeInterface)) {
            throw new RootNodeNotFoundException('Root node is not resolvable because the parent node is not set');
        }

        return $this->parent->resolveRoot();
    }

    public function getPath()
    {
        return (($parent = $this->parent->getPath()) ? $parent . "." : false) . $this->key;
    }

    /**
     * @return string[]
     */
    public function getInlineNodes()
    {
        return $this->inlineNodes;
    }

    /**
     * @return Node|null
     */
    public function getInlineNode($key): ?Node
    {
        foreach ($this->getInlineNodes() as $inlineNode) {
            $node = $this->resolveRoot()->getNodeByPath($inlineNode);
            if ($node->getKey() === $key) {
                return $node;
            }
        }
    }

    /**
     * Unset an inline node in this node by node key
     *
     * @param $nodeKey
     * @return bool
     */
    public function removeInlineNode($nodeKey): bool
    {
        foreach ($this->inlineNodes as $key => $value) {
            if ($value === $nodeKey) {
                unset($this->inlineNodes[$key]);
                return true;
            }
        }

        return false;
    }

    /**
     * @param array $inlineNodes
     * @return Node
     */
    public function setInlineNodes(array $inlineNodes): Node
    {
        $this->inlineNodes = $inlineNodes;

        return $this;
    }

    /**
     * @param string $inlineNode
     */
    public function addInlineNode($inlineNode): Node
    {
        $this->inlineNodes[] = $inlineNode;
        return $this;
    }

    public function hasVisibleChildren()
    {
        if ($this->childNodes && count($this->childNodes) > 0) {
            foreach ($this->childNodes as $child) {
                if (!$child->isHidden()) {
                    return true;
                }
            }

            return false;
        }

        return false;
    }

    /**
     * @return Node[]|array
     */
    public function getParentNodes()
    {
        $parents = [];

        $parent = $this;
        while (($parent = $parent->getParent()) && !($parent instanceof ScopeNode)) {
            $parents[] = $parent;
        }

        return $parents;
    }

    public function getOption(string $key)
    {
        return $this->options[$key] ?? null;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'label' => null,
            'title' => null,
            'icon' => null,
            'priority' => null,
            'children' => null,
            'hidden' => false,
            'url' => null,
            'inline' => []
        ]);

        $resolver->setRequired([
            'label',
            'url'
        ]);

        $resolver->setAllowedTypes('hidden', ['null', 'bool', 'string']);
        $resolver->setAllowedTypes('label', ['string']);
        $resolver->setAllowedTypes('url', ['string', 'null']);
        $resolver->setAllowedTypes('title', ['string', 'null']);
        $resolver->setAllowedTypes('inline', ['array']);
    }
}
