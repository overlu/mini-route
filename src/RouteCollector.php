<?php
/**
 * This file is part of Mini.
 * @auth lupeng
 */
declare(strict_types=1);

namespace MiniRoute;

class RouteCollector
{
    /** @var RouteParser */
    protected RouteParser $routeParser;

    /** @var DataGenerator */
    protected DataGenerator $dataGenerator;

    /** @var string */
    protected string $currentGroupPrefix;

    /**
     * Constructs a route collector.
     *
     * @param RouteParser $routeParser
     * @param DataGenerator $dataGenerator
     */
    public function __construct(RouteParser $routeParser, DataGenerator $dataGenerator)
    {
        $this->routeParser = $routeParser;
        $this->dataGenerator = $dataGenerator;
        $this->currentGroupPrefix = '';
    }

    /**
     * Adds a route to the collection.
     *
     * The syntax used in the $route string depends on the used route parser.
     *
     * @param string|string[] $httpMethod
     * @param string $route
     * @param mixed $handler
     */
    public function addRoute($httpMethod, string $route, $handler): void
    {
        $currentGroupPrefix = trim($this->currentGroupPrefix, '/');
        $currentGroupPrefix = $currentGroupPrefix ? '/' . $currentGroupPrefix : '';
        $route = rtrim($currentGroupPrefix . '/' . trim($route, '/'), '/') ?: '/';
        $routeDatas = $this->routeParser->parse($route);
        foreach ((array)$httpMethod as $method) {
            foreach ($routeDatas as $routeData) {
                $this->dataGenerator->addRoute($method, $routeData, $handler);
            }
        }
    }

    /**
     * Create a route group with a common prefix.
     *
     * All routes created in the passed callback will have the given group prefix prepended.
     *
     * @param string $prefix
     * @param callable $callback
     */
    public function addGroup(string $prefix, callable $callback): void
    {
        $previousGroupPrefix = $this->currentGroupPrefix;
        $this->currentGroupPrefix = $previousGroupPrefix . '/' . trim($prefix, '/');
        $callback($this);
        $this->currentGroupPrefix = $previousGroupPrefix;
    }

    /**
     * Adds a GET route to the collection
     *
     * This is simply an alias of $this->addRoute('GET', $route, $handler)
     *
     * @param string $route
     * @param mixed $handler
     */
    public function get(string $route, $handler): void
    {
        $this->addRoute('GET', $route, $handler);
    }

    /**
     * Adds a POST route to the collection
     *
     * This is simply an alias of $this->addRoute('POST', $route, $handler)
     *
     * @param string $route
     * @param mixed $handler
     */
    public function post(string $route, $handler): void
    {
        $this->addRoute('POST', $route, $handler);
    }

    /**
     * Adds a PUT route to the collection
     *
     * This is simply an alias of $this->addRoute('PUT', $route, $handler)
     *
     * @param string $route
     * @param mixed $handler
     */
    public function put(string $route, $handler): void
    {
        $this->addRoute('PUT', $route, $handler);
    }

    /**
     * Adds a DELETE route to the collection
     *
     * This is simply an alias of $this->addRoute('DELETE', $route, $handler)
     *
     * @param string $route
     * @param mixed $handler
     */
    public function delete(string $route, $handler): void
    {
        $this->addRoute('DELETE', $route, $handler);
    }

    /**
     * Adds a PATCH route to the collection
     *
     * This is simply an alias of $this->addRoute('PATCH', $route, $handler)
     *
     * @param string $route
     * @param mixed $handler
     */
    public function patch(string $route, $handler): void
    {
        $this->addRoute('PATCH', $route, $handler);
    }

    /**
     * Adds a HEAD route to the collection
     *
     * This is simply an alias of $this->addRoute('HEAD', $route, $handler)
     *
     * @param string $route
     * @param mixed $handler
     */
    public function head(string $route, $handler): void
    {
        $this->addRoute('HEAD', $route, $handler);
    }

    /**
     * Returns the collected route data, as provided by the data generator.
     *
     * @return array
     */
    public function getData(): array
    {
        return $this->dataGenerator->getData();
    }
}
