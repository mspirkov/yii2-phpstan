<?php

declare(strict_types=1);

namespace MSpirkov\Yii2\PHPStan\Analyzers;

use yii\base\InvalidConfigException;

final class PathAnalyzer
{
    /**
     * @param list<string> $paths
     *
     * @return list<string>
     */
    public function resolveRealPaths(array $paths, string $parameter): array
    {
        $realPaths = [];
        foreach ($paths as $path) {
            $realPath = realpath($path);
            if ($realPath === false) {
                throw new InvalidConfigException(sprintf(
                    'Path "%s" of the "%s" parameter does not exist.',
                    $path,
                    $parameter
                ));
            }

            $realPaths[] = $realPath;
        }

        return $realPaths;
    }

    /**
     * Returns the indexes of the path lists that contain the most specific path of the file, several when they
     * list the same path.
     *
     * @param array<int, list<string>> $pathLists real paths
     *
     * @return list<int>
     */
    public function findClosestPathIndexes(string $file, array $pathLists): array
    {
        $realPath = realpath($file);
        $file = $realPath === false ? $file : $realPath;

        $indexes = [];
        $longestLength = 0;
        foreach ($pathLists as $index => $paths) {
            foreach ($paths as $path) {
                $length = strlen($path);
                if (
                    $length < $longestLength
                    || ($file !== $path && strncmp($file, $path . DIRECTORY_SEPARATOR, $length + 1) !== 0)
                ) {
                    continue;
                }

                if ($length > $longestLength) {
                    $indexes = [];
                    $longestLength = $length;
                }

                $indexes[$index] = $index;
            }
        }

        return array_values($indexes);
    }
}
