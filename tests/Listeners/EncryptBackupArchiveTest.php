<?php

use Spatie\Backup\Events\BackupZipWasCreated;

beforeEach(function () {
    config()->set('backup.backup.password', fakePassword());
});

it('keeps archive unencrypted without password', function () {
    config()->set('backup.backup.password', null);

    $path = zip();

    $zip = new ZipArchive;
    $zip->open($path);

    assertEncryptionMethod($zip, ZipArchive::EM_NONE);

    $this->assertTrue($zip->extractTo(__DIR__.'/../temp/extraction'));
    assertValidExtractedFiles();

    $zip->close();
});

/**
 * @param  int  $algorithm
 */
it('encrypts archive with password', function (int $algorithm) {
    config()->set('backup.backup.encryption', $algorithm);

    $path = zip();

    $zip = new ZipArchive;
    $zip->open($path);

    assertEncryptionMethod($zip, $algorithm);

    $zip->setPassword(fakePassword());
    $this->assertTrue($zip->extractTo(__DIR__.'/../temp/extraction'));
    assertValidExtractedFiles();

    $zip->close();
})->with([
    [ZipArchive::EM_AES_128],
    [ZipArchive::EM_AES_192],
    [ZipArchive::EM_AES_256],
]);

it('can not open encrypted archive without password', function () {
    $path = zip();

    $zip = new ZipArchive;
    $zip->open($path);

    assertEncryptionMethod($zip, ZipArchive::EM_AES_256);

    expect($zip->extractTo(__DIR__.'/../temp/extraction'))->toBeFalse();

    $zip->close();
});

function zip(): string
{
    $source = __DIR__.'/../stubs/archive.zip';
    $target = __DIR__.'/../temp/archive.zip';

    copy($source, $target);

    app()->call('\Spatie\Backup\Listeners\EncryptBackupArchive@handle', ['event' => new BackupZipWasCreated($target)]);

    return $target;
}

function assertEncryptionMethod(ZipArchive $zip, int $algorithm): void
{
    foreach (range(0, $zip->numFiles - 1) as $i) {
        expect($zip->statIndex($i)['encryption_method'])->toBe($algorithm);
    }
}

function assertValidExtractedFiles(): void
{
    foreach (['file1.txt', 'file2.txt', 'file3.txt'] as $filename) {
        $filepath = __DIR__.'/../temp/extraction/'.$filename;
        expect(file_exists($filepath))->toBeTrue();
        expect(file_get_contents($filepath))->toBe('lorum ipsum');
    }
}

function fakePassword(): string
{
    return '24dsjF6BPjWgUfTu';
}
