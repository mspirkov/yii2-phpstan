<?php

declare(strict_types=1);

namespace MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\Application;

use yii\mail\BaseMailer;

class Mailer extends BaseMailer
{
    protected function sendMessage($message): bool
    {
        return true;
    }
}
