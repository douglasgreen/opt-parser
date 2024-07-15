<?php

declare(strict_types=1);

namespace DouglasGreen\OptParser\Option;

class Param extends Option
{
    /**
     * @param list<string> $aliases
     */
    public function __construct(
        string $name,
        string $desc,
        array $aliases,
        string $argType,
        ?callable $callback = null,
    ) {
        parent::__construct($name, $desc, $callback);
        foreach ($aliases as $alias) {
            $this->addAlias($alias);
        }

        $this->setArgType($argType);
    }

    #[\Override]
    public function write(): string
    {
        return $this->hyphenate($this->name) . '=' . $this->argType;
    }
}
