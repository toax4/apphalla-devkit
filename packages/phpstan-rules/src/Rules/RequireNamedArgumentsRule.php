<?php

declare(strict_types=1);

namespace Apphalla\PHPStanRules\Rules;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements Rule<Node>
 */
final class RequireNamedArgumentsRule implements Rule
{
    private int $minArguments;

    public function __construct(int $minArguments = 2)
    {
        $this->minArguments = $minArguments;
    }

    public function getNodeType(): string
    {
        return Node::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node instanceof FuncCall
            && !$node instanceof MethodCall
            && !$node instanceof StaticCall
        ) {
            return [];
        }

        /** @var Arg[] $args */
        $args = array_filter($node->args, static fn ($arg) => $arg instanceof Arg);

        if (count($args) < $this->minArguments) {
            return [];
        }

        $errors = [];

        foreach ($args as $position => $arg) {
            if ($arg->name !== null) {
                continue;
            }

            if ($arg->unpack) {
                continue;
            }

            $errors[] = RuleErrorBuilder::message(sprintf(
                'Argument #%d n\'est pas nommé',
                $position + 1,
            ))
                ->identifier('apphalla.namedArguments')
                ->line($arg->getStartLine())
                ->build();
        }

        return $errors;
    }
}