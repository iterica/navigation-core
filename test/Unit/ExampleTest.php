<?php

declare(strict_types=1);

/**
 * Copyright (c) 2017-2020 Andreas Möller
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/ergebnis/php-library-template
 */

namespace Iterica\Navigation\Test\Unit;

use Iterica\Navigation\Node\ScopeNode;
use PHPUnit\Framework;

/**
 * @internal
 *
 * @covers \Iterica\Navigation\Node\ScopeNode
 */
final class ScopeNodeTest extends Framework\TestCase
{
    public function testInitializeScopeNode(): void
    {
        $scopeNode = new ScopeNode('test');

        parent::assertEquals($scopeNode->getKey(), 'test');
    }
}
