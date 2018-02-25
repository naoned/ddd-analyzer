<?php

declare(strict_types = 1);

namespace Niktux\DDD\Analyzer\Domain\Visitors\Collect;

use PHPUnit\Framework\TestCase;
use Niktux\DDD\Analyzer\Domain\Services\KnowledgeBase;
use Niktux\DDD\Analyzer\Domain\Services\NamespaceInterpreter;
use PhpParser\Node;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Trait_;

class BoundedContextCollectorTest extends TestCase
{
    private
        $base,
        $visitor;

    protected function setUp(): void
    {
        $this->base = new KnowledgeBase();
        $interpreter = new NamespaceInterpreter(0);

        $this->visitor = new BoundedContextCollector($this->base, $interpreter);
    }

    /**
     * @dataProvider providerTestEnter
     */
    public function testEnter(Node $node, bool $expectedAdd)
    {
        $nbBefore = $this->countBC();

        $this->visitor->enterNode(new Namespace_(new Name("BC\\Domain")));
        $this->visitor->enterNode($node);

        $nbAfter = $this->countBC();

        $this->assertSame($expectedAdd, $nbAfter == ($nbBefore+1), "BC found = " . $nbAfter);
    }

    public function providerTestEnter(): array
    {
        $class = new Class_("MyClass");
        $anonymousClass = new Class_(null);
        $interface = new Interface_("MyInterface");
        $trait = new Trait_("MyTrait");
        $abstractClass = new Class_("MyClass", ['flags' => Class_::MODIFIER_ABSTRACT]);

        return [
            'class' => [$class, true],
            'anonymous class' => [$anonymousClass, true],
            'interface' => [$interface, true],
            'trait' => [$trait, true],
            'abstract class' => [$abstractClass, true],
        ];
    }

    public function testEnterNamespaceNotDDD()
    {
        $nbBefore = $this->countBC();

        $this->visitor->enterNode(new Namespace_(new Name("Pony\\Unicorn\\Pegasus\\Horse")));
        $this->visitor->enterNode(new Class_("MyClass"));

        $this->assertSame($nbBefore, $this->countBC());
    }

    public function testEnterWithoutNamespace()
    {
        $nbBefore = $this->countBC();

        $this->visitor->enterNode(new Class_("MyClass"));

        $this->assertSame($nbBefore, $this->countBC());
    }

    private function countBC(): int
    {
        return count($this->base->boundedContexts());;
    }
}
