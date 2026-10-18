<?php

declare(strict_types=1);

use yii\console\Application as ConsoleApplication;
use yii\web\Application;
use yii\web\IdentityInterface;

use function PHPStan\Testing\assertType;

function app(): void
{
    assertType(ConsoleApplication::class . '|' . Application::class . '<' . IdentityInterface::class . '>|null', Yii::$app);
}
