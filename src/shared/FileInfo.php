<?php declare(strict_types = 1);
namespace TheSeer\phpDox;

class FileInfo extends \SplFileInfo {
    public function __toString(): string {
        return $this->getPathname();
    }

    /**
     * @throws FileInfoException
     */
    public function getRealPath() {
        $path = parent::getRealPath();

        if (!$path) {
            throw new FileInfoException(
                \sprintf("Path '%s' does not exist - call to realpath failed", $this->getPathname()),
                FileInfoException::InvalidPath
            );
        }

        return $this->toUnix($path);
    }

    public function exists(): bool {
        \clearstatcache(true, $this->getPathname());

        return \file_exists($this->getPathname());
    }

    public function asFileUri(): string {
        $result = $this->getRealPath();

        if ($result[0] !== '/') {
            $result = '/' . $result;
        }

        return 'file://' . urlencode($result);
    }

    public function getPath() {
        return $this->toUnix(parent::getPath());
    }

    /**
     * @param bool $inclusive
     */
    public function getRelative(\SplFileInfo $relation, $inclusive = true): self {
        $relPath      = $this->getRealPath();
        $relationPath = $relation->getRealPath();

        if ($inclusive) {
            $relationPath = \dirname($relationPath);
        }
        $relPath = \mb_substr($relPath, \mb_strlen($relationPath) + 1);

        return new self($relPath);
    }

    public function getPathname(): string {
        return $this->toUnix(parent::getPathname());
    }

    public function getLinkTarget(): string {
        return $this->toUnix(parent::getLinkTarget());
    }

    /**
     * @param string $class_name
     *
     * @throws FileInfoException
     */
    public function getFileInfo($class_name = null): void {
        throw new FileInfoException('getFileInfo not implemented', FileInfoException::NotImplemented);
    }

    /**
     * @param string $class_name
     *
     * @throws FileInfoException
     */
    public function getPathInfo($class_name = null): void {
        throw new FileInfoException('getPathInfo not implemented', FileInfoException::NotImplemented);
    }

    /**
     * @param string $str
     */
    private function toUnix($str): string {
        return \str_replace('\\', '/', $str);
    }
}
