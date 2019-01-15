<?php declare(strict_types = 1);
namespace TheSeer\phpDox;

/**
 * Class FileInfoCollection
 */
class FileInfoCollection implements \Iterator, \Countable {
    /**
     * @var FileInfo[]
     */
    private $data = [];

    /**
     * @var int
     */
    private $pos = 0;

    public function add(FileInfo $file): void {
        $this->data[] = $file;
    }

    /**
     * @throws \TheSeer\phpDox\FileInfoCollectionException
     */
    public function current(): FileInfo {
        if (!isset($this->data[$this->pos])) {
            throw new FileInfoCollectionException('Empty collection');
        }

        return $this->data[$this->pos];
    }

    public function next(): void {
        $this->pos++;
    }

    public function key(): int {
        return $this->pos;
    }

    public function valid(): bool {
        return $this->count() > $this->pos;
    }

    public function rewind(): void {
        $this->pos = 0;
    }

    public function count(): int {
        return \count($this->data);
    }
}
