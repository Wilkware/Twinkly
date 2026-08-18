<?php

declare(strict_types=1);

/**
 * class ValidationTest
 */
class ValidationTest extends TestCaseSymconValidation
{
    /**
     * testValidateLibrary
     *
     * @return void
     */
    public function testValidateLibrary(): void
    {
        $this->validateLibrary(__DIR__ . '/..');
    }

    /**
     * @dataProvider moduleProvider
     */
    public function testValidateModule(string $modDir): void
    {
        if (!is_dir($modDir)) {
            // @phpstan-ignore method.notFound
            $this->fail("Modulverzeichnis $modDir existiert nicht.");
        }

        $this->validateModule($modDir);
    }

    /**
     * Returns all module directories for the DataProvider.
     *
     * @return string[][] Array of arrays with module path as string
     */
    public static function moduleProvider(): array
    {
        $parentDir = __DIR__ . '/..';
        $dirs = array_filter(glob($parentDir . '/*'), 'is_dir');

        $ignore = ['.git', '.github', '.style', '.vscode', 'libs', 'docs', 'imgs', 'tests', 'actions'];
        $dirs = array_filter($dirs, fn ($dir) => !in_array(basename($dir), $ignore, true));

        if (empty($dirs)) {
            // Dummy entry so that PHPUnit generates at least one test
            return ['NO_MODULES_FOUND' => ['__NO_MODULES_FOUND__']];
        }

        $result = [];
        foreach ($dirs as $dir) {
            $result[basename($dir)] = [$dir]; // Real name as key
        }

        return $result;
    }
}