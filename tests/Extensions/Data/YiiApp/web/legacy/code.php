<?php

declare(strict_types=1);

namespace app\web\legacy\controllers;

use Yii;
use yii\web\Application;

use function PHPStan\Testing\assertType;

function app(): void
{
    assertType(Application::class . '|null', Yii::$app);
}
