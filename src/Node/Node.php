<?php
declare(strict_types=1);

namespace Iterica\Navigation\Node;

use Iterica\Navigation\Exception\RootNodeNotFoundException;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Node implements NodeInterface
{
    /** @var string $key */
    protected string $key;

    /** @var Node|ScopeNode|NodeInterface|null $parent */
    protected ?NodeInterface $parent = null;

    /** @var bool */
    protected bool $active = false;

    /** @var bool */
    protected bool $activeChild = false;

    /** @var Node[]|array */
    protected array $childNodes = [];

    /** @var string[]|array */
    protected array $inlineNodes = [];

    /** @var array */
    protected array $options;

    /**
     * Node constructor.
     * @param string $key
     * @param array $options
     * @param callable|null $optionsConfigurator
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
     * @return self
     */
    public function setKey(string $key): self
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
     * @return self
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
     * @return self
     */
    public function setTitle(string $title = null): self
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
     * @return self
     */
    public function setButtonClass(string $buttonClass = null): self
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
     * @return self
     */
    public function setIcon(string $icon = null): self
    {
        $this->options['icon'] = $icon;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getUrl(): ?string
    {
        return $this->options['url'];
    }

    /**
     * @param string $url
     * @return $this
     */
    public function setUrl(string $url): self
    {
        $this->options['url'] = $url;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getPriority(): ?int
    {
        return $this->options['priority'];
    }

    /**
     * @param int $priority
     * @return self
     */
    public function setPriority(int $priority): self
    {
        $this->options['priority'] = $priority;

        return $this;
    }

    /**
     * @return Node[]|null
     */
    public function getChildren(): ?array
    {
        return $this->childNodes;
    }

    /**
     * @param array $children
     */
    public function setChildren(array $children = null): void
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
     * @return NodeInterface|Node|ScopeNode
     */
    public function getParent(): NodeInterface
    {
        return $this->parent;
    }

    /**
     * @param NodeInterface $parent
     * @return self
     */
    public function setParent(NodeInterface $parent): self
    {
        $this->parent = $parent;

        return $this;
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
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @param boolean $active
     * @return self
     */
    public function setActive($active): self
    {
        $this->active = $active;

        if ($active === true && ($this->getParent() instanceof Node)) {
            $this->getParent()->setActiveChild(true);
        }

        return $this;
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
     * @return self
     */
    public function setActiveChild($activeChild = false)
    {
        $this->activeChild = $activeChild;

        if ($activeChild) {
            $this->active = false;
        }

        if ($this->getParent() instanceof Node) {
            $this->getParent()->setActiveChild($activeChild);
        }

        return $this;
    }

    /**
     * @return ScopeNode
     * @throws RootNodeNotFoundException
     */
    public function resolveRoot(): ScopeNode
    {
        if ($this->parent === null) {
            throw new RootNodeNotFoundException('Root node is not resolvable because the parent node is not set');
        }

        return $this->parent->resolveRoot();
    }

    /**
     * @return string|null
     */
    public function getPath(): ?string
    {
        $parent = $this->parent->getPath();

        return ($parent !== null ? $parent . '.' : '') . $this->key;
    }

    /**
     * @return string[]|array
     */
    public function getInlineNodes(): array
    {
        return $this->inlineNodes;
    }

    /**
     * @var string $key
     * @return Node|null
     */
    public function getInlineNode(string $key): ?Node
    {
        foreach ($this->getInlineNodes() as $inlineNode) {
            $node = $this->resolveRoot()->getNodeByPath($inlineNode);
            if ($node->getKey() === $key) {
                return $node;
            }
        }

        return null;
    }

    /**
     * Unset an inline node in this node by node key
     *
     * @param string $nodeKey
     * @return bool
     */
    public function removeInlineNode(string $nodeKey): bool
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

    /**
     * @return bool
     */
    public function hasVisibleChildren(): bool
    {
        if (count($this->childNodes) > 0) {
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
    public function getParentNodes(): array
    {
        $parents = [];

        $parent = $this;
        while (($parent = $parent->getParent()) && !($parent instanceof ScopeNode)) {
            $parents[] = $parent;
        }

        return $parents;
    }

    /**
     * @param string $key
     * @return mixed|null
     */
    public function getOption(string $key)
    {
        return $this->options[$key] ?? null;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
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
