<?php

declare(strict_types=1);

namespace FastRoute;

use function preg_match;

class Route implements RouteInterface
{
    /**
     * @var string
     */
    public string $httpMethod;

    /**
     * @var string
     */
    public string $regex;

    /**
     * @var mixed[]
     */
    public array $variables;

    /**
     * @var mixed
     */
    public $handler;

    /**
     * @var bool
     */
    public bool $isStatic = false;

    /**
     * string|null
     */
    protected ?string $name;

    /**
     * @var string|null
     */
    protected ?string $template = null;

    /**
     * @param string $httpMethod
     * @param mixed $handler
     * @param string $regex
     * @param mixed[] $variables
     * @param bool $isStatic
     */
    public function __construct(string $httpMethod, $handler, string $regex, array $variables, bool $isStatic = false, ?string $name = null)
    {
        $this->httpMethod = $httpMethod;
        $this->handler = $handler;
        $this->regex = $regex;
        $this->variables = $variables;
        $this->isStatic = $isStatic;
        $this->name = $name;
    }

    protected function optionalParts(string $string, array $optionalParts = [])
    {
        $optionalRegex = '/\[(.*)]/';
        preg_match($optionalRegex, $string, $match);

        if (isset($match[0])) {
            $optionalParts[] = $match[1];
            $optionalParts += $this->optionalParts($match[1], $optionalParts);
        }

        return $optionalParts;
    }

    /**
     * @param array $vars
     * @return string
     */
    public function reverse(array $vars = []): string
    {
        if ($this->template === null) {
            $this->template = $this->regex;
        }

        $link = $this->template;

        $getVarName = function ($match) {
            $string = substr($match, 1);
            $string = substr($string, 0, -1);
            $pieces = explode(':', $string);

            return $pieces[0];
        };

        $availableVars = array_keys($vars);
        foreach ($this->optionalParts($this->template) as $optionalPart) {
            $optionalVars = [];
            preg_match_all('/{([^}]*.?)}/', $this->template, $matches);
            foreach ($matches[0] as $match) {
                $optionalVars[] = $getVarName($match);
            }

            foreach ($optionalVars as $var) {
                if (!in_array($var, $availableVars, true)) {
                    $link = str_replace('[' . $optionalPart . ']', '', $link);
                    break;
                }
            }
        }

        preg_match_all('/{([^}]*.?)}/', $this->template, $matches);

        foreach ($matches[0] as $match) {
            $name = $getVarName($match);

            if (isset($vars[$name])) {
                $link = str_replace($match, $vars[$name], $link);
            }
        }

        return str_replace(['[', ']'], ['', ''], $link);
    }

    /**
     * @return string|null
     */
    public function name(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return void
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * Tests whether this route matches the given string.
     *
     * @param string $string URI string to match
     */
    public function matches(string $string): bool
    {
        if ($this->isStatic) {
            return $string === $this->regex;
        }

        $regex = '~^' . $this->regex . '$~';

        return (bool) preg_match($regex, $string);
    }

    /**
     * @return mixed
     */
    public function handler()
    {
        return $this->handler;
    }

    public function regex(): string
    {
        return $this->regex;
    }

    /**
     * @return mixed[]
     */
    public function variables(): array
    {
        return $this->variables;
    }

    /**
     * @return bool
     */
    public function isStatic(): bool
    {
        return $this->isStatic;
    }
}
