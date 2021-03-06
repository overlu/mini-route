<?php
/**
 * This file is part of Mini.
 * @auth lupeng
 */
declare(strict_types=1);

namespace MiniRoute\DataGenerator;

class MarkBased extends RegexBasedAbstract
{
    protected function getApproxChunkSize(): int
    {
        return 30;
    }

    protected function processChunk($regexToRoutesMap): array
    {
        $routeMap = [];
        $regexes = [];
        $markName = 'a';
        foreach ($regexToRoutesMap as $regex => $route) {
            $regexes[] = $regex . '(*MARK:' . $markName . ')';
            $routeMap[$markName] = [$route->handler, $route->variables];

            ++$markName;
        }

        $regex = '~^(?|' . implode('|', $regexes) . ')$~';
        return ['regex' => $regex, 'routeMap' => $routeMap];
    }
}
