<?php

declare(strict_types = 1);

namespace Niktux\DDD\Analyzer\Domain\ValueObjects;

use PHPUnit\Framework\TestCase;

class InterpretedFQNTest extends TestCase
{
    public function testConcat()
    {
        $from = new InterpretedFQN(
            new FullyQualifiedName('BC\\Domain\\X\\Y\\Z'),
            new BoundedContext('BC'),
            Layer::domain(),
            'X\\Y\\Z'
        );

        $to = $from->concat('A\\B');

        $this->assertTrue($from->boundedContext()->equals($to->boundedContext()));
        $this->assertTrue($from->layer()->equals($to->layer()));

        $this->assertTrue($to->fqn()->equals(new FullyQualifiedName('BC\\Domain\\X\\Y\\Z\\A\\B')));
        $this->assertSame('X\\Y\\Z\\A\\B', $to->relativeName());

        $expected = new InterpretedFQN(
            new FullyQualifiedName('BC\\Domain\\X\\Y\\Z\\A\\B'),
            new BoundedContext('BC'),
            Layer::domain(),
            'X\\Y\\Z\\A\\B'
        );

        $this->assertTrue($expected->equals($to));
    }
}
