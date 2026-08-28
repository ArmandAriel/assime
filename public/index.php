<?php

use App\Kernel;

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

return static function (array $context) {
    $appEnv = $context['APP_ENV'] ?? null;

    if (!is_string($appEnv)) {
        throw new LogicException('APP_ENV must be a string.');
    }

    return new Kernel($appEnv, (bool) ($context['APP_DEBUG'] ?? false));
};
