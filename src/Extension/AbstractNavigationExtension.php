<?php
declare(strict_types=1);

namespace Iterica\Navigation\Extension;

use Iterica\Navigation\Node\Node;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractNavigationExtension implements NavigationExtensionInterface
{
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {

    }

    /**
     * @param Node $node
     */
    public function processNode(Node $node): void
    {

    }

    /**
     * @param ExpressionLanguage $expressionLanguage
     */
    public function configureExpressionLanguage(ExpressionLanguage $expressionLanguage): void
    {

    }

    /**
     * @return array
     */
    public function getExpressionContext(): array
    {
        return [];
    }
}
