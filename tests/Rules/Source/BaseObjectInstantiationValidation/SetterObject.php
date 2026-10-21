<?php

declare(strict_types=1);

namespace MSpirkov\Yii2\PHPStan\Tests\Rules\Source\BaseObjectInstantiationValidation;

use yii\base\BaseObject;

final class SetterObject extends BaseObject
{
    private ?string $transport = null;

    protected ?string $secret = null;

    /**
     * @param array{dsn: string}|string $transport
     */
    public function setTransport($transport): void
    {
        $this->transport = is_array($transport) ? $transport['dsn'] : $transport;
    }

    public function setDefaults(): void
    {
    }
}
