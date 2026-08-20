<?php

namespace Laravel\Telescope\Tests\Console;

use Laravel\Telescope\Console\InstallCommand;
use Laravel\Telescope\Tests\FeatureTestCase;

class InstallCommandTest extends FeatureTestCase
{
    public function test_telescope_migrations_are_only_published_once()
    {
        $directory = database_path('migrations');
        $directoryAlreadyExists = is_dir($directory);

        if (! $directoryAlreadyExists) {
            mkdir($directory, 0755, true);
        }

        $migration = $directory.'/2026_08_20_000000_create_telescope_entries_table.php';
        $command = new TestInstallCommand;

        try {
            $command->publishMigrationsForTest();

            $this->assertSame(1, $command->migrationPublishCount);

            file_put_contents($migration, '<?php');

            $command->publishMigrationsForTest();

            $this->assertSame(1, $command->migrationPublishCount);
        } finally {
            if (file_exists($migration)) {
                unlink($migration);
            }

            if (! $directoryAlreadyExists && is_dir($directory)) {
                rmdir($directory);
            }
        }
    }
}

class TestInstallCommand extends InstallCommand
{
    public int $migrationPublishCount = 0;

    public function publishMigrationsForTest()
    {
        $this->publishMigrations();
    }

    public function callSilent($command, array $arguments = [])
    {
        if ($command === 'vendor:publish' && $arguments === ['--tag' => 'telescope-migrations']) {
            $this->migrationPublishCount++;
        }

        return 0;
    }
}
