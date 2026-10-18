<?php

declare(strict_types=1);

namespace app\web\api\controllers;

use MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\YiiApp\ApiApplication;
use Yii;

use function PHPStan\Testing\assertType;

function app(): void
{
    assertType(ApiApplication::class, Yii::$app);
}
