<?php declare(strict_types = 1);
namespace TheSeer\phpDox\Generator;

use TheSeer\fDOM\fDOMDocument;

class AbstractUnitObject {
    /**
     * @var fDOMDocument
     */
    private $dom;

    public function __construct(fDOMDocument $dom) {
        $this->dom = $dom;
    }

    public function asDom(): fDOMDocument {
        return $this->dom;
    }

    public function getInlineComments() {
        return new InlineCommentCollection($this->dom->query('phpdox:inline'));
    }

    public function getSourceFile(): string {
        $file = $this->asDom()->queryOne('//phpdox:file');

        if (!$file) {
            return '';
        }

        return $file->getAttribute('realpath');
    }

    public function getFullName(): string {
        return $this->dom->documentElement->getAttribute('full');
    }

    public function getConstants(): ConstantCollection {
        return new ConstantCollection($this->dom->query('phpdox:constant'));
    }

    public function getMethods(): MethodCollection {
        return new MethodCollection($this->dom->query('phpdox:constructor|phpdox:method|phpdox:destructor'));
    }
}
