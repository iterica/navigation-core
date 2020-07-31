<?php
namespace Iterica\Navigation\Extension;

use Iterica\Navigation\Node\Node;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\OptionsResolver\OptionsResolver;

interface NavigationExtensionInterface
{
    public function configureOptions(OptionsResolver $resolver): void;

    public function processNode(Node $node): void;

    public function configureExpressionLanguage(ExpressionLanguage $expressionLanguage): void;

    public function getExpressionContext(): array;
}
