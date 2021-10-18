<?php
/**
 * This file is part of Mini.
 * @auth lupeng
 */
declare(strict_types=1);

namespace MiniRoute;

use MiniRoute\RouteParser\Std;
use MiniRoute\DataGenerator\GroupCountBased;
use MiniRoute\RouteCollector;
use RuntimeException;

if (!function_exists('MiniRoute\simpleDispatcher')) {
    /**
     * @param callable $routeDefinitionCallback
     * @param array $options
     *
     * @return Dispatcher
     */
    function simpleDispatcher(callable $routeDefinitionCallback, array $options = []): Dispatcher
    {
        $options += [
            'routeParser' => Std::class,
            'dataGenerator' => GroupCountBased::class,
            'dispatcher' => Dispatcher\GroupCountBased::class,
            'routeCollector' => RouteCollector::class,
        ];

        /** @var RouteCollector $routeCollector */
        $routeCollector = new $options['routeCollector'](
            new $options['routeParser'], new $options['dataGenerator']
        );
        $routeDefinitionCallback($routeCollector);

        return new $options['dispatcher']($routeCollector->getData());
    }

    /**
     * @param callable $routeDefinitionCallback
     * @param array $options
     *
     * @return Dispatcher
     */
    function cachedDispatcher(callable $routeDefinitionCallback, array $options = []): Dispatcher
    {
        $options += [
            'routeParser' => Std::class,
            'dataGenerator' => GroupCountBased::class,
            'dispatcher' => Dispatcher\GroupCountBased::class,
            'routeCollector' => RouteCollector::class,
            'cacheDisabled' => false,
        ];

        if (!isset($options['cacheFile'])) {
            throw new \LogicException('Must specify "cacheFile" option');
        }

        if (!$options['cacheDisabled'] && file_exists($options['cacheFile'])) {
            $dispatchData = require $options['cacheFile'];
            if (!is_array($dispatchData)) {
                throw new RuntimeException('Invalid cache file "' . $options['cacheFile'] . '"');
            }
            return new $options['dispatcher']($dispatchData);
        }

        $routeCollector = new $options['routeCollector'](
            new $options['routeParser'], new $options['dataGenerator']
        );
        $routeDefinitionCallback($routeCollector);

        /** @var RouteCollector $routeCollector */
        $dispatchData = $routeCollector->getData();
        if (!$options['cacheDisabled']) {
            file_put_contents(
                $options['cacheFile'],
                '<?php return ' . var_export($dispatchData, true) . ';'
            );
        }

        return new $options['dispatcher']($dispatchData);
    }
}
