<?php

declare(strict_types=1);

namespace app\console\controllers {
    use MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\ContainerGet\MailerInterface;
    use yii\di\Container;

    use function PHPStan\Testing\assertType;

    function console(Container $container): void
    {
        assertType(MailerInterface::class, $container->get(MailerInterface::class));
        assertType('object', $container->get('mailer'));
    }
}

namespace app\commonly {
    use yii\di\Container;

    use function PHPStan\Testing\assertType;

    function similarlyNamed(Container $container): void
    {
        assertType('object', $container->get('mailer'));
    }
}

namespace {
    use yii\di\Container;

    use function PHPStan\Testing\assertType;

    function global_namespace(Container $container): void
    {
        assertType('object', $container->get('mailer'));
    }
}
