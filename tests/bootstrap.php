<?php

declare(strict_types=1);

$stubBasePath = getenv('IPS_STUBS_PATH');

if ($stubBasePath === false) {
    $possiblePaths = [
        __DIR__ . '/stubs',
    ];
    foreach ($possiblePaths as $path) {
        if (file_exists($path . '/Validator.php')) {
            $stubBasePath = $path;
            break;
        }
    }
}

if ($stubBasePath === false || !file_exists($stubBasePath . '/Validator.php')) {
    throw new RuntimeException('IPS Stubs not found. Please set IPS_STUBS_PATH environment variable or check relative paths.');
}

include_once $stubBasePath . '/Validator.php';
