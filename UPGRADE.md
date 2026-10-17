# Upgrade guide

## Upgrading from 0.13 to 0.14

The package is renamed from `mspirkov/yii2-phpstan-rules` to `mspirkov/yii2-phpstan`, since it now ships type extensions alongside the rules. The rules themselves work as before. What changes is the package name, the config file, and the `mspirkovYii2Rules` prefix of the parameters and error identifiers, which becomes `mspirkovYii2`.

### 1. Replace the package

```bash
php composer.phar remove --dev mspirkov/yii2-phpstan-rules
php composer.phar require --dev mspirkov/yii2-phpstan
```

The new package replaces the old one, so Composer won't install them side by side: remove the old package first.

### 2. Update the include

Skip this step if your project uses [`phpstan/extension-installer`](https://github.com/phpstan/extension-installer).

`rules.neon` is gone: `extension.neon` now holds both the rules and the type extensions.

```diff
 includes:
-    - vendor/mspirkov/yii2-phpstan-rules/rules.neon
+    - vendor/mspirkov/yii2-phpstan/extension.neon
```

### 3. Rename the parameters

The parameters moved from `mspirkovYii2Rules` to `mspirkovYii2`. The option names under it are unchanged.

```diff
 parameters:
-    mspirkovYii2Rules:
+    mspirkovYii2:
         aliases:
             '@app': %currentWorkingDirectory%
         noDynamicQueryWhere:
             enabled: false
```

### 4. Update the error identifiers

The error identifiers moved from `mspirkovYii2Rules.<rule>` to `mspirkovYii2.<rule>`, e.g. `mspirkovYii2Rules.noDirectSuperglobals` is now `mspirkovYii2.noDirectSuperglobals`. Update them wherever you reference them:

- `ignoreErrors` entries in your PHPStan config;
- `@phpstan-ignore` comments in your code;
- the baseline.

The messages of the view existence rules that name a parameter now say `"mspirkovYii2.aliases"`, so baseline entries for those messages change too.

Every change in steps 3 and 4 is the same rename, so replacing `mspirkovYii2Rules` with `mspirkovYii2` across your project (excluding `vendor`) covers them all.

### New: `Container::get()` return type

`Yii::$container->get()` can now resolve to the class behind a container definition, read from your application configs. It's opt-in through the new `mspirkovYii2.applicationConfigs` parameter. Without it, types stay as they were. See [Type extensions](README.md#type-extensions) in the README for the setup.
