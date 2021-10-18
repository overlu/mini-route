<?php
/**
 * This file is part of Mini.
 * @auth lupeng
 */
declare(strict_types=1);

namespace MiniRoute\DataGenerator;

class GroupPosBased extends RegexBasedAbstract
{
    protected function getApproxChunkSize(): int
    {
        return 10;
    }

    protected function processChunk($regexToRoutesMap): array
    {
        $routeMap = [];
        $regexes = [];
        $offset = 1;
        foreach ($regexToRoutesMap as $regex => $route) {
            $regexes[] = $regex;
            $routeMap[$offset] = [$route->handler, $route->variables];

            $offset += count($route->variables);
        }

        $regex = '~^(?:' . implode('|', $regexes) . ')$~';
        return ['regex' => $regex, 'routeMap' => $routeMap];
    }
}
