<?php
/**
 * Copyright (c) 2010-2012 Arne Blankerts <arne@blankerts.de>
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
    use TheSeer\fDOM\fDOMElement;

    class GlobalConfig {

        /**
         * @var fDOMDocument
         */
        protected $cfg;

        /**
         * Filename this config is based on
         * @var string
         */
        protected $fname;

        /**
         * Constructor for global config
         *
         * @param fDOMDocument $cfg   A configuration dom
         * @param string       $fname Filename
         */
        public function __construct(fDOMDocument $cfg, $fname) {
            $this->cfg = $cfg;
            $this->fname = $fname;
        }

        public function getFilename() {
            return $this->fname;
        }

        public function isSilentMode() {
            $root = $this->cfg->queryOne('/cfg:phpdox');
            if (!$root instanceOf \DomNode) {
                return false;
            }
            return $root->getAttribute('silent', 'false') === 'true';
        }

        public function getBootstrapFiles() {
            $files = array();
            foreach($this->cfg->query('//cfg:bootstrap/cfg:require[@file]') as $require) {
                $files[] = $require->getAttribute('file');
            }
            return $files;
        }

        public function getAvailableProjects() {
            $list = array();
            foreach ($this->cfg->query('//cfg:project[@enabled="true" or not(@enabled)]') as $pos => $project) {
                $list[] = $project->getAttribute('name') ?: $pos;
            }
            return $list;
        }

        public function getProjectConfig($project) {
            $filter = is_int($project) ? $project : "@name = '$project'";
            $ctx = $this->cfg->queryOne("//cfg:project[$filter]");
            if (!$ctx) {
                throw new ConfigException("Project '$project' not found in configuration xml file", ConfigException::ProjectNotFound);
            }
            return new ProjectConfig($this->runResolver($ctx));
        }

        protected function runResolver($ctx) {
            if (defined('PHPDOX_VERSION') && constant('PHPDOX_VERSION')=='%development%') {
                $home = realpath(__DIR__.'/../../');
            } else {
                $home = realpath(__DIR__.'/../');
            }

            $vars = array(
                'basedir' => $ctx->getAttribute('basedir', dirname($this->fname)),

                'phpDox.home' => $home,
                'phpDox.file' => $this->fname,
                'phpDox.version' => defined('PHPDOX_VERSION') ? PHPDOX_VERSION : '*UNKNOWN*',

                'phpDox.project.name' => $ctx->getAttribute('name', 'unnamed'),
                'phpDox.project.source' => $ctx->getAttribute('source', 'src'),
                'phpDox.project.workdir' => $ctx->getAttribute('workdir','xml'),

                'phpDox.php.version' => PHP_VERSION,

            );
            $protected = array_keys($vars);

            foreach($ctx->query('cfg:property') as $property) {
                $name = $property->getAttribute('name');
                if (!in_array($name, $protected)) {
                    $vars[$name] =  $this->resolveValue($property->getAttribute('value'), $vars);
                }
            }

            foreach($ctx->query('.//*[not(name()="property")]/@*') as $attr) {
                $attr->nodeValue = $this->resolveValue($attr->nodeValue, $vars);
            }

            return $ctx;
        }

        protected function resolveValue($value, Array $vars) {
            return preg_replace_callback('/\${(.*?)}/', function($matches) use ($vars) { return isset($vars[$matches[1]]) ? $vars[$matches[1]] : $matches[0]; }, $value);
        }

    }

    class ConfigException extends \Exception {

        const ProjectNotFound = 1;
        const NoCollectorSection = 2;
        const NoGeneratorSection = 3;
    }
}
