<img src="https://github.com/user-attachments/assets/33ef89f7-0be1-4f27-bf13-8a4e668b74cd" />

[![PHP](https://img.shields.io/badge/%3E%3D7.4-7A86B8.svg?style=for-the-badge&logo=php&logoColor=white&label=PHP)](https://www.php.net/releases/7_4_0.php)
[![Yii2](https://img.shields.io/badge/%3E%3D2.0.53-247BA0.svg?style=for-the-badge&logo=yii&logoColor=white&label=Yii)](https://github.com/yiisoft/yii2/releases/tag/2.0.53)
[![PHPStan](https://img.shields.io/badge/%3E%3D2.2.0-247BA0.svg?style=for-the-badge&label=PHPStan)](https://github.com/phpstan/phpstan/releases/tag/2.2.0)
[![Total Downloads](https://img.shields.io/packagist/dt/mspirkov/yii2-phpstan.svg?style=for-the-badge&logo=composer&logoColor=white&label=Downloads)](https://packagist.org/packages/mspirkov/yii2-phpstan)
[![Tests](https://img.shields.io/github/actions/workflow/status/mspirkov/yii2-phpstan/ci.yml?branch=main&style=for-the-badge&logo=github&label=Tests)](https://github.com/mspirkov/yii2-phpstan/actions/workflows/ci.yml)
[![Coverage](https://img.shields.io/codecov/c/github/mspirkov/yii2-phpstan.svg?branch=main&style=for-the-badge&logo=codecov&logoColor=white&label=Coverage)](https://codecov.io/github/mspirkov/yii2-phpstan)
[![PHPStan Level Max](https://img.shields.io/badge/Max-7A86B8.svg?style=for-the-badge&label=PHPStan%20Level)](https://github.com/mspirkov/yii2-phpstan/blob/main/phpstan.dist.neon)

[PHPStan](https://phpstan.org) rules and type extensions for [Yii2](https://github.com/yiisoft/yii2). The rules check the config arrays and names that Yii2 only resolves at runtime, and keep business logic and database access out of controllers and views. The type extensions resolve types from the application config, such as the class behind a DI container ID. The rules are my own conventions, not a standard: use what's useful and disable the rest.

## Support

If you like this project, give it a ⭐ on [GitHub](https://github.com/mspirkov/yii2-phpstan) — it helps others
discover it.

## Installation

> [!IMPORTANT]
>
> The newer your [PHP](https://www.php.net), [Yii2](https://www.yiiframework.com) and [PHPStan](https://phpstan.org), the more accurate the analysis.

```bash
php composer.phar require --dev mspirkov/yii2-phpstan
```

With [`phpstan/extension-installer`](https://github.com/phpstan/extension-installer), the package is enabled automatically. Otherwise, include it in `phpstan.neon`:

```neon
includes:
    - vendor/mspirkov/yii2-phpstan/extension.neon
```

### Upgrading

See [UPGRADE.md](UPGRADE.md) to upgrade between versions or from `mspirkov/yii2-phpstan-rules`, the package's former name.

## Supported extensions

The classes of [yiisoft/yii2-mongodb](https://github.com/yiisoft/yii2-mongodb) and [yiisoft/yii2-redis](https://github.com/yiisoft/yii2-redis) are treated like those of `yii\db`, except by `activeQueryWithValidation`, which skips them. `behaviorAttributesValidation` also checks `DateTimeBehavior` from [mspirkov/yii2-db](https://github.com/mspirkov/yii2-db).

## Configuration

All parameters go under `parameters.mspirkovYii2`.

### Enabling rules

All rules are on by default. `enableAllRules` switches all of them, `enableValidationRules` the [validation rules](#validation-rules) (it defaults to `enableAllRules`), and `enabled` a single rule:

```neon
parameters:
    mspirkovYii2:
        # Only the validation rules
        enableAllRules: false
        enableValidationRules: true

        # Except one
        modelScenariosValidation:
            enabled: false
```

### Application configs

The [type extensions](#type-extensions) read types from these configs. The `files` of an entry are merged with `ArrayHelper::merge()` in order and apply to the code in its `paths` (directories or files, the most specific one wins). The files are executed during analysis, so load what they rely on (`Yii.php`, aliases, `YII_ENV`, ...) through `bootstrapFiles`. For [yii2-app-advanced](https://github.com/yiisoft/yii2-app-advanced):

```neon
parameters:
    mspirkovYii2:
        applicationConfigs:
            -
                paths:
                    - %currentWorkingDirectory%/backend
                files:
                    - %currentWorkingDirectory%/common/config/main.php
                    - %currentWorkingDirectory%/common/config/main-local.php
                    - %currentWorkingDirectory%/backend/config/main.php
                    - %currentWorkingDirectory%/backend/config/main-local.php
            -
                paths:
                    - %currentWorkingDirectory%/common
                files:
                    - %currentWorkingDirectory%/common/config/main.php
                    - %currentWorkingDirectory%/common/config/main-local.php
            -
                paths:
                    - %currentWorkingDirectory%/console
                files:
                    - %currentWorkingDirectory%/common/config/main.php
                    - %currentWorkingDirectory%/common/config/main-local.php
                    - %currentWorkingDirectory%/console/config/main.php
                    - %currentWorkingDirectory%/console/config/main-local.php
            -
                paths:
                    - %currentWorkingDirectory%/frontend
                files:
                    - %currentWorkingDirectory%/common/config/main.php
                    - %currentWorkingDirectory%/common/config/main-local.php
                    - %currentWorkingDirectory%/frontend/config/main.php
                    - %currentWorkingDirectory%/frontend/config/main-local.php
```

### `Yii::$app` types

`yiiAppTypes` sets the type of `Yii::$app` for the code in the `paths` of an entry, matched as in `applicationConfigs`. `type` is a PHPDoc type, the one Yii declares when omitted, and `nullable: false` removes `null` from it. For [yii2-app-advanced](https://github.com/yiisoft/yii2-app-advanced):

```neon
parameters:
    mspirkovYii2:
        yiiAppTypes:
            -
                paths:
                    - %currentWorkingDirectory%/backend
                    - %currentWorkingDirectory%/frontend
                type: yii\web\Application<common\models\User>
                nullable: false
            -
                paths:
                    - %currentWorkingDirectory%/common
                type: yii\console\Application|yii\web\Application<common\models\User>
                nullable: false
            -
                paths:
                    - %currentWorkingDirectory%/console
                type: yii\console\Application
                nullable: false
```

### Views

How the view existence rules resolve view names:

```neon
parameters:
    mspirkovYii2:
        # Root aliases (`@app`, not `@app/modules`) for `@alias/...` names. `//...` names go through `@app/views`
        aliases:
            '@app': %currentWorkingDirectory%
            '@shared': %currentWorkingDirectory%/modules/shared

        # Tried in order for names without an extension, `php` by default
        viewExtensions:
            - php
            - twig

        # Views directory for the controllers in a namespace, the most specific one wins.
        # `SiteController` reads `resources/frontend/views/site/*`
        viewPaths:
            'App\Frontend\Controllers': %currentWorkingDirectory%/resources/frontend/views
```

### Rule options

```neon
parameters:
    mspirkovYii2:
        # Classes skipped by the rules that check config arrays against a class, e.g. when its
        # constructor consumes some keys itself. Without `attributes`, the whole class is skipped
        baseObjectConfigValidation:
            skippedClasses:
                -
                    class: app\payment\PaymentGateway
                    attributes:
                        - retryPolicy
                -
                    class: app\sdk\ThirdPartySdkClient

        # Project validator aliases for modelRulesValidation
        modelRulesValidation:
            customValidators:
                slug: app\validators\SlugValidator

        # Limits for the complexity rules (the defaults)
        actionComplexity:
            ifCount: 3
            foreachCount: 0
            forCount: 0
            whileCount: 0
            doWhileCount: 0
            switchCount: 0
            matchCount: 0
            ternaryCount: 1
            tryCatchCount: 1

        # Components treated as the database by the DB access rules (the default)
        yiiAppDbProperties:
            - db

        # Yii::$app properties allowed by noForbiddenYiiAppProperties (the defaults)
        noForbiddenYiiAppProperties:
            allowedProperties:
                - id
                - name
                - charset
                - language
                - timeZone
```

## Type extensions

The type extensions resolve types that Yii only knows at runtime. Set them up in [Application configs](#application-configs) and [`Yii::$app` types](#yiiapp-types).

### `Container::get()` return type

Resolves a string ID from `container.definitions` or `container.singletons` to the class its definition names. An ID that is a class or an interface keeps that type.

```php
'container' => [
    'definitions' => [
        MailerInterface::class => SmtpMailer::class,
        'mailer' => MailerInterface::class,
        'mailer.smtp' => ['class' => SmtpMailer::class],
        'clock' => static fn() => new SystemClock(),
    ],
],

Yii::$container->get(MailerInterface::class); // MailerInterface, as Yii declares it
Yii::$container->get('mailer');               // MailerInterface instead of object
Yii::$container->get('mailer.smtp');          // SmtpMailer instead of object
Yii::$container->get('clock');                // SystemClock, inferred from the closure
```

### `Yii::$app` type

Gives `Yii::$app` the type set in [`yiiAppTypes`](#yiiapp-types), e.g. a custom application class or the identity class of the `user` component. With the [yii2-app-advanced](https://github.com/yiisoft/yii2-app-advanced) example from there:

```php
// backend, frontend
Yii::$app;                 // yii\web\Application<common\models\User> instead of yii\console\Application|yii\web\Application|null
Yii::$app->user->identity; // common\models\User|null instead of yii\web\IdentityInterface|null
Yii::$app?->id;            // ✗ Using nullsafe property access on non-nullable type

// console
Yii::$app;                 // yii\console\Application

// outside yiiAppTypes
Yii::$app;                 // yii\console\Application|yii\web\Application|null, as Yii declares it
```

### `Yii::$app` components

Resolves `Yii::$app->payments` and `Yii::$app->get('payments')` to the class set in `components`.

```php
'components' => [
    'mailer' => ['class' => SymfonyMailer::class],
    'payments' => ['class' => StripeGateway::class],
],

Yii::$app->mailer;                 // SymfonyMailer instead of MailerInterface
Yii::$app->payments;               // StripeGateway instead of an undefined property
Yii::$app->get('payments');        // StripeGateway instead of object
Yii::$app->get('payments', false); // StripeGateway|null instead of object|null
```

### `Yii::$app->params`

Resolves to an array shape of `params`, with the values generalized to types.

```php
'params' => [
    'adminEmail' => 'admin@example.com',
    'pageSize' => 20,
],

Yii::$app->params;             // array{adminEmail: string, pageSize: int}
Yii::$app->params['pageSzie']; // ✗ Offset 'pageSzie' does not exist
```

## Rules at a glance

### Validation rules

Check the config arrays and names (attributes, relations, actions, views) that Yii2 only resolves at runtime.

| Rule                                                                             | Catches                                                                                                                                |
| -------------------------------------------------------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------- |
| [`activeFormFieldValidation`](#active-form-field-validation)                     | Missing, read-only and write-only attributes in `ActiveForm::field()`                                                                  |
| [`activeQueryWithValidation`](#activequery-with-validation)                      | Unknown relations in `with()` / `joinWith()` / `innerJoinWith()`                                                                       |
| [`activeRecordConditionValidation`](#active-record-condition-validation)         | Unknown attributes and mistyped values in `findOne()` / `findAll()` / `deleteAll()` / `updateAll()` / `updateAllCounters()` conditions |
| [`activeRecordRelationValidation`](#active-record-relations-validation)          | Unknown attributes in `hasOne()` / `hasMany()` links                                                                                   |
| [`activeRecordUpdateValuesValidation`](#active-record-update-values-validation)  | Unknown attributes and mistyped values in `updateAll()` / `updateAllCounters()` values                                                 |
| [`baseObjectInstantiationValidation`](#baseobject-instantiation-validation)      | Unknown and mistyped options in the `$config` of `new` on a `BaseObject`                                                               |
| [`behaviorAttributesValidation`](#behavior-attributes-validation)                | Unknown attributes in the options of `TimestampBehavior`, `SluggableBehavior` and other attribute behaviors                            |
| [`componentBehaviorsValidation`](#component-behaviors-validation)                | Non-behavior classes and unknown or mistyped options in `behaviors()`                                                                  |
| [`controllerActionsValidation`](#controller-actions-validation)                  | Non-action classes and unknown or mistyped options in `actions()`                                                                      |
| [`controllerBehaviorActionsValidation`](#controller-behavior-actions-validation) | Unknown actions in the filters of controller `behaviors()`                                                                             |
| [`controllerViewExistenceValidation`](#controller-view-existence-validation)     | Missing views in controller `render()` / `renderPartial()` / `renderAjax()`                                                            |
| [`htmlActiveAttributeValidation`](#html-active-attribute-validation)             | Unknown attributes in `Html::active*()`                                                                                                |
| [`modelAttributeHintsValidation`](#model-attribute-hints-validation)             | Unknown and empty attributes in `attributeHints()`                                                                                     |
| [`modelAttributeLabelsValidation`](#model-attribute-labels-validation)           | Unknown and empty attributes in `attributeLabels()`                                                                                    |
| [`modelRulesValidation`](#model-validation-rules-validation)                     | Unknown validators, options and attributes, missing required options and invalid regexes in `rules()`                                  |
| [`modelScenariosValidation`](#model-scenarios-validation)                        | Empty names, non-array attribute lists and unknown attributes in `scenarios()`                                                         |
| [`nestedViewExistenceValidation`](#nested-view-existence-validation)             | Missing views in `$this->render()` in views                                                                                            |
| [`queryConditionValidation`](#query-condition-validation)                        | Wrong operand counts in `where()` / `andWhere()` / `orWhere()` operator conditions                                                     |
| [`uploadedFileInstanceValidation`](#uploadedfile-instance-validation)            | Unknown attributes in `UploadedFile::getInstance()` / `getInstances()`                                                                 |
| [`viewRenderExistenceValidation`](#viewrender-existence-validation)              | Missing views in `View::render()` outside views                                                                                        |
| [`widgetPropertiesValidation`](#widget-properties-validation)                    | Unknown and mistyped options in `Widget::begin()` / `Widget::widget()`                                                                 |
| [`yiiCreateObjectValidation`](#yiicreateobject-validation)                       | Missing `class` and unknown or mistyped options in `Yii::createObject()`                                                               |

### Code quality rules

Keep business logic and database access out of controllers and views, and catch superglobals, dynamic SQL, `Yii::$app` used as a service locator, and redundant calls.

| Rule                                                                   | Catches                                                           |
| ---------------------------------------------------------------------- | ----------------------------------------------------------------- |
| [`noComplexActionClasses`](#complexity-limits)                         | `Action` classes with too much branching and looping              |
| [`noComplexControllerActions`](#complexity-limits)                     | Controller actions with too much branching and looping            |
| [`noControllerActionCallsViaThis`](#no-calling-actions-via-this)       | `$this->actionFoo()` in controllers                               |
| [`noDbQueriesInActions`](#no-database-access-outside-repositories)     | Database access in `Action` classes                               |
| [`noDbQueriesInControllers`](#no-database-access-outside-repositories) | Database access in controllers                                    |
| [`noDbQueriesInViews`](#no-database-access-outside-repositories)       | Database access in views                                          |
| [`noDirectSuperglobals`](#no-raw-superglobals)                         | `$_GET`, `$_POST`, `$_SESSION` and other superglobals             |
| [`noDynamicQueryWhere`](#no-dynamic-sql-strings)                       | String-built conditions in `where()` / `andWhere()` / `orWhere()` |
| [`noForbiddenYiiAppProperties`](#no-forbidden-yiiapp-properties)       | Reads of `Yii::$app` components                                   |
| [`noRedundantExistenceCheck`](#no-redundant-existence-check)           | `one()` and `count()` checks where `exists()` suffices            |
| [`noRedundantHtmlEncode`](#no-redundant-htmlencode)                    | `Html::encode()` of a `numeric-string`                            |
| [`noYiiAppPropertyMutation`](#no-yiiapp-property-mutation)             | Writes to `Yii::$app` properties, including `setComponents()`     |

## Rule reference

### Validation rules

The attribute checks recognize PHPDoc `@property` tags and skip `yii\base\DynamicModel`, whose attributes are only defined at runtime.

#### Active Form field validation

Checks that the attribute in `ActiveForm::field()` is readable and writable: a property, a `@property` tag, or a getter and setter.

```php
/**
 * @property string $email
 * @property-read string $fullName
 */
final class ContactModel extends Model
{
    public $name;

    public function getPhone(): string { /* ... */ }

    public function setPhone(string $phone): void { /* ... */ }
}
```

```php
/** @var ContactModel $model */

$form = ActiveForm::begin();

echo $form->field($model, 'name');     // ✓ declared property
echo $form->field($model, 'email');    // ✓ declared via @property
echo $form->field($model, 'phone');    // ✓ has both getPhone() and setPhone()
echo $form->field($model, 'fullName'); // ✗ read-only — declared via @property-read, nothing to write the submitted value back to
echo $form->field($model, 'nickname'); // ✗ typo — "nickname" is not a property on ContactModel

ActiveForm::end();
```

#### `ActiveQuery` `with()` validation

Checks the relations in `with()`, `joinWith()` and `innerJoinWith()`, including aliases (`'orders o'`) and sub-relations (`'orders.items'`). A sub-relation is checked when the model of its parent relation is known from `@return ActiveQuery<T>` or `@property-read T`.

```php
/**
 * @property-read Address $address
 */
class Customer extends ActiveRecord
{
    /** @return ActiveQuery<Order> */
    public function getOrders()
    {
        return $this->hasMany(Order::class, ['customer_id' => 'id']);
    }

    public function getAddress()
    {
        return $this->hasOne(Address::class, ['id' => 'address_id']);
    }
}

class Order extends ActiveRecord
{
    /** @return ActiveQuery<Item> */
    public function getItems()
    {
        return $this->hasMany(Item::class, ['order_id' => 'id']);
    }
}

class Address extends ActiveRecord
{
    /** @return ActiveQuery<Country> */
    public function getCountry()
    {
        return $this->hasOne(Country::class, ['id' => 'country_id']);
    }
}

class Item extends ActiveRecord { /* ... */ }
class Country extends ActiveRecord  { /* ... */ }
```

```php
Customer::find()->with('orders')->all();           // ✓
Customer::find()->with('orders.items')->all();     // ✓ Order declares its own "items" relation
Customer::find()->with('address.country')->all();  // ✓ related model resolved via @property-read
Customer::find()->joinWith('orders o')->all();     // ✓ alias is stripped before the relation is checked
Customer::find()->with('oders')->all();             // ✗ typo — no such relation on Customer
Customer::find()->with('orders.oops')->all();       // ✗ typo — no such relation on Order
```

#### Active Record condition validation

Checks the attributes and value types in the array conditions of `findOne()`, `findAll()`, `deleteAll()`, `updateAll()` and `updateAllCounters()`. An `ExpressionInterface` value fits any attribute.

```php
/**
 * @property int $id
 * @property int $status
 * @property string $updated_at
 */
final class Customer extends ActiveRecord { /* ... */ }

Customer::findOne(1);                                         // ✓ primary key lookup, not a condition hash
Customer::findOne(['status' => 1]);                           // ✓
Customer::findOne(['status' => [1, 2]]);                      // ✓ IN (1, 2)
Customer::findOne(['updated_at' => new Expression('NOW()')]); // ✓ raw SQL, not type-checked
Customer::findOne(['statuss' => 1]);                          // ✗ typo — unknown attribute
Customer::findOne(['status' => '1']);                         // ✗ wrong type — int expected
Customer::deleteAll(['statuss' => 1]);                        // ✗ typo — unknown attribute
Customer::updateAll(['status' => 1], ['idd' => 5]);           // ✗ typo — unknown attribute in the condition
```

#### Active Record relations validation

Checks that the attributes in `hasOne()` and `hasMany()` links exist: the keys on the related model, the values on the current one.

```php
/**
 * @property int $id
 * @property int $customer_id
 * @property int $shipping_address_id
 */
final class Order extends ActiveRecord
{
    public function getShippingAddress(): ActiveQuery
    {
        // ✗ missing property "uuid" on Address
        return $this->hasOne(Address::class, ['uuid' => 'shipping_address_id']);
    }

    public function getItems(): ActiveQuery
    {
        // ✗ missing property "order_uuid" on Order
        return $this->hasMany(OrderItem::class, ['order_id' => 'order_uuid']);
    }

    public function getCustomer(): ActiveQuery
    {
        // ✓
        return $this->hasOne(Customer::class, ['id' => 'customer_id']);
    }
}

/**
 * @property int $id
 */
final class Customer extends ActiveRecord { /* ... */ }

/**
 * @property int $id
 */
final class Address extends ActiveRecord { /* ... */ }

/**
 * @property int $id
 * @property int $order_id
 */
final class OrderItem extends ActiveRecord { /* ... */ }
```

#### Active Record update values validation

Checks the attributes and value types in the values of `updateAll()` and `updateAllCounters()`. Unlike in a condition, an array value is a type mismatch, and an `ExpressionInterface` value fits any attribute.

```php
/**
 * @property int $id
 * @property int $status
 * @property int $age
 * @property string $updated_at
 */
final class Customer extends ActiveRecord { /* ... */ }

Customer::updateAll(['status' => 1], ['id' => 5]);              // ✓
Customer::updateAll(['updated_at' => new Expression('NOW()')]); // ✓ raw SQL, not type-checked
Customer::updateAll(['statuss' => 1]);                          // ✗ typo — unknown attribute
Customer::updateAll(['status' => 'active']);                    // ✗ wrong type — int expected
Customer::updateAllCounters(['age' => 1]);                      // ✓
Customer::updateAllCounters(['agee' => 1]);                     // ✗ typo — unknown attribute
```

#### `BaseObject` instantiation validation

Checks the options passed to `new` on a `yii\base\BaseObject` subclass against its writable properties and their types, when its last constructor parameter is `$config`.

```php
$countQuery = Article::find()->where(['status' => 1]);

new Pagination(['totalCount' => $countQuery->count()]);  // ✓
new Pagination(['totalCoutn' => 100]);                   // ✗ typo — unknown option "totalCoutn"
new Pagination(['defaultPageSize' => '20']);             // ✗ wrong type — int expected
```

#### Behavior attributes validation

Checks that the attributes in the options of `TimestampBehavior`, `BlameableBehavior`, `SluggableBehavior`, `AttributeTypecastBehavior` and `DateTimeBehavior` exist on the model.

```php
/**
 * @property string $title
 * @property string $slug
 * @property string $created_at
 */
final class Post extends ActiveRecord
{
    public function behaviors(): array
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',   // ✓
                'updatedAtAttribute' => 'udpated_at',   // ✗ typo — unknown attribute
            ],
            [
                'class' => SluggableBehavior::class,
                'attribute' => 'titel',                 // ✗ typo — unknown attribute
                'slugAttribute' => 'slug',              // ✓
            ],
            [
                'class' => AttributeTypecastBehavior::class,
                'attributeTypes' => [
                    'created_at' => AttributeTypecastBehavior::TYPE_STRING, // ✓
                ],
            ],
        ];
    }
}
```

The `attributes` event map of any `AttributeBehavior` subclass is checked the same way:

```php
/**
 * @property string $created_at
 * @property string|null $updated_at
 */
final class Article extends ActiveRecord
{
    public function behaviors(): array
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'attributes' => [
                    self::EVENT_BEFORE_INSERT => ['created_at', 'updated_at'], // ✓
                ],
            ],
            [
                'class' => BlameableBehavior::class,
                'attributes' => [
                    self::EVENT_BEFORE_INSERT => ['created_by', 'updated_by'], // ✗ neither attribute exists
                ],
            ],
            [
                'class' => SluggableBehavior::class,
                'attributes' => [
                    self::EVENT_BEFORE_VALIDATE => 'slug',     // ✗ unknown attribute
                ],
            ],
            [
                'class' => DateTimeBehavior::class,
                'attributes' => [
                    self::EVENT_BEFORE_UPDATE => 'updated_at', // ✓
                ],
            ],
        ];
    }
}
```

#### Component behaviors validation

Checks `behaviors()` of `yii\base\Component` subclasses: classes that aren't behaviors, bad config keys, and unknown or mistyped options.

```php
public function behaviors(): array
{
    return [
        'timestamp' => [
            'class' => TimestampBehavior::class,
            'createdAtAtribute' => 'created_at',     // ✗ typo — unknown option
        ],
        'typecast' => [
            'class' => AttributeTypecastBehavior::class,
            'attributeTypes' => [
                'views_count' => AttributeTypecastBehavior::TYPE_INTEGER,
                'is_published' => AttributeTypecastBehavior::TYPE_BOOLEAN,
            ],
            'typecastAfterValidate' => 1,            // ✗ bool expected
        ],
        'invalid' => stdClass::class,                // ✗ not a yii\base\Behavior

        'slug' => [
            'class' => SluggableBehavior::class,
            'attribute' => 'title',                  // ✓
        ],
    ];
}
```

#### Controller actions validation

Checks `actions()` of `yii\base\Controller` subclasses: classes that aren't actions, empty action IDs, bad config keys, and unknown or mistyped options.

```php
public function actions(): array
{
    return [
        'error' => [
            'class' => ErrorAction::class,
            'vieww' => 'error',            // ✗ typo — unknown option
        ],
        'captcha' => [
            'class' => CaptchaAction::class,
            'fixedVerifyCode' => 1,        // ✗ string expected
        ],
        'invalid' => stdClass::class,      // ✗ not a yii\base\Action

        'download' => [
            'class' => DownloadAction::class,
            'path' => '@app/uploads',      // ✓
        ],
    ];
}
```

#### Controller behavior actions validation

Checks that the actions named in controller filters exist, as `actionXxx()` methods or in `actions()`: `only`, `except` and `optional` of `ActionFilter` subclasses, `actions` in `AccessControl` rules, and the keys of `VerbFilter::$actions`. Wildcards are skipped.

```php
public function behaviors(): array
{
    return [
        'verbs' => [
            'class' => VerbFilter::class,
            'actions' => [
                'delet' => ['POST'],                   // ✗ typo — no such action
                'view-post' => ['GET'],                // ✓ actionViewPost()
            ],
        ],
        'access' => [
            'class' => AccessControl::class,
            'only' => ['index', 'logout', 'signup'],   // ✗ "signup" does not exist
            'rules' => [
                ['allow' => true, 'actions' => ['index', 'logot'], 'roles' => ['@']],   // ✗ typo — no such action
                ['allow' => true, 'actions' => ['captcha']],                            // ✓ declared in actions()
            ],
        ],
        'negotiator' => [
            'class' => ContentNegotiator::class,
            'only' => ['not-found'],                   // ✗ no such action
        ],
    ];
}
```

#### Controller view existence validation

Checks that the view in controller `render()`, `renderPartial()` and `renderAjax()` exists, resolving the name the way Yii does with the [view settings](#views). The views directory comes from the location of the controller (`<module>/controllers/<Name>Controller.php`) or from `viewPaths`. Dynamic names, layouts and controllers that override `getViewPath()` are skipped.

```php
final class SiteController extends Controller  // app/controllers/SiteController.php
{
    public function actionIndex(): string
    {
        $this->render('index');                  // ✓ app/views/site/index.php exists
        $this->render('/site/_form');            // ✓ module views directory
        $this->render('@app/views/shared/menu'); // ✓ through the "@app" alias
        $this->render($name);                    // skipped — not a constant string

        $this->render('idnex');                  // ✗ app/views/site/idnex.php does not exist
        $this->renderPartial('_missing');        // ✗
        $this->render('//layouts/missing');      // ✗ app/views/layouts/missing.php does not exist
        $this->render('@theme/menu');            // ✗ the "@theme" alias is not in `aliases`

        return $this->render('index');
    }
}
```

#### `Html` active attribute validation

Checks that the attribute in `Html::activeInput()`, `activeTextInput()` and the other `active*()` methods exists on the model.

```php
/**
 * @property string $email
 */
final class ContactModel extends Model
{
    public $name;
}

/** @var ContactModel $model */

echo Html::activeLabel($model, 'name');    // ✓ declared property
echo Html::activeInput('text', $model, 'email');  // ✓ declared via @property
echo Html::activeTextInput($model, 'nema');       // ✗ typo — "nema" is not a property on ContactModel
echo Html::activeHint($model, 'nickname');        // ✗ typo — "nickname" is not a property on ContactModel
```

#### Model attribute hints validation

Checks that the keys of `attributeHints()` are existing attributes and aren't empty.

```php
/**
 * @property string $email
 */
final class ContactModel extends Model
{
    public $name;

    public function attributeHints(): array
    {
        return [
            'name' => 'Your full name',
            'emial' => 'We will reply here',   // ✗ typo — "emial" is not a property on ContactModel
            'email' => 'We will reply here',   // ✓ declared via @property
        ];
    }
}
```

#### Model attribute labels validation

Checks that the keys of `attributeLabels()` are existing attributes and aren't empty.

```php
/**
 * @property string $email
 */
final class ContactModel extends Model
{
    public $name;

    public function attributeLabels(): array
    {
        return [
            'name' => 'Name',
            'emial' => 'E-mail',   // ✗ typo — "emial" is not a property on ContactModel
            'email' => 'E-mail',   // ✓ declared via @property
        ];
    }
}
```

#### Model validation rules validation

Checks each entry of `rules()` against its validator: unknown validators, unknown or mistyped options, missing required options and invalid regexes. Add project validator aliases in [`customValidators`](#rule-options).

```php
public function rules(): array
{
    return [
        ['email', 'string', 'lenght' => 255],             // ✗ typo — unknown option "lenght" for StringValidator
        ['code', 'match', 'pattern' => '/[/'],            // ✗ invalid regular expression
        ['ip', 'ip', 'ipv4' => false, 'ipv6' => false],   // ✗ disables both protocols
        ['message', 'string', 'max' => 'invalid'],        // ✗ 'max' must be int|null
        ['status', 'someUnregisteredAlias'],              // ✗ unknown validator

        ['name', 'string', 'max' => 255],                 // ✓
    ];
}
```

It also checks that the attributes of each rule exist on the model:

```php
/**
 * @property string $email
 */
final class ContactModel extends Model
{
    public $name;

    public function rules(): array
    {
        return [
            ['name', 'required'],
            ['emial', 'required'],   // ✗ typo — "emial" is not a property on ContactModel
            ['email', 'string'],     // ✓ declared via @property
        ];
    }
}
```

#### Model scenarios validation

Checks `scenarios()` for empty names, attribute lists that aren't arrays, and unknown attributes. An unsafe `!attribute` is checked without the `!`.

```php
final class ContactModel extends Model
{
    public $name;
    public $email;

    public function scenarios(): array
    {
        return [
            'create' => ['name', 'email'],
            'update' => ['name', '!emial'],  // ✗ typo — "emial" is not a property on ContactModel
            '' => ['name'],                  // ✗ empty scenario name
            'delete' => 'name',              // ✗ must be an array of attribute names
        ];
    }
}
```

#### Nested view existence validation

Checks that the view in `$this->render()` in a view file (in a `views` or `viewPaths` directory) exists. A relative name is resolved against the directory of the file, other names as in [`controllerViewExistenceValidation`](#controller-view-existence-validation).

```php
// app/views/site/index.php
/** @var yii\web\View $this */

echo $this->render('_form');              // ✓ app/views/site/_form.php exists
echo $this->render('/layouts/_footer');   // ✓ app/views/layouts/_footer.php exists
echo $this->render($partial);             // skipped — not a constant string

echo $this->render('_fromm');             // ✗ app/views/site/_fromm.php does not exist
echo $this->render('//layouts/missing');  // ✗ app/views/layouts/missing.php does not exist
```

#### Query condition validation

Checks the operand count of operator conditions in `where()`, `andWhere()` and `orWhere()` (`in`, `between`, `like`, comparisons, ...), including those nested in `and`, `or` and `not`. Custom operators are skipped.

```php
$query->where(['in', 'status']);                                    // ✗ missing the values operand — needs 2
$query->andWhere(['between', 'age', 18]);                           // ✗ missing the upper bound — needs 3
$query->orWhere(['not', ['in', 'status']]);                         // ✗ same as above, nested inside "not"
$query->where(['>=', 'age', 18, 30]);                               // ✗ arbitrary operator, extra operand — needs exactly 2
$query->where(['and']);                                             // ✗ empty "and" — needs at least 1 operand

$query->where(['in', 'status', [1, 2]]);                            // ✓
$query->andWhere(['between', 'age', 18, 65]);                       // ✓
$query->orWhere(['and', ['status' => 1], ['in', 'type', [1, 2]]]);  // ✓
```

#### `UploadedFile` instance validation

Checks that the attribute in `UploadedFile::getInstance()` and `getInstances()` exists on the model.

```php
final class UploadForm extends Model
{
    public $imageFile;
    public $imageFiles;

    public function rules(): array
    {
        return [[['imageFile', 'imageFiles'], 'file']];
    }
}

/** @var UploadForm $model */

$model->imageFile = UploadedFile::getInstance($model, 'imageFile');    // ✓
$model->imageFiles = UploadedFile::getInstances($model, 'imageFiles'); // ✓
$model->imageFile = UploadedFile::getInstance($model, 'imagefile');    // ✗ typo — "imagefile" is not a property on UploadForm
$files = UploadedFile::getInstances($model, 'imagefiles');             // ✗ typo — "imagefiles" is not a property on UploadForm
```

#### `View::render()` existence validation

Checks `View::render()` outside view files, for `@alias/...` and `//...` names only: there is no current view file to resolve the others against.

```php
final class Mailer extends Component
{
    public function render(View $view): string
    {
        $view->render('@app/mail/layouts/html');   // ✓ file exists
        $view->render('//mail/welcome');           // ✓ app/views/mail/welcome.php exists
        $view->render('_relative');                // skipped — no current view file to resolve against

        return $view->render('@app/mail/missing'); // ✗ file does not exist
    }
}
```

#### Widget properties validation

Checks the options of `Widget::begin()` and `Widget::widget()` against the writable properties of the widget and their types.

```php
ActiveForm::begin([
    'method' => 'get',             // ✓ declared property
    'metod' => 'get',              // ✗ typo — unknown option "metod"
    'encodeErrorSummary' => 'yes', // ✗ wrong type — bool expected, string given
]);

ActiveForm::end();
```

#### `Yii::createObject()` validation

Checks that a `Yii::createObject()` config array has `class` or `__class`, and checks its options against the writable properties of the class and their types.

```php
Yii::createObject([
    'traceLevel' => 3,        // ✗ missing "class" or "__class"
]);

Yii::createObject([
    'class' => Logger::class,
    'traceLevel' => '3',      // ✗ wrong type — int expected
    'flushInteval' => 1000,   // ✗ typo — unknown option (should be "flushInterval")
]);

Yii::createObject([
    'class' => Logger::class,
    'traceLevel' => 3,        // ✓
]);
```

### Code quality rules

#### Complexity limits

Counts `if`, loops, `switch`, `match`, ternaries and `try/catch` in controller actions and `Action::run()` against the [`actionComplexity`](#rule-options) limits.

```php
// ✗ flagged: 4 `if` statements against a default limit of 3
public function actionCheckout(): string
{
    if ($this->cart->isEmpty()) { /* ... */ }
    if (!$this->cart->hasPaymentMethod()) { /* ... */ }
    if ($this->cart->hasOutOfStockItems()) { /* ... */ }
    if ($this->cart->hasExpiredCoupon()) { /* ... */ }

    return $this->render('checkout', ['cart' => $this->cart]);
}

// ✓ the decision tree moves to a service, the action just orchestrates
public function actionCheckout(): string
{
    return $this->render('checkout', $this->checkoutService->process($this->cart));
}
```

#### No calling actions via `$this`

```php
// ✗ flagged: bypasses the action-resolution pipeline (filters, events, results)
public function actionEdit(int $id): Response
{
    return $this->actionView($id);
}

// ✓ redirect, or extract the shared part into a private method / service
public function actionEdit(int $id): Response
{
    return $this->redirect(['view', 'id' => $id]);
}
```

#### No database access outside repositories

Reports ActiveRecord queries and saves, `Yii::$app->db`, commands, transactions and `Query` building in controllers, actions and views.

```php
// ✗ flagged in a view: queries the database instead of just rendering data
<?php foreach (Post::find()->where(['status' => 1])->all() as $post): ?>

// ✓ the controller/action fetches the data, the view only renders it
<?php foreach ($posts as $post): ?>
```

#### No raw superglobals

```php
// ✗ flagged, with the fix suggested in the error message
$id = $_GET['id'];

// ✓ read through the injected yii\web\Request instead
$id = $this->request->get('id');
```

#### No dynamic SQL strings

```php
// ✗ flagged: string-built condition, one step from SQL injection
$query->where("status = $status");
$query->andWhere('status = ' . $status);
$query->orWhere("id IN ($ids)");

// ✓ array condition syntax — parameterized, and PHPStan can see the shape
$query->where(['status' => $status]);
$query->andWhere(['in', 'id', $ids]);
```

#### No forbidden `Yii::$app` properties

Applies to any `yii\base\Application` expression. The [`allowedProperties`](#rule-options) stay readable.

```php
// ✗ arbitrary component access
$cache = Yii::$app->cache;

// ✓ inject the component instead
public function __construct(private CacheInterface $cache) {}
```

#### No redundant existence check

Reports `one()` compared with `null` and `count()` compared with `0` or `1`, where `exists()` runs a lighter query.

```php
// ✗ flagged: fetches a full row just to test whether one is there
public function emailIsTaken(string $email): bool
{
    return User::find()->where(['email' => $email])->one() !== null;
}

// ✗ flagged: counts every matching row just to test whether one is there
public function emailIsAvailable(string $email): bool
{
    return User::find()->where(['email' => $email])->count() < 1;
}

// ✓ exists() only asks the database whether a row is there
public function emailIsTaken(string $email): bool
{
    return User::find()->where(['email' => $email])->exists();
}

// ✓ same, negated
public function emailIsAvailable(string $email): bool
{
    return !User::find()->where(['email' => $email])->exists();
}
```

#### No redundant `Html::encode()`

A `numeric-string` has nothing to escape:

```php
/**
 * @var numeric-string $id
 * @var string $name
 */

echo Html::encode($id);   // ✗ flagged — $id can only ever be a numeric-string
echo Html::encode($name); // ✓ a plain string may still contain special characters
```

#### No `Yii::$app` property mutation

Applies to any `yii\base\Application` expression, like `noForbiddenYiiAppProperties`.

```php
// ✗ mutation of properties
Yii::$app->params = [...];
Yii::$app->setComponents([...]);
```
