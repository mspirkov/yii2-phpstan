<?php

declare(strict_types=1);

namespace MSpirkov\Yii2\PHPStan\Tests\Extensions;

use PHPStan\Testing\TypeInferenceTestCase;

abstract class AbstractTestCase extends TypeInferenceTestCase
{
    /**
     * @dataProvider dataFiles
     */
    public function testFileAsserts(string $file): void
    {
        foreach (static::gatherAssertTypes($file) as $assert) {
            $this->assertFileAsserts(...$assert);
        }
    }

    /**
     * @return iterable<string, array{string}>
     */
    abstract public static function dataFiles(): iterable;

    public static function getAdditionalConfigFiles(): array
    {
        $result = parent::getAdditionalConfigFiles();
        $result[] = __DIR__ . '/../../extension.neon';

        $additionalFile = static::getAdditionalConfigFile();
        if ($additionalFile !== null) {
            $result[] = $additionalFile;
        }

        return $result;
    }

    abstract protected static function getAdditionalConfigFile(): ?string;
}
