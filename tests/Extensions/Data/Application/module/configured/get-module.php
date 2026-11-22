<?php

declare(strict_types=1);

namespace app\module\configured;

use MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\Application\AdminModule;
use MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\Application\BlogModule;
use MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\Application\ContentModule;
use MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\Application\ForumModule;
use Yii;
use yii\base\Module;
use yii\web\Application;

use function PHPStan\Testing\assertType;

function resolved(Application $app, bool $flag): void
{
    assertType(AdminModule::class, $app->getModule('admin'));
    assertType(AdminModule::class, Yii::$app->getModule('admin'));
    assertType(AdminModule::class, $app->getModule('admin', true));
    assertType(AdminModule::class . '|null', $app->getModule('admin', false));
    assertType(AdminModule::class . '|null', $app->getModule('admin', $flag));
    assertType(ContentModule::class, $app->getModule('admin/content'));
    assertType(ContentModule::class . '|null', $app->getModule('admin/content', false));
    assertType(ForumModule::class, $app->getModule('forum'));
    assertType(BlogModule::class, $app->getModule('blog'));
    assertType(AdminModule::class . '|' . ForumModule::class, $app->getModule($flag ? 'admin' : 'forum'));
}

function skipped(Application $app, Module $module, string $id, bool $flag): void
{
    assertType(Module::class . '|null', $app->getModule('clock'));
    assertType(Module::class . '|null', $app->getModule('undefined'));
    assertType(Module::class . '|null', $app->getModule('admin/undefined'));
    assertType(Module::class . '|null', $app->getModule('admin/content/undefined'));
    assertType(Module::class . '|null', $app->getModule('forum/undefined'));
    assertType(Module::class . '|null', $app->getModule($flag ? 'admin' : 'undefined'));
    assertType(Module::class . '|null', $app->getModule($id));
    assertType(Module::class . '|null', $app->getModule());
    assertType(Module::class . '|null', $module->getModule('admin'));
}
