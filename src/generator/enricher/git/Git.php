<?php declare(strict_types = 1);
namespace TheSeer\phpDox\Generator\Enricher;

use TheSeer\fDOM\fDOMDocument;
use TheSeer\fDOM\fDOMElement;
use TheSeer\phpDox\Generator\ClassStartEvent;
use TheSeer\phpDox\Generator\InterfaceStartEvent;
use TheSeer\phpDox\Generator\PHPDoxEndEvent;
use TheSeer\phpDox\Generator\PHPDoxStartEvent;
use TheSeer\phpDox\Generator\TraitStartEvent;

class Git extends AbstractEnricher implements FullEnricherInterface {
    public const GITNS = 'http://xml.phpdox.net/gitlog';

    /**
     * @var bool
     */
    private $noGitAvailable = false;

    /**
     * Array of tokens for git log
     * see git log --help for more details
     *
     * @var array
     */
    private $tokens = ['H', 'aE', 'aN', 'cE', 'cN', 'at', 'ct'];

    /**
     * @var GitConfig
     */
    private $config;

    /**
     * @var fDOMDocument
     */
    private $cacheDom;

    /**
     * @var bool
     */
    private $cacheDirty = false;

    /**
     * @var string
     */
    private $commitSha1;

    public function __construct(GitConfig $config) {
        $this->ensureExecFunctionEnabled();
        $this->ensureGitVersionSupported($config->getGitBinary());
        $this->config = $config;
    }

    public function getName(): string {
        return 'GIT information';
    }

    public function enrichStart(PHPDoxStartEvent $event): void {
        $dom = $event->getIndex()->asDom();
        /** @var fDOMElement $enrichtment */
        $enrichtment = $this->getEnrichtmentContainer($dom->documentElement, 'git');

        $binary = $this->config->getGitBinary();

        $devNull = \mb_strtolower(\mb_substr(\PHP_OS, 0, 3)) == 'win' ? 'nul' : '/dev/null';

        $cwd = \getcwd();
        \chdir($this->config->getSourceDirectory()->getPathname());
        $describe = \exec($binary . ' describe --always --dirty 2>' . $devNull, $foo, $rc);

        if ($rc !== 0) {
            $enrichtment->appendChild(
                $dom->createComment('Not a git repository or no git binary available')
            );
            \chdir($cwd);
            $this->noGitAvailable = true;

            return;
        }

        \exec($binary . ' tag 2>' . $devNull, $tags, $rc);

        if (\count($tags)) {
            $tagsNode = $enrichtment->appendElementNS(self::GITNS, 'tags');

            foreach ($tags as $tagName) {
                $tag = $tagsNode->appendElementNS(self::GITNS, 'tag');
                $tag->setAttribute('name', $tagName);
            }
        }

        $currentBranch = 'master';
        \exec($binary . ' branch --no-color 2>' . $devNull, $branches, $rc);

        if (\count($branches)) {
            $branchesNode = $enrichtment->appendElementNS(self::GITNS, 'branches');

            foreach ($branches as $branchName) {
                $branch = $branchesNode->appendElementNS(self::GITNS, 'branch');

                if ($branchName[0] == '*') {
                    $branchName    = \trim(\mb_substr($branchName, 1));
                    $currentBranch = $branchName;
                } else {
                    $branchName = \trim($branchName);
                }
                $branch->setAttribute('name', $branchName);
            }
        }

        $current = $enrichtment->appendElementNS(self::GITNS, 'current');
        $current->setAttribute('describe', $describe);
        $current->setAttribute('branch', $currentBranch);

        $this->commitSha1 = \exec($binary . ' rev-parse HEAD 2>' . $devNull);
        $current->setAttribute('commit', $this->commitSha1);

        \chdir($cwd);
    }

    public function enrichClass(ClassStartEvent $event): void {
        $this->enrichByFile($event->getClass()->asDom());
    }

    public function enrichInterface(InterfaceStartEvent $event): void {
        $this->enrichByFile($event->getInterface()->asDom());
    }

    public function enrichTrait(TraitStartEvent $event): void {
        $this->enrichByFile($event->getTrait()->asDom());
    }

    public function enrichEnd(PHPDoxEndEvent $event): void {
        if ($this->cacheDirty) {
            $path = \dirname($this->config->getLogfilePath());

            if (!\file_exists($path)) {
                \mkdir($path, 0777, true);
            }
            $this->cacheDom->save($this->config->getLogfilePath());
        }
    }

    private function enrichByFile(fDOMDocument $dom): void {
        if ($this->noGitAvailable) {
            return;
        }
        $fileNode = $dom->queryOne('//phpdox:file');

        if (!$fileNode) {
            return;
        }

        /** @var fDOMElement $enrichtment */
        $enrichtment = $this->getEnrichtmentContainer($dom->documentElement, 'git');

        if (!$this->config->doLogProcessing()) {
            $enrichtment->appendChild(
                $dom->createComment('GitEnricher: Log processing disabled in configuration ')
            );

            return;
        }

        if ($this->loadFromCache($fileNode, $enrichtment)) {
            return;
        }

        try {
            $count = 0;
            $limit = $this->config->getLogLimit();
            $log   = $this->getLogHistory($fileNode->getAttribute('realpath'));
            $block = [];

            foreach ($log as $line) {
                if ($line == '[EOF]') {
                    $this->addCommit($enrichtment, $this->tokens, $block);
                    $block = [];
                    $count++;

                    if ($count > $limit) {
                        break;
                    }

                    continue;
                }
                $block[] = $line;
            }

            $this->addToCache($fileNode, $enrichtment);
        } catch (GitEnricherException $e) {
            $enrichtment->appendChild(
                $dom->createComment('GitEnricher Error: ' . $e->getMessage())
            );
        }
    }

