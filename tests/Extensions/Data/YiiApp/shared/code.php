<?php

declare(strict_types=1);

namespace app\shared;

use MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\YiiApp\User;
use Yii;
use yii\console\Application as ConsoleApplication;
use yii\web\Application;
use yii\web\IdentityInterface;

use function PHPStan\Testing\assertType;

function app(): void
{
    assertType(ConsoleApplication::class . '|' . Application::class . '<' . User::class . '>', Yii::$app);
}

function narrowed(): void
{
    if (Yii::$app instanceof Application) {
        assertType(Application::class . '<' . IdentityInterface::class . '>', Yii::$app);
    }
}
