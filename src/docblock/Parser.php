<?php declare(strict_types = 1);
namespace TheSeer\phpDox\DocBlock;

class Parser {
    protected $factory;

    protected $current;

    protected $aliasMap = [];

    public function __construct(Factory $factory) {
        $this->factory = $factory;
    }

    public function parse($block, array $aliasMap) {
        $this->aliasMap = $aliasMap;
        $this->current  = null;

        $docBlock = $this->factory->getDocBlock();
        $lines    = $this->prepare((string)$block);

        if (\count($lines) > 1) {
            $this->startParser('description');
        }
        $buffer = [];

        foreach ($lines as $line) {
            if ($line == '' || $line == '/') {
                if (\count($buffer)) {
                    $buffer[] = '';
                }

                continue;
            }

            if ($line[0] == '@') {
                if ($this->current !== null) {
                    $docBlock->appendElement(
                        $this->current->getObject($buffer)
                    );
                }
                $buffer = [];

                \preg_match('/^\@([a-zA-Z0-9_]+)(.*)$/', $line, $lineParts);
                $name    = ($lineParts[1] ?? '(undefined)');
                $payload = (isset($lineParts[2]) ? \trim($lineParts[2]) : '');

                $this->startParser($name, $payload);

                continue;
            }
            $buffer[] = $line;
        }

        if (!$this->current) {
            // A Single line docblock with no @ annotation is considered a description
            $this->startParser('description');
        }
        $docBlock->appendElement(
            $this->current->getObject($buffer)
        );

        return $docBlock;
    }

    protected function prepare($block) {
        $block = \str_replace(["\r\n", "\r"], "\n", \mb_substr($block, 3, -2));
        $raw   = [];

        foreach (\explode("\n", $block) as $line) {
            $line  = \preg_replace('/^\s*\*? ?/', '', $line);
            $raw[] = \rtrim($line, " \n\t*");
        }

        return $raw;
    }

    protected function startParser($name, $payload = null): void {
        if (!\preg_match('/^[a-zA-Z0-9-_\.]*$/', $name) || empty($name)) {
            // TODO: errorlog
            $this->current = $this->factory->getParserInstanceFor('invalid', $name);
        } else {
            $this->current = $this->factory->getParserInstanceFor($name);
        }
        $this->current->setAliasMap($this->aliasMap);

        if ($payload !== null) {
            $this->current->setPayload($payload);
        }
    }
}
