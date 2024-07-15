<?php

declare(strict_types=1);

namespace DouglasGreen\OptParser\Option;

class Term extends Option
{
    public function __construct(
        string $name,
        string $desc,
        string $argType,
        ?callable $callback = null,
    ) {
        parent::__construct($name, $desc, $callback);
        $this->setArgType($argType);
    }

    #[\Override]
    public function write(): string
    {
        return $this->name . ':' . $this->argType;
    }
}
