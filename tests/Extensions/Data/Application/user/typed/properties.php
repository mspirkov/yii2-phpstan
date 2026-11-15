<?php

declare(strict_types=1);

namespace app\user\typed;

use MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\Application\Clock;
use MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\Application\Identity;
use MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\Application\WebUser;
use Yii;
use yii\web\User;

use function PHPStan\Testing\assertType;

function components(): void
{
    assertType(WebUser::class . '&' . User::class . '<' . Identity::class . '>', Yii::$app->user);
    assertType(Identity::class . '|null', Yii::$app->user->identity);
}

function skipped(): void
{
    assertType(Clock::class, Yii::$app->formatter);
}
