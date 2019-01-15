<?php declare(strict_types = 1);
namespace TheSeer\phpDox\Collector\Backend;

use PhpParser\ParserFactory;
use TheSeer\phpDox\Collector\SourceFile;
use TheSeer\phpDox\DocBlock\Parser as DocblockParser;
use TheSeer\phpDox\ErrorHandler;

class PHPParser implements BackendInterface {
    /**
     * @var \PhpParser\Parser
     */
    private $parser;

    /**
     * @var DocblockParser
     */
    private $docblockParser;

    /**
     * @var ErrorHandler
     */
    private $errorHandler;

    public function __construct(DocblockParser $parser, ErrorHandler $errorHandler) {
        $this->docblockParser = $parser;
        $this->errorHandler   = $errorHandler;
    }

    /**
     * @param bool $publicOnly
     *
     * @throws ParseErrorException
     */
    public function parse(SourceFile $sourceFile, $publicOnly): ParseResult {
        try {
            $result = new ParseResult($sourceFile);
            $parser = $this->getParserInstance();
            $nodes  = $parser->parse($sourceFile->getSource());

            if (!$nodes) {
                throw new ParseErrorException("Parser didn't return any nodes", ParseErrorException::GeneralParseError);
            }
            $this->getTraverserInstance($result, $publicOnly)->traverse($nodes);

            return $result;
        } catch (\Exception $e) {
            $this->errorHandler->clearLastError();

            throw new ParseErrorException('Internal Error during parsing', ParseErrorException::GeneralParseError, $e);
        }
    }

    private function getParserInstance(): \PhpParser\Parser {
        if ($this->parser === null) {
            $this->parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7, new CustomLexer());
        }

        return $this->parser;
    }

    /**
     * @param bool $publicOnly
     */
    private function getTraverserInstance(ParseResult $result, $publicOnly): \PhpParser\NodeTraverser {
        $traverser = new \PhpParser\NodeTraverser();
        $traverser->addVisitor(new \PhpParser\NodeVisitor\NameResolver());

        if ($publicOnly === true) {
            $traverser->addVisitor(new PublicOnlyVisitor());
        }
        $traverser->addVisitor(new UnitCollectingVisitor($this->docblockParser, $result));

        return $traverser;
    }
}
