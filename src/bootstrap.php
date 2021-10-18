<?php
/**
 * This file is part of Mini.
 * @auth lupeng
 */
declare(strict_types=1);

namespace MiniRoute;

require __DIR__ . '/functions.php';

spl_autoload_register(static function ($class) {
    if (strpos($class, 'MiniRoute\\') === 0) {
        $name = substr($class, strlen('MiniRoute'));
        require __DIR__ . str_replace('\\', DIRECTORY_SEPARATOR, $name) . '.php';
    }
});
