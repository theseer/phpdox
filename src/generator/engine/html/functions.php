<?php
namespace TheSeer\phpDox\Generator\Engine\Html {

    use TheSeer\fDOM\fDOMDocument;
    use TheSeer\fDOM\fDOMElement;
    use TheSeer\fXSL\fXSLTProcessor;

    class Functions {

        protected $projectNode;

        protected $indexDom;

        protected $dom;
        protected $extension;
        protected $links = array();

        public function __construct(fDOMElement $project, fDOMDocument $index, $extension = 'xhtml') {
            $this->projectNode = $project;
            $this->indexDom = $index;
            $this->extension = $extension;

            // Helper to create Nodes with
            $this->dom = new fDOMDocument();
        }

        public function version() {
            return \TheSeer\phpDox\Version::getVersion();
        }

        public function info() {
            return \TheSeer\phpDox\Version::getGeneratedByString();
        }

        public function classNameToFileName($class, $method = NULL) {
            $name = str_replace('\\', '_', $class);
            if ($method !== NULL) {
                $name .= '/' . $method;
            }
            return $name . '.' . $this->extension;
        }

        public function getProjectNode() {
            return $this->projectNode;
        }

    }
}
