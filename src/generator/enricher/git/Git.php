<?php
namespace TheSeer\phpDox\Generator\Enricher {

    use TheSeer\fDOM\fDOMElement;
    use TheSeer\phpDox\EnrichConfig;
    use TheSeer\phpDox\Generator\PHPDoxStartEvent;

    class Git extends AbstractEnricher implements IndexEnricherInterface {

        private $srcDir;

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

    }

}
