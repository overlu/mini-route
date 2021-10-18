<?php
/**
 * This file is part of Mini.
 * @auth lupeng
 */
declare(strict_types=1);

namespace MiniRoute\Dispatcher;

class MarkBased extends RegexBasedAbstract
{
    public function __construct($data)
    {
        [$this->staticRouteMap, $this->variableRouteData] = $data;
    }

    protected function dispatchVariableRoute(array $routeData, string $uri): array
    {
        foreach ($routeData as $data) {
            if (!preg_match($data['regex'], $uri, $matches)) {
                continue;
            }

            [$handler, $varNames] = $data['routeMap'][$matches['MARK']];

            $vars = [];
            $i = 0;
            foreach ($varNames as $varName) {
                $vars[$varName] = $matches[++$i];
            }
            return [self::FOUND, $handler, $vars];
        }

        return [self::NOT_FOUND];
    }
}
