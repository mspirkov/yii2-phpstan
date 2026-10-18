<?php

declare(strict_types=1);

namespace app\console\controllers;

use Yii;
use yii\console\Application;

use function PHPStan\Testing\assertType;

function app(): void
{
    assertType(Application::class, Yii::$app);
}
