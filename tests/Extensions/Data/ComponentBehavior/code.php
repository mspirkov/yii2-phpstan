<?php

declare(strict_types=1);

namespace app\models;

use MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\ComponentBehavior\Counter;
use MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\ComponentBehavior\Logger;
use MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\ComponentBehavior\Post;
use MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\ComponentBehavior\Registry;
use MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\ComponentBehavior\SpecialPost;
use stdClass;
use yii\web\IdentityInterface;
use yii\web\User;

use function PHPStan\Testing\assertType;

function attached(Post $post): void
{
    $post->attachBehavior('counter', Counter::class);

    assertType(Post::class . '&behaviors(counter: ' . Counter::class . ')', $post);
    assertType('int', $post->increment());
    assertType('int', $post->count);
    assertType('bool', $post->publish());
    assertType('string', $post->title);
    assertType('*ERROR*', $post->log('message'));
    assertType('*ERROR*', $post->messages);
}

function definitions(Post $post, Logger $logger, bool $flag): void
{
    $post->attachBehavior('class', ['class' => Counter::class, 'count' => 1]);
    $post->attachBehavior('__class', ['__class' => Logger::class, 'class' => Counter::class]);
    $post->attachBehavior('object', $logger);
    $post->attachBehavior('union', $flag ? Counter::class : Logger::class);

    assertType(
        Post::class . '&behaviors(class: ' . Counter::class . ', __class: ' . Logger::class . ', object: '
        . Logger::class . ', union: ' . Counter::class . '|' . Logger::class . ')',
        $post
    );
}

function attachedAll(Post $post, bool $flag): void
{
    $post->attachBehaviors([
        'counter' => Counter::class,
        Logger::class,
        stdClass::class,
        'unknown' => stdClass::class,
    ]);

    assertType(Post::class . '&behaviors(counter: ' . Counter::class . ', ' . Logger::class . ')', $post);
    assertType('int', $post->reset());

    $definitions = ['logger' => Logger::class];
    if ($flag) {
        $definitions['optional'] = Counter::class;
    }

    $post->attachBehaviors($definitions);

    assertType(
        Post::class . '&behaviors(counter: ' . Counter::class . ', ' . Logger::class . ', logger: ' . Logger::class . ')',
        $post
    );
}

function order(Post $post): void
{
    $post->attachBehavior('logger', Logger::class);
    $post->attachBehavior('counter', Counter::class);

    assertType('list<string>', $post->reset());

    $post->attachBehavior('logger', Counter::class);

    assertType(Post::class . '&behaviors(logger: ' . Counter::class . ', counter: ' . Counter::class . ')', $post);
    assertType('int', $post->reset());

    $post->attachBehavior('counter', Counter::class);

    assertType(Post::class . '&behaviors(logger: ' . Counter::class . ', counter: ' . Counter::class . ')', $post);

    $post->attachBehavior('logger', stdClass::class);

    assertType(Post::class . '&behaviors(counter: ' . Counter::class . ')', $post);
}

function detached(Post $post): void
{
    $post->attachBehaviors(['counter' => Counter::class, 'logger' => Logger::class]);
    $post->detachBehavior('counter');

    assertType(Post::class . '&behaviors(logger: ' . Logger::class . ')', $post);

    $post->detachBehaviors();

    assertType(Post::class, $post);
}

function optionalBranch(Post $post, bool $flag): void
{
    if ($flag) {
        $post->attachBehavior('counter', Counter::class);
    }

    assertType(Post::class, $post);
}

function differentBranches(Post $post, bool $flag): void
{
    if ($flag) {
        $post->attachBehavior('behavior', Counter::class);
    } else {
        $post->attachBehavior('behavior', Logger::class);
    }

    assertType(
        Post::class . '&behaviors(behavior: ' . Counter::class . ')|' . Post::class . '&behaviors(behavior: '
        . Logger::class . ')',
        $post
    );

    $post->detachBehaviors();

    assertType(Post::class, $post);
}

function mergedBranches(Post $post, Logger $logger, bool $flag, bool $isLogger): void
{
    if ($flag) {
        $post->attachBehavior('counter', Counter::class);
    } else {
        $post->attachBehavior('logger', Logger::class);
    }

    $post->attachBehavior('counter', Counter::class);

    assertType(Post::class . '&behaviors(counter: ' . Counter::class . ')', $post);
    assertType(
        Logger::class . '|' . Post::class . '&behaviors(counter: ' . Counter::class . ')',
        $isLogger ? $logger : $post
    );

    if ($post instanceof SpecialPost) {
        assertType(Post::class . '&behaviors(counter: ' . Counter::class . ')&' . SpecialPost::class, $post);
        assertType('int', $post->increment());
    }

    assertType(Post::class . '&behaviors(counter: ' . Counter::class . ')', $post);
}

/**
 * @param Post|Registry $postOrRegistry
 * @param User<IdentityInterface> $user
 * @param array<string, string> $definitions
 */
function skipped(
    Post $post,
    ?Post $nullablePost,
    $postOrRegistry,
    User $user,
    string $name,
    bool $flag,
    array $definitions
): void {
    $post->attachBehavior($name, Counter::class);
    $post->attachBehavior('counter');
    $post->attachBehaviors();
    $post->attachBehaviors($definitions);
    $post->attachBehaviors($flag ? ['counter' => Counter::class] : ['logger' => Logger::class]);
    $post->attachBehavior('class', ['count' => 1]);
    $post->attachBehavior('optional', $flag ? ['class' => Counter::class] : []);
    $post->detachBehavior($name);
    $post->detachBehavior('undefined');
    $post->detachBehaviors();
    $post->trigger('event');

    assertType(Post::class, $post);

    if ($post->attachBehavior('counter', Counter::class) !== null) {
        assertType(Post::class, $post);
    }

    $nullablePost->attachBehavior('counter', Counter::class);
    $postOrRegistry->attachBehavior('counter', Counter::class);
    $user->attachBehavior('counter', Counter::class);

    assertType(Post::class . '|null', $nullablePost);
    assertType(Post::class . '|' . Registry::class, $postOrRegistry);
    assertType(User::class . '<' . IdentityInterface::class . '>', $user);
}