    private function addCommit(fDOMElement $enrichment, array $tokens, array $block): void {
        [$data, $text] = \array_chunk($block, \count($tokens));

        $data = \array_combine($tokens, $data);

        $commit = $enrichment->appendElementNS(self::GITNS, 'commit');
        $commit->setAttribute('sha1', $data['H']);

        $author = $commit->appendElementNS(self::GITNS, 'author');
        $author->setAttribute('email', $data['aE']);
        $author->setAttribute('name', $data['aN']);
        $author->setAttribute('time', \date('c', (int)$data['at']));
        $author->setAttribute('unixtime', $data['at']);

        $commiter = $commit->appendElementNS(self::GITNS, 'commiter');
        $commiter->setAttribute('email', $data['cE']);
        $commiter->setAttribute('name', $data['cN']);
        $commiter->setAttribute('time', \date('c', (int)$data['ct']));
        $commiter->setAttribute('unixtime', $data['ct']);

        $message = $commit->appendElementNS(self::GITNS, 'message');
        $message->appendTextNode(\trim(\implode("\n", $text)));
    }

    private function getLogHistory($filename) {
        /*
         * H:8283723b40725a91c684e27c0c0449b959a48740
         * aE:Arne@Blankerts.de
         * aN:Arne Blankerts
         * cE:Arne@Blankerts.de
         * cN:Arne Blankerts
         * at:1375611883
         * ct:1375836305
         * {commit message text}
         * [EOF]
         *
         * see git log --help for more details
         *
         * The logic of addCommit assumes the commit message to be last
         */
        $format = '%' . \implode('%n%', $this->tokens) . '%n%B%n[EOF]';

        $cwd = \getcwd();

        if (!\file_exists($filename)) {
            throw new GitEnricherException('Error getting log history for file ' . $filename . ' (file not found)', GitEnricherException::FetchingHistoryFailed);
        }
        \chdir(\dirname($filename));
        $fname = \escapeshellarg(\basename($filename));
        \exec(\sprintf($this->config->getGitBinary() . ' log --no-color --follow --pretty=format:"%s" %s', $format, $fname), $log, $rc);
        \chdir($cwd);

        if ($rc !== 0) {
            throw new GitEnricherException('Error getting log history for file ' . $filename, GitEnricherException::FetchingHistoryFailed);
        }

        return $log;
    }

    private function loadFromCache(fDOMElement $fileNode, fDOMElement $enrichment) {
        $dom    = $this->getCacheDom();
        $fields = [
            'path' => $fileNode->getAttribute('path'),
            'file' => $fileNode->getAttribute('file')
        ];
        $query     = $dom->prepareQuery('//*[@path = :path and @file = :file]', $fields);
        $cacheNode = $dom->queryOne($query);

        if (!$cacheNode) {
            return false;
        }

        foreach ($cacheNode->childNodes as $child) {
            $enrichment->appendChild(
                $enrichment->ownerDocument->importNode($child, true)
            );
        }

        return true;
    }

    private function addToCache(fDOMElement $fileNode, fDOMElement $enrichment): void {
        $dom    = $this->getCacheDom();
        $import = $dom->createElementNS(self::GITNS, 'file');

        foreach ($fileNode->attributes as $attr) {
            $import->appendChild(
                $dom->importNode($attr)
            );
        }

        foreach ($enrichment->childNodes as $node) {
            $import->appendChild(
                $dom->importNode($node, true)
            );
        }
        $dom->documentElement->appendChild($import);
        $this->cacheDirty = true;
    }

    private function getCacheDom() {
        if ($this->cacheDom === null) {
            $this->cacheDom = new fDOMDocument();
            $cacheFile      = $this->config->getLogfilePath();

            if (\file_exists($cacheFile)) {
                $this->cacheDom->load($cacheFile);

                $sha1 = $this->cacheDom->documentElement->getAttribute('sha1');
                $cwd  = \getcwd();
                \chdir($this->config->getSourceDirectory()->getPathname());
                \exec($this->config->getGitBinary() . ' diff --name-only ' . $sha1, $files, $rc);

                foreach ($files as $file) {
                    $fields = [
                        'path' => \dirname($file),
                        'file' => \basename($file)
                    ];
                    $query = $this->cacheDom->prepareQuery('//*[@path = :path and @file = :file]', $fields);
                    $node  = $this->cacheDom->queryOne($query);

                    if (!$node) {
                        continue;
                    }
                    $node->parentNode->removeChild($node);
                }
                \chdir($cwd);
            } else {
                $this->cacheDom->loadXML('<?xml version="1.0" ?><gitlog xmlns="' . self::GITNS . '" />');
                $this->cacheDom->documentElement->setAttribute('sha1', $this->commitSha1);
            }
        }

        return $this->cacheDom;
    }

    /**
     * @throws GitEnricherException
     */
    private function ensureExecFunctionEnabled(): void {
        if (\strpos(\ini_get('disable_functions'), 'exec') !== false) {
            throw new GitEnricherException(
                'The use of "exec" has been disabled in php.ini but is required for this enricher',
                GitEnricherException::ExecDisabled
            );
        }
    }

    private function ensureGitVersionSupported($binary): void {
        $output  = \exec(\sprintf('%s --version', $binary));
        if (\preg_match('/Apple Git/', $output)) {
            $output = \preg_replace('/\s+\(Apple Git-\d+(\.\d+)?\)/', '', $output);
        }
        $parts   = \explode(' ', $output);
        $version = \array_pop($parts);

        if (\version_compare($version, '1.7.2', '<')) {
            throw new GitEnricherException(
                \sprintf('Your version of GIT is too old. Please upgrade to at least version 1.7.2 (Found: %s)', $version),
                GitEnricherException::GitVersionTooOld
            );
        }
    }
}
