<?php
/**
 * This file is part of Mini.
 * @auth lupeng
 */
declare(strict_types=1);

namespace MiniRoute\DataGenerator;

class GroupCountBased extends RegexBasedAbstract
{
    protected function getApproxChunkSize(): int
    {
        return 10;
    }

    protected function processChunk($regexToRoutesMap): array
    {
        $routeMap = [];
        $regexes = [];
        $numGroups = 0;
        foreach ($regexToRoutesMap as $regex => $route) {
            $numVariables = count($route->variables);
            $numGroups = max($numGroups, $numVariables);

            $regexes[] = $regex . str_repeat('()', $numGroups - $numVariables);
            $routeMap[$numGroups + 1] = [$route->handler, $route->variables];

            ++$numGroups;
        }

        $regex = '~^(?|' . implode('|', $regexes) . ')$~';
        return ['regex' => $regex, 'routeMap' => $routeMap];
    }
}
