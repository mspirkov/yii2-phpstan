<?php

namespace MSpirkov\Yii2\PHPStan\Tests\Rules\Source\ModelAttributeHintsValidation;

use MSpirkov\Yii2\PHPStan\Tests\Rules\Source\Common\TaggableBehavior;
use yii\base\DynamicModel;
use yii\base\Model;

/**
 * @property string $email
 */
final class ValidModel extends Model
{
    public $login;
    public $password;

    public function attributeHints(): array
    {
        return array_merge(parent::attributeHints(), [
            'login' => 'Your login name',
            'password' => 'Your password',
            'email' => 'Your e-mail',
        ]);
    }
}

final class InvalidModel extends Model
{
    public $login;
    public $password;

    public function attributeHints(): array
    {
        return [
            'login' => 'Your login name',
            'nickname' => 'Your nickname',
            'password' => 42,
            'oops',
            '' => 'Empty name',
            'unknownAndBadType' => 123,
        ];
    }
}

final class DynamicHintModel extends Model
{
    public $login;

    public function attributeHints(): array
    {
        $hint = $this->buildHint();

        return [
            'login' => $hint,
        ];
    }

    private function buildHint(): string
    {
        return 'Your login name';
    }
}

final class SpreadHintModel extends Model
{
    public $login;

    public function attributeHints(): array
    {
        $extra = ['login' => 'Your login name'];

        return [
            ...$extra,
        ];
    }
}

final class NotInterestingMethod
{
    public function attributeHints(): array
    {
        return [
            'unrelated' => 123,
        ];
    }
}

final class MagicAttributeModel extends Model
{
    public $login;

    public function attributeHints(): array
    {
        return [
            'login' => 'Your login name',
            'fullName' => 'Your full name',
            'secret' => 'Your secret',
        ];
    }

    public function getFullName(): string
    {
        return 'Full Name';
    }

    public function setSecret(string $value): void
    {
    }
}

/**
 * @mixin TaggableBehavior
 */
final class MixinAttributeModel extends Model
{
    public $login;

    public function behaviors(): array
    {
        return [TaggableBehavior::class];
    }

    public function attributeHints(): array
    {
        return [
            'login' => 'Your login name',
            'tagNames' => 'Your tag names',
            'tagValues' => 'Your tag values',
            'nickname' => 'Your nickname',
        ];
    }
}

final class AttributeNameShapeModel extends Model
{
    public $login;

    public function attributeHints(): array
    {
        return [
            'user.name' => 'User name',
            'COALESCE(map_id, 0)' => 'Map',
            ' login ' => 'Your login name',
            ' nickname ' => 'Your nickname',
        ];
    }
}

final class ConfigSkippedModel extends Model
{
    public function attributeHints(): array
    {
        return [
            'createdAt' => 'Creation time',
            '' => 'Empty name',
        ];
    }
}

final class ConfigSkippedAttributesModel extends Model
{
    public function attributeHints(): array
    {
        return [
            'createdAt' => 'Creation time',
            'nickname' => 'Your nickname',
        ];
    }
}

final class SkippedDynamicModel extends DynamicModel
{
    public function attributeHints(): array
    {
        return [
            'unknown' => 'Unknown hint',
        ];
    }
}
