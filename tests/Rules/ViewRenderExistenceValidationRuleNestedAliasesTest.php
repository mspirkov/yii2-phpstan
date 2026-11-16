<?php

declare(strict_types=1);

namespace MSpirkov\Yii2\PHPStan\Tests\Rules;

use MSpirkov\Yii2\PHPStan\Rules\ViewRenderExistenceValidationRule;

/**
 * @extends AbstractTestCase<ViewRenderExistenceValidationRule>
 */
final class ViewRenderExistenceValidationRuleNestedAliasesTest extends AbstractTestCase
{
    public function testRuleResolvesTheLongestMatchingAlias(): void
    {
        $this->analyse(
            [self::getDataFilePath('nested-aliases')],
            [
                [$this->message('@ext/gii/views/default/missing', 'gii/views/default/missing'), 9, $this->tip('@ext/gii')],
                [$this->message('@ext/giix/views/missing', 'ext/giix/views/missing'), 10, $this->tip('@ext')],
                [
                    $this->message('@debug/toolbar/views/missing', 'debug-toolbar/views/missing'),
                    11,
                    $this->tip('@debug/toolbar'),
                ],
                [
                    'View "@debug/views/missing" uses the alias "@debug" that cannot be resolved. Add it to the "mspirkovYii2.aliases" parameter.',
                    12,
                ],
            ],
        );
    }

    public static function getAdditionalConfigFiles(): array
    {
        return array_merge(parent::getAdditionalConfigFiles(), [
            self::getConfigFilePath('nested-aliases'),
        ]);
    }

    protected static function getRuleClass(): string
    {
        return ViewRenderExistenceValidationRule::class;
    }

    private function message(string $view, string $file): string
    {
        return sprintf(
            'View "%s" does not exist at "tests/Rules/Data/ViewRenderExistenceValidation/nested-aliases/%s.php". Check the view name or create the view file.',
            $view,
            $file
        );
    }

    private function tip(string $alias): string
    {
        return sprintf(
            'The view path is built from the "%s" alias set in the "mspirkovYii2.aliases" parameter. It may point to the wrong directory.',
            $alias
        );
    }
}
