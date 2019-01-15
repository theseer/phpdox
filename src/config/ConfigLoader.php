<?php declare(strict_types = 1);
namespace TheSeer\phpDox;

use TheSeer\fDOM\fDOMDocument;
use TheSeer\fDOM\fDOMException;

class ConfigLoader {
    public const XMLNS = 'http://xml.phpdox.net/config';

    /**
     * @var FileInfo
     */
    private $homeDir;

    /**
     * @var Version
     */
    private $version;

    public function __construct(Version $version, FileInfo $homeDir) {
        $this->version = $version;
        $this->homeDir = $homeDir;
    }

    /**
     * @param $fname
     *
     * @throws ConfigLoaderException
     */
    public function load($fname): GlobalConfig {
        if (!\file_exists($fname)) {
            throw new ConfigLoaderException("Config file '$fname' not found", ConfigLoaderException::NotFound);
        }

        return $this->createInstanceFor($fname);
    }

    /**
     * @throws ConfigLoaderException
     */
    public function autodetect(): GlobalConfig {
        $candidates = [
            './phpdox.xml',
            './phpdox.xml.dist'
        ];

        foreach ($candidates as $fname) {
            if (!\file_exists($fname)) {
                continue;
            }

            return $this->createInstanceFor($fname);
        }

        throw new ConfigLoaderException('None of the candidate files found', ConfigLoaderException::NeitherCandidateExists);
    }

    /**
     * @param $fname
     *
     * @throws ConfigLoaderException
     */
    private function createInstanceFor($fname): GlobalConfig {
        $dom = $this->loadFile($fname);
        $this->ensureCorrectNamespace($dom);
        $this->ensureCorrectRootNodeName($dom);

        return new GlobalConfig($this->version, $this->homeDir, $dom, new FileInfo($fname));
    }

    /**
     * @param $fname
     *
     * @throws ConfigLoaderException
     */
    private function loadFile($fname): fDOMDocument {
        try {
            $dom = new fDOMDocument();
            $dom->load($fname);
            $dom->registerNamespace('cfg', self::XMLNS);

            return $dom;
        } catch (fDOMException $e) {
            throw new ConfigLoaderException(
                "Parsing config file '$fname' failed.",
                ConfigLoaderException::ParseError,
                $e
            );
        }
    }

    /**
     * @throws ConfigLoaderException
     */
    private function ensureCorrectNamespace(fDOMDocument $dom): void {
        if ($dom->documentElement->namespaceURI != self::XMLNS) {
            throw new ConfigLoaderException(
                \sprintf(
                    "The configuratin file '%s' uses a wrong or outdated xml namespace.\n" .
                    "Please ensure it uses 'http://xml.phpdox.net/config'",
                    $dom->documentURI
                ),
                ConfigLoaderException::WrongNamespace
            );
        }
    }

    /**
     * @throws ConfigLoaderException
     */
    private function ensureCorrectRootNodeName(fDOMDocument $dom): void {
        if ($dom->documentElement->localName != 'phpdox') {
            throw new ConfigLoaderException(
                \sprintf(
                    "The file '%s' does not seem to be a phpdox configration file.",
                    $dom->documentURI
                ),
                ConfigLoaderException::WrongType
            );
        }
    }
}
