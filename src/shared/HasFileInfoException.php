<?php declare(strict_types = 1);
namespace TheSeer\phpDox;

abstract class HasFileInfoException extends \Exception {
    protected $file;

    public function __construct($message, $code, \Exception $previous, \SPLFileInfo $file) {
        parent::__construct($message, $code, $previous);
        $this->file = $file;
    }

    public function getFileInfo() {
        return $this->file;
    }
}
