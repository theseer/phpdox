<?php
namespace TheSeer\phpDox\Generator\Enricher {

    use TheSeer\fDOM\fDOMDocument;
    use TheSeer\fDOM\fDOMElement;
    use TheSeer\phpDox\EnrichConfig;
    use TheSeer\phpDox\Generator\ClassStartEvent;
    use TheSeer\phpDox\Generator\InterfaceStartEvent;
    use TheSeer\phpDox\Generator\PHPDoxStartEvent;
    use TheSeer\phpDox\Generator\TraitStartEvent;

    class Git extends AbstractEnricher implements IndexEnricherInterface, InterfaceEnricherInterface, ClassEnricherInterface, TraitEnricherInterface {

        private $srcDir;
        private $noGitAvailable = false;

        /**
         * Array of tokens for git log
         * see git log --help for more details
         *
         * @var array
         */
        private $tokens = array('H','aE','aN','cE','cN','at','ct');

        public function __construct(EnrichConfig $config) {
            $this->srcDir = $config->getGeneratorConfig()->getProjectConfig()->getSourceDirectory();
        }

        /**
         * @return string
         */
        public function getName() {
            return 'GIT information';
        }

        public function enrichIndex(PHPDoxStartEvent $event) {
            $dom = $event->getIndex()->asDom();
            /** @var fDOMElement $enrichtment */
            $enrichtment = $this->getEnrichtmentContainer($dom->documentElement, 'git');

            $cwd = getcwd();
            chdir($this->srcDir);
            $describe = exec('git describe --always --dirty 2>/dev/null', $foo, $rc);
            if ($rc !== 0) {
                $enrichtment->appendChild(
                    $dom->createComment('Not a git repository or no git binary available')
                );
                chdir($cwd);
                $this->noGitAvailable = true;
                return;
            }

            exec('git tag 2>/dev/null', $tags, $rc);
            if (count($tags)) {
                $tagsNode = $enrichtment->appendElementNS(self::XMLNS, 'tags');
                foreach($tags as $tagName) {
                    $tag = $tagsNode->appendElementNS(self::XMLNS, 'tag');
                    $tag->setAttribute('name', $tagName);
                }
            }

            $currentBranch = 'master';
            exec('git branch 2>/dev/null', $branches, $rc);
            if (count($branches)) {
                $branchesNode = $enrichtment->appendElementNS(self::XMLNS, 'branches');
                foreach($branches as $branchName) {
                    $branch = $branchesNode->appendElementNS(self::XMLNS, 'branch');
                    if ($branchName[0] == '*') {
                        $branchName = trim(substr($branchName,1));
                        $currentBranch = $branchName;
                    } else {
                        $branchName = trim($branchName);
                    }
                    $branch->setAttribute('name', $branchName);
                }
            }

            $current = $enrichtment->appendElementNS(self::XMLNS, 'current');
            $current->setAttribute('describe', $describe);
            $current->setAttribute('branch', $currentBranch);

            chdir($cwd);
        }

        public function enrichClass(ClassStartEvent $event) {
            $this->enrichByFile($event->getClass()->asDom());
        }

        public function enrichInterface(InterfaceStartEvent $event) {
            $this->enrichByFile($event->getInterface()->asDom());
        }

        public function enrichTrait(TraitStartEvent $event) {
            $this->enrichByFile($event->getTrait()->asDom());
        }

        private function enrichByFile(fDOMDocument $dom) {
            if ($this->noGitAvailable) {
                return;
            }
            $fileNode = $dom->queryOne('//phpdox:file');
            if (!$fileNode) {
                return;
            }

            $enrichtment = $this->getEnrichtmentContainer($dom->documentElement, 'git');

            try {
                $log = $this->getLogHistory($fileNode->getAttribute('realpath'));
                $block = array();
                foreach($log as $line) {
                    if ($line == '[EOF]') {
                        $this->addCommit($enrichtment, $this->tokens, $block);
                        $block = array();
                        continue;
                    }
                    $block[] = $line;
                }

            } catch (GitEnricherException $e) {
                $enrichtment->appendChild(
                    $dom->createComment('GitEnricher Error: ' . $e->getMessage())
                );
            }

        }

        private function addCommit(fDOMElement $enrichment, array $tokens, array $block) {
            list($data, $text) = array_chunk($block, count($tokens));

            $data = array_combine($tokens, $data);

            $commit = $enrichment->appendElementNS(self::XMLNS, 'commit');
            $commit->setAttribute('sha1', $data['H']);

            $author = $commit->appendElementNS(self::XMLNS, 'author');
            $author->setAttribute('email', $data['aE']);
            $author->setAttribute('name', $data['aN']);
            $author->setAttribute('time', date('c', $data['at']));
            $author->setAttribute('unixtime', $data['at']);

            $commiter = $commit->appendElementNS(self::XMLNS, 'commiter');
            $commiter->setAttribute('email', $data['cE']);
            $commiter->setAttribute('name', $data['cN']);
            $commiter->setAttribute('time', date('c', $data['ct']));
            $commiter->setAttribute('unixtime', $data['ct']);

            $message = $commit->appendElementNS(self::XMLNS, 'message');
            $message->appendTextNode(trim(join("\n", $text)));
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
            $format = '%' . join('%n%',$this->tokens) . '%n%B%n[EOF]';

            $cwd = getcwd();
            if (!file_exists($filename)) {
                throw new GitEnricherException('Error getting log history for file ' . $filename . ' (file not found)', GitEnricherException::FetchingHistoryFailed);
            }
            chdir(dirname($filename));
            $fname = escapeshellarg(basename($filename));
            exec(sprintf('git log --follow --pretty=format:"%s" %s', $format, $fname), $log, $rc);
            chdir($cwd);
            if ($rc !== 0) {
                throw new GitEnricherException('Error getting log history for file ' . $filename, GitEnricherException::FetchingHistoryFailed);
            }
            return $log;
        }
    }


    class GitEnricherException extends \Exception {
        const FetchingHistoryFailed = 1;
    }
}
