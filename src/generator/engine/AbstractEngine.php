<?php declare(strict_types = 1);
namespace TheSeer\phpDox\Generator\Engine;

use TheSeer\fDOM\fDOMDocument;
use TheSeer\fXSL\fXSLTProcessor;
use TheSeer\phpDox\DirectoryCleaner;
use TheSeer\phpDox\FileInfo;

abstract class AbstractEngine implements EngineInterface {
    protected function getXSLTProcessor($template) {
        $tpl = new fDomDocument();
        $tpl->load($template);

        if (\stripos(\PHP_OS, 'Linux') !== 0) {
            $this->resolveImports($tpl);
        }

        return new fXSLTProcessor($tpl);
    }

    protected function clearDirectory($path): void {
        $cleaner = new DirectoryCleaner();
        $cleaner->process(new FileInfo((string)$path));
    }

    protected function saveDomDocument(\DOMDocument $dom, $filename, $format = true) {
        $path = \dirname($filename);
        \clearstatcache();

        if (!\file_exists($path)) {
            \mkdir($path, 0777, true);
        }
        $dom->formatOutput = $format;

        return $dom->save($filename);
    }

    protected function saveFile($content, $filename) {
        $path = \dirname($filename);
        \clearstatcache();

        if (!\file_exists($path)) {
            \mkdir($path, 0777, true);
        }

        return \file_put_contents($filename, $content);
    }

    protected function copyStatic($path, $dest, $recursive = true): void {
        $len = \mb_strlen($path);

        if ($recursive) {
            $worker = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path));
        } else {
            $worker = new \DirectoryIterator($path);
        }

        foreach ($worker as $x) {
            if ($x->isDir() && ($x->getFilename() == '.' || $x->getFilename() == '..')) {
                continue;
            }
            $target = $dest . \mb_substr($x->getPathname(), $len);

            if (!\file_exists(\dirname($target))) {
                \mkdir(\dirname($target), 0777, true);
            }
            \copy($x->getPathname(), $target);
        }
    }

    private function resolveImports(fDOMDocument $doc): void {
        $doc->registerNamespace('xsl', 'http://www.w3.org/1999/XSL/Transform');
        $baseDir = \dirname($doc->documentURI);

        foreach ($doc->query('/xsl:stylesheet/xsl:import') as $importNode) {
            /** @var $importNode \DOMElement */
            $import = new fDOMDocument();
            $import->load($baseDir . '/' . $importNode->getAttribute('href'));

            $newParent = $importNode->parentNode;

            foreach ($import->documentElement->childNodes as $child) {
                if ($child->localName === 'output') {
                    continue;
                }
                $importedChild = $doc->importNode($child, true);
                $newParent->insertBefore($importedChild, $importNode);
            }
            $newParent->removeChild($importNode);
        }
    }
}

class EngineException extends \Exception {
    public const UnexpectedError = 1;
}
