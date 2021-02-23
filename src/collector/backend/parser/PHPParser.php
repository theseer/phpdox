<?php declare(strict_types = 1);
namespace TheSeer\phpDox\Collector\Backend;

use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use TheSeer\phpDox\Collector\SourceFile;
use TheSeer\phpDox\DocBlock\Parser as DocblockParser;
use TheSeer\phpDox\ErrorHandler;

class PHPParser implements BackendInterface {
    /**
     * @var Parser
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
     * @throws ParseErrorException
     */
    public function parse(SourceFile $sourceFile, bool $publicOnly): ParseResult {
        try {
            $result = new ParseResult($sourceFile);
            $nodes  = $this->getParserInstance()->parse($sourceFile->getSource());

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

    private function getParserInstance(): Parser {
        if ($this->parser === null) {
            $this->parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7, new CustomLexer());
        }

        return $this->parser;
    }

    /**
     * @param bool $publicOnly
     */
    private function getTraverserInstance(ParseResult $result, $publicOnly): NodeTraverser {
        $traverser = new NodeTraverser();
        $traverser->addVisitor(new NameResolver());

        if ($publicOnly === true) {
            $traverser->addVisitor(new PublicOnlyVisitor());
        }
        $traverser->addVisitor(new UnitCollectingVisitor($this->docblockParser, $result));

        return $traverser;
    }
}
