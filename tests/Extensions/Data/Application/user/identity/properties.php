<?php

declare(strict_types=1);

namespace app\user\identity;

use MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\Application\Clock;
use MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\Application\Identity;
use MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\Application\WebUser;
use yii\web\Application;
use yii\web\User;

use function PHPStan\Testing\assertType;

function components(Application $app): void
{
    assertType(User::class . '<' . Identity::class . '>', $app->user);
    assertType(Identity::class . '|null', $app->user->identity);
    assertType(WebUser::class . '&' . User::class . '<' . Identity::class . '>', $app->webUser);
    assertType(Identity::class . '|null', $app->webUser->identity);
}

function skipped(Application $app): void
{
    assertType(Clock::class, $app->clockUser);
    assertType(WebUser::class, $app->nonIdentityUser);
    assertType(WebUser::class, $app->unknownIdentityUser);
}
