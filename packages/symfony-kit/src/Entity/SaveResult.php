<?php

namespace Apphalla\SymfonyKit\Entity;

use Apphalla\SymfonyKit\Enum\SaveAction;

final class SaveResult
{
    public function __construct(
        public readonly object $entity,
        public readonly SaveAction $action,
        public readonly array $changes = [],
    ) {
    }
}
