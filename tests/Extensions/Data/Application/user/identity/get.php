<?php

declare(strict_types=1);

namespace app\user\identity;

use MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\Application\Identity;
use MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\Application\WebUser;
use yii\web\Application;
use yii\web\User;

use function PHPStan\Testing\assertType;

function resolved(Application $app): void
{
    assertType(User::class . '<' . Identity::class . '>', $app->get('user'));
    assertType(WebUser::class . '&' . User::class . '<' . Identity::class . '>', $app->get('webUser'));
}
