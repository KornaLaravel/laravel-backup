<?php

use Spatie\Backup\Config\BackupConfig;
use Spatie\Backup\Config\CleanupConfig;
use Spatie\Backup\Config\Config;
use Spatie\Backup\Config\MonitoredBackupsConfig;
use Spatie\Backup\Config\NotificationsConfig;

beforeEach(function () {
    config()->set('backup', []);
});

it('returns default backup config if no backup config file exist', function () {
    $config = Config::fromArray(config('backup'));

    expect($config->backup)->toBeInstanceOf(BackupConfig::class);
    expect($config->notifications)->toBeInstanceOf(NotificationsConfig::class);
    expect($config->monitoredBackups)->toBeInstanceOf(MonitoredBackupsConfig::class);
    expect($config->cleanup)->toBeInstanceOf(CleanupConfig::class);
});

it('returns a merged backup config made with minimal config and default config file', function () {
    config()->set('backup.backup.name', 'foo');

    $config = Config::fromArray(config('backup'));

    expect($config->backup)->toBeInstanceOf(BackupConfig::class);
    expect($config->backup->name)->toBe('foo');
});

it('receives temp directory as configured from service container', function () {
    config()->set('backup.backup.temporary_directory', '/foo');

    $tempDirectory = app()->make('backup-temporary-project');

    expect($tempDirectory->path())->toBe('/foo');
});
