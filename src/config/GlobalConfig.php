<?php
/**
 * Copyright (c) 2010-2017 Arne Blankerts <arne@blankerts.de>
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without modification,
 * are permitted provided that the following conditions are met:
 *
 *   * Redistributions of source code must retain the above copyright notice,
 *     this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright notice,
 *     this list of conditions and the following disclaimer in the documentation
 *     and/or other materials provided with the distribution.
 *
 *   * Neither the name of Arne Blankerts nor the names of contributors
 *     may be used to endorse or promote products derived from this software
 *     without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT  * NOT LIMITED TO,
 * THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR
 * PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER ORCONTRIBUTORS
 * BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY,
 * OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @package    phpDox
 * @author     Arne Blankerts <arne@blankerts.de>
 * @copyright  Arne Blankerts <arne@blankerts.de>, All rights reserved.
 * @license    BSD License
 *
 */

namespace TheSeer\phpDox {

    use TheSeer\fDOM\fDOMDocument;

    class GlobalConfig {

        /**
         * @var Version
         */
        private $version;

        /**
         * Directory of phpDox home
         * @var Fileinfo
         */
        private $homeDir;

        /**
         * @var fDOMDocument
         */
        private $cfg;

        /**
         * File this config is based on
         * @var FileInfo
         */
        private $file;

        /**
         * Constructor for global config
         *
         * @param Version      $version
         * @param FileInfo     $home
         * @param fDOMDocument $cfg  A configuration dom
         * @param FileInfo     $file FileInfo of the cfg file
         *
         * @throws ConfigException
         */
        public function __construct(Version $version, FileInfo $home, fDOMDocument $cfg, FileInfo $file) {
            if ($cfg->documentElement->nodeName != 'phpdox' ||
                $cfg->documentElement->namespaceURI != 'http://xml.phpdox.net/config') {
                throw new ConfigException("Not a valid phpDox configuration", ConfigException::InvalidDataStructure);
            }
            $this->homeDir = $home;
            $this->version = $version;
            $this->cfg = $cfg;
            $this->file = $file;
        }

        /**
         * @return FileInfo
         */
        public function getConfigFile() {
            return $this->file;
        }

        /**
         * @return bool
         */
        public function isSilentMode() {
            $root = $this->cfg->queryOne('/cfg:phpdox');
            return $root->getAttribute('silent', 'false') === 'true';
        }

        /**
         * @return FileInfoCollection
         */
        public function getCustomBootstrapFiles() {
            $files = new FileInfoCollection();
            foreach($this->cfg->query('//cfg:bootstrap/cfg:require[@file]') as $require) {
                $files->add(new FileInfo($require->getAttribute('file'))) ;
            }
            return $files;
        }

        /**
         * @return array
         */
        public function getProjects() {
            $list = array();
            foreach ($this->cfg->query('//cfg:project[@enabled="true" or not(@enabled)]') as $pos => $project) {
                $list[$project->getAttribute('name', $pos)] = new ProjectConfig($this->version, $this->homeDir, $this->runResolver($project));
            }
            return $list;
        }

        /**
         * @param $ctx
         *
         * @return mixed
         * @throws ConfigException
         */
        private function runResolver($ctx) {
            $vars = array(
                'basedir' => $ctx->getAttribute('basedir', dirname($this->file->getRealPath())),

                'phpDox.home' => $this->homeDir->getPathname(),
                'phpDox.file' => $this->file->getPathname(),
                'phpDox.version' => $this->version->getVersion(),

                'phpDox.project.name' => $ctx->getAttribute('name', 'unnamed'),
                'phpDox.project.source' => $ctx->getAttribute('source', 'src'),
                'phpDox.project.workdir' => $ctx->getAttribute('workdir', 'xml'),

                'phpDox.php.version' => PHP_VERSION,

            );
            $protected = array_keys($vars);

            foreach($ctx->query('cfg:property|/cfg:phpdox/cfg:property') as $property) {
                /** @var $property \DOMElement */
                $name = $property->getAttribute('name');
                $line = $property->getLineNo();

                if (in_array($name, $protected)) {
                    throw new ConfigException("Cannot overwrite system property in line $line", ConfigException::OverrideNotAllowed);
                }
                if (isset($vars[$name])) {
                    throw new ConfigException("Cannot overwrite existing property '$name' in line $line", ConfigException::OverrideNotAllowed);
                }
                $vars[$name] =  $this->resolveValue($property->getAttribute('value'), $vars, $line);
            }

            foreach($ctx->query('.//*[not(name()="property")]/@*|@*') as $attr) {
                $attr->nodeValue = $this->resolveValue($attr->nodeValue, $vars, $attr->getLineNo());
            }

            return $ctx;
        }

        /**
         * @param string   $value
         * @param string[] $vars
         * @param int      $line
         *
         * @return string
         */
        private function resolveValue($value, Array $vars, $line) {
            $result = preg_replace_callback('/\${(.*?)}/',
                function($matches) use ($vars, $line) {
                    if (!isset($vars[$matches[1]])) {
                        throw new ConfigException("No value for property '{$matches[1]}' found in line $line", ConfigException::PropertyNotFound);
                    }
                    return $vars[$matches[1]];
                }, $value);
            if (preg_match('/\${(.*?)}/', $result)) {
                $result = $this->resolveValue($result, $vars, $line);
            }
            return $result;
        }

    }

}
