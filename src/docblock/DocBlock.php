<?php declare(strict_types = 1);
namespace TheSeer\phpDox\DocBlock;

class DocBlock {
    protected $elements = [];

    public function appendElement(GenericElement $element): void {
        $name = $element->getAnnotationName();

        if (isset($this->elements[$name])) {
            if (!\is_array($this->elements[$name])) {
                $this->elements[$name] = [$this->elements[$name]];
            }
            $this->elements[$name][] = $element;

            return;
        }
        $this->elements[$name] = $element;
    }

    public function hasElementByName($name) {
        return isset($this->elements[$name]);
    }

    public function getElementByName($name) {
        if (!isset($this->elements[$name])) {
            throw new DocBlockException("No element with name '$name'", DocBlockException::NotFound);
        }

        return $this->elements[$name];
    }

    public function asDom(\TheSeer\fDOM\fDOMDocument $doc): \TheSeer\fDOM\fDOMElement {
        $node = $doc->createElementNS('http://xml.phpdox.net/src', 'docblock');
        // add lines and such?
        foreach ($this->elements as $element) {
            if (\is_array($element)) {
                foreach ($element as $el) {
                    $node->appendChild($el->asDom($doc));
                }

                continue;
            }
            $node->appendChild($element->asDom($doc));
        }

        return $node;
    }
}
