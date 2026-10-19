<?php

declare(strict_types=1);

namespace MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\ComponentBehavior;

use yii\base\Behavior;
use yii\base\Component;

/**
 * @extends Behavior<Component>
 */
class Logger extends Behavior
{
    /** @var list<string> */
    public array $messages = [];

    public function log(string $message): void
    {
        $this->messages[] = $message;
    }

    /**
     * @return list<string>
     */
    public function reset(): array
    {
        $messages = $this->messages;
        $this->messages = [];

        return $messages;
    }
}
