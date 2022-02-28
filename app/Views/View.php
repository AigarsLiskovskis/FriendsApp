<?php

namespace App\Views;

class View
{
    private string $path;
    private array $variables;

    public function __construct(string $path, array $variables = [])
    {

        $this->path = $path;
        $this->variables = $variables;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return array
     */
    public function getVariables(): array
    {
        return $this->variables;
    }


}