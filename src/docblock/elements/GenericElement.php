<?php declare(strict_types = 1);
namespace TheSeer\phpDox\DocBlock;

class GenericElement {
    protected $factory;

    protected $name;

    protected $body;

    protected $attributes = [];

    public function __construct(Factory $factory, $name) {
        $this->factory = $factory;
        $this->name    = $name;
    }

    public function __call($method, $value): void {
        if (!\preg_match('/^set/', $method)) {
            throw new GenericElementException("Method '$method' not defined", GenericElementException::MethodNotDefined);
        }
        // extract attribute name (remove 'set' or 'get' from string)
        $attribute                    = \mb_strtolower(\mb_substr($method, 3));
        $this->attributes[$attribute] = $value[0];
    }

    public function getAnnotationName() {
        return $this->name;
    }

    public function getBody() {
        return $this->body;
    }

    public function setBody($body): void {
        $this->body = $body;
    }

    public function asDom(\TheSeer\fDOM\fDOMDocument $ctx) {
        $node = $ctx->createElementNS('http://xml.phpdox.net/src', \mb_strtolower($this->name));

        foreach ($this->attributes as $attribute => $value) {
            if ($value != '') {
                $node->setAttribute($attribute, $value);
            }
        }

        if ($this->body !== null && $this->body !== '') {
            $parser = $this->factory->getInlineProcessor($ctx);
            $node->appendChild($parser->transformToDom($this->body));
        }

        return $node;
    }
}
