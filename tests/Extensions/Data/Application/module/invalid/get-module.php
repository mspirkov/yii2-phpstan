<?php

declare(strict_types=1);

namespace app\module\invalid;

use yii\base\Module;
use yii\console\Application;

use function PHPStan\Testing\assertType;

function skipped(Application $app): void
{
    assertType(Module::class . '|null', $app->getModule('admin'));
}
