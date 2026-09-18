<?php

namespace Apphalla\TwigInjecterBundle\Contract;

use Symfony\Component\HttpFoundation\Request;

interface TwigInjecterInterface
{
    public function supports(Request $request): bool;

    public function getName(): string;
    public function getValue(): mixed;
}