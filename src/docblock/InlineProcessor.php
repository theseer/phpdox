<?php declare(strict_types = 1);
namespace TheSeer\phpDox\DocBlock;

class InlineProcessor {
    protected $dom;

    protected $factory;

    protected $regex = '/(.*?)\{(\@(?>[^{}]+|(?R))*)\}|(.*)/m';

    public function __construct(Factory $factory, \TheSeer\fDOM\fDOMDocument $ctx) {
        $this->factory = $factory;
        $this->dom     = $ctx;
    }

    public function transformToDom($text) {
        return $this->doParse($text);
    }

    protected function doParse($text) {
        $count = \preg_match_all($this->regex, $text, $matches);

        if (\implode('', $matches[1]) == '') {
            return $this->dom->createTextNode($text);
        }
        $fragment = $this->dom->createDocumentFragment();

        for ($x = 0; $x < $count; $x++) {
            for ($t = 1; $t <= 3; $t++) {
                if ($matches[$t][$x] == '') {
                    continue;
                }

                if ($t == 2) {
                    $fragment->appendChild($this->processMatch($matches[$t][$x]));

                    continue;
                }
                $part = $matches[$t][$x];

                if ($t == 3 && $part != '') {
                    $part .= "\n";
                }
                $fragment->appendChild($this->dom->createTextNode($part));
            }
        }

        return $fragment;
    }

    protected function processMatch($match) {
        if ($match === '@') {
            return $this->dom->createTextNode('{');
        }
        $parts      = \preg_split("/[\s,]+/", $match, 2, \PREG_SPLIT_NO_EMPTY);
        $annotation = \mb_substr($parts[0], 1);

        if (\preg_match('=^[a-zA-Z0-9]*$=', $annotation)) {
            $parser = $this->factory->getParserInstanceFor($annotation);
        } else {
            $parser = $this->factory->getParserInstanceFor('invalid', $annotation);
        }

        if (isset($parts[1])) {
            $parser->setPayload($parts[1]);
        }

        $node = $parser->getObject([])->asDom($this->dom);

        foreach ($node->childNodes as $child) {
            if ($child instanceof \DOMText) {
                $node->replaceChild($this->doParse($child->wholeText), $child);
            }
        }

        return $node;
    }
}
