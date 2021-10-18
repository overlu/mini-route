<?php
/**
 * This file is part of Mini.
 * @auth lupeng
 */
declare(strict_types=1);

namespace MiniRoute;

class Route
{
    /** @var string */
    public string $httpMethod = '';

    /** @var string */
    public string $regex = '';

    /** @var array */
    public array $variables = [];

    /** @var mixed */
    public $handler;

    /**
     * Constructs a route (value object).
     *
     * @param string $httpMethod
     * @param mixed $handler
     * @param string $regex
     * @param array $variables
     */
    public function __construct(string $httpMethod, $handler, string $regex, array $variables)
    {
        $this->httpMethod = $httpMethod;
        $this->handler = $handler;
        $this->regex = $regex;
        $this->variables = $variables;
    }

    /**
     * Tests whether this route matches the given string.
     *
     * @param string $str
     *
     * @return bool
     */
    public function matches(string $str): bool
    {
        $regex = '~^' . $this->regex . '$~';
        return (bool)preg_match($regex, $str);
    }
}
