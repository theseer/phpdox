<?php declare(strict_types = 1);
namespace TheSeer\phpDox;

class DirectoryCleanerTest extends \PHPUnit\Framework\TestCase {
    /**
     * @var DirectoryCleaner
     */
    private $cleaner;

    protected function setUp(): void {
        $this->cleaner = new DirectoryCleaner();
    }

    /**
     * @expectedException \TheSeer\phpDox\DirectoryCleanerException
     * @expectedExceptionCode \TheSeer\phpDox\DirectoryCleanerException::SecurityLimitation
     */
    public function testTryingToDeleteAShortPathThrowsException(): void {
        $this->cleaner->process(new FileInfo('/tmp'));
    }

    public function testTryingToDeleteNonExistingDirectoryJustReturns(): void {
        $this->cleaner->process(new FileInfo('/not/existing/directory'));
        $this->assertTrue(true);
    }

    public function testCanDeleteRecursiveDirectoryStructure(): void {
        $base = '/tmp/' . \uniqid('dctest-');
        $path = $base . '/a/b/c/d/e/f/g/h';
        \mkdir($path, 0700, true);
        \touch($path . '/test-h.txt');
        \touch($path . '/../test-g.txt');
        \touch($path . '/../../test-f.txt');

        $this->assertFileExists($path . '/test-h.txt');
        $this->assertDirectoryExists($path);

        $this->cleaner->process(new FileInfo($base));

        $this->assertFileNotExists($path . '/test-h.txt', 'File vanished');
        $this->assertDirectoryNotExists($base, 'Directory vanished');
    }
}
