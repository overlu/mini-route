<?php
/**
 * This file is part of Mini.
 * @auth lupeng
 */
declare(strict_types=1);

namespace MiniRoute\DataGenerator;

use MiniRoute\BadRouteException;
use MiniRoute\DataGenerator;
use MiniRoute\Route;

abstract class RegexBasedAbstract implements DataGenerator
{
    /** @var mixed[][] */
    protected array $staticRoutes = [];

    /** @var Route[][] */
    protected array $methodToRegexToRoutesMap = [];

    /**
     * @return int
     */
    abstract protected function getApproxChunkSize(): int;

    /**
     * @param $regexToRoutesMap
     * @return mixed[]
     */
    abstract protected function processChunk($regexToRoutesMap): array;

    public function addRoute(string $httpMethod, array $routeData, $handler): void
    {
        if ($this->isStaticRoute($routeData)) {
            $this->addStaticRoute($httpMethod, $routeData, $handler);
        } else {
            $this->addVariableRoute($httpMethod, $routeData, $handler);
        }
    }

    /**
     * @return mixed[]
     */
    public function getData(): array
    {
        if (empty($this->methodToRegexToRoutesMap)) {
            return [$this->staticRoutes, []];
        }

        return [$this->staticRoutes, $this->generateVariableRouteData()];
    }

    /**
     * @return mixed[]
     */
    private function generateVariableRouteData(): array
    {
        $data = [];
        foreach ($this->methodToRegexToRoutesMap as $method => $regexToRoutesMap) {
            $chunkSize = $this->computeChunkSize(count($regexToRoutesMap));
            $chunks = array_chunk($regexToRoutesMap, $chunkSize, true);
            $data[$method] = array_map([$this, 'processChunk'], $chunks);
        }
        return $data;
    }

    /**
     * @param int
     * @return int
     */
    private function computeChunkSize($count): int
    {
        $numParts = max(1, round($count / $this->getApproxChunkSize()));
        return (int)ceil($count / $numParts);
    }

    /**
     * @param mixed[]
     * @return bool
     */
    private function isStaticRoute(array $routeData): bool
    {
        return count($routeData) === 1 && is_string($routeData[0]);
    }

    private function addStaticRoute(string $httpMethod, array $routeData, $handler): void
    {
        $routeStr = $routeData[0];

        if (isset($this->staticRoutes[$httpMethod][$routeStr])) {
            throw new BadRouteException(sprintf(
                'Cannot register two routes matching "%s" for method "%s"',
                $routeStr, $httpMethod
            ));
        }

        if (isset($this->methodToRegexToRoutesMap[$httpMethod])) {
            foreach ($this->methodToRegexToRoutesMap[$httpMethod] as $route) {
                if ($route->matches($routeStr)) {
                    throw new BadRouteException(sprintf(
                        'Static route "%s" is shadowed by previously defined variable route "%s" for method "%s"',
                        $routeStr, $route->regex, $httpMethod
                    ));
                }
            }
        }

        $this->staticRoutes[$httpMethod][$routeStr] = $handler;
    }

    private function addVariableRoute(string $httpMethod, array $routeData, $handler): void
    {
        [$regex, $variables] = $this->buildRegexForRoute($routeData);

        if (isset($this->methodToRegexToRoutesMap[$httpMethod][$regex])) {
            throw new BadRouteException(sprintf(
                'Cannot register two routes matching "%s" for method "%s"',
                $regex, $httpMethod
            ));
        }

        $this->methodToRegexToRoutesMap[$httpMethod][$regex] = new Route(
            $httpMethod, $handler, $regex, $variables
        );
    }

    /**
     * @param mixed[]
     * @return mixed[]
     */
    private function buildRegexForRoute(array $routeData): array
    {
        $regex = '';
        $variables = [];
        foreach ($routeData as $part) {
            if (is_string($part)) {
                $regex .= preg_quote($part, '~');
                continue;
            }

            list($varName, $regexPart) = $part;

            if (isset($variables[$varName])) {
                throw new BadRouteException(sprintf(
                    'Cannot use the same placeholder "%s" twice', $varName
                ));
            }

            if ($this->regexHasCapturingGroups($regexPart)) {
                throw new BadRouteException(sprintf(
                    'Regex "%s" for parameter "%s" contains a capturing group',
                    $regexPart, $varName
                ));
            }

            $variables[$varName] = $varName;
            $regex .= '(' . $regexPart . ')';
        }

        return [$regex, $variables];
    }

    /**
     * @param string
     * @return bool
     */
    private function regexHasCapturingGroups(string $regex): bool
    {
        if (false === strpos($regex, '(')) {
            // Needs to have at least a ( to contain a capturing group
            return false;
        }

        // Semi-accurate detection for capturing groups
        return (bool)preg_match(
            '~
                (?:
                    \(\?\(
                  | \[ [^\]\\\\]* (?: \\\\ . [^\]\\\\]* )* \]
                  | \\\\ .
                ) (*SKIP)(*FAIL) |
                \(
                (?!
                    \? (?! <(?![!=]) | P< | \' )
                  | \*
                )
            ~x',
            $regex
        );
    }
}
