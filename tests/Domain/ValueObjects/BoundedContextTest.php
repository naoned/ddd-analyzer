<?php

declare(strict_types = 1);

namespace Niktux\DDD\Analyzer\Domain\ValueObjects;

use PHPUnit\Framework\TestCase;

class BoundedContextTest extends TestCase
{
    /**
     * @dataProvider providerTestEquals
     */
    public function testEquals(string $context, string $other, bool $expected)
    {
        $context = new BoundedContext($context);
        $other = new BoundedContext($other);

        $this->assertSame($expected, $context->equals($other));
    }

    public function providerTestEquals()
    {
        return [
            ["Pony", "Pony", true],
            ["Pony", "Unicorn", false],
            ["Unicorn", "Pony", false],
        ];
    }

}