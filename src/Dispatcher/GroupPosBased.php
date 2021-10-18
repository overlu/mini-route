<?php

namespace MiniRoute\Dispatcher;

class GroupPosBased extends RegexBasedAbstract
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

            // find first non-empty match
            for ($i = 1; '' === $matches[$i]; ++$i) {

            }

            [$handler, $varNames] = $data['routeMap'][$i];

            $vars = [];
            foreach ($varNames as $varName) {
                $vars[$varName] = $matches[$i++];
            }
            return [self::FOUND, $handler, $vars];
        }

        return [self::NOT_FOUND];
    }
}
