<?php

declare(strict_types=1);

namespace app\web\controllers;

use MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\YiiApp\CustomYii;
use MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\YiiApp\Registry;
use MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\YiiApp\User;
use Yii;
use yii\BaseYii;
use yii\console\Application as ConsoleApplication;
use yii\di\Container;
use yii\web\Application;
use yii\web\IdentityInterface;

use function PHPStan\Testing\assertType;

function app(): void
{
    assertType(Application::class . '<' . User::class . '>', Yii::$app);
    assertType(Application::class . '<' . User::class . '>', \Yii::$app);
    assertType(Application::class . '<' . User::class . '>', BaseYii::$app);
    assertType(Application::class . '<' . User::class . '>', CustomYii::$app);
    assertType(User::class . '|null', Yii::$app->user->identity);
}

function assigned(): void
{
    Yii::$app = null;
    assertType('null', Yii::$app);
}

/**
 * @param class-string<Yii> $class
 */
function skipped(string $class, string $name): void
{
    assertType(Container::class, Yii::$container);
    assertType('string|null', Registry::$app);
    assertType('mixed', Yii::${$name});
    assertType(ConsoleApplication::class . '|' . Application::class . '<' . IdentityInterface::class . '>|null', $class::$app);
}
