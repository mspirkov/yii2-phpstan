<?php

declare(strict_types=1);

namespace MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\ContainerGet;

class ClockFactory
{
    public function __invoke(): Clock
    {
        return new Clock();
    }

    public static function create(): Clock
    {
        return new Clock();
    }

    public static function createNullable(): ?Clock
    {
        return null;
    }

    public static function createObject(): object
    {
        return new Clock();
    }
}
