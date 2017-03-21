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
namespace TheSeer\phpDox\Generator\Engine {

    use TheSeer\phpDox\BuildConfig;
    use TheSeer\phpDox\FactoryInterface;

    class Factory {

        protected $engines = array();
        protected $configs = array();

        public function addEngineClass($name, $class) {
            $this->engines[$name] = $class;
        }

        public function addEngineFactory($name, FactoryInterface $factory) {
            $this->engines[$name] = $factory;
        }

        public function getEngineList() {
            return array_keys($this->engines);
        }

        public function setConfigClass($name, $class) {
            $this->configs[$name] = $class;
        }

        public function getInstanceFor(BuildConfig $buildCfg) {
            $name = $buildCfg->getEngine();
            if (!isset($this->engines[$name])) {
                throw new FactoryException("Engine '$name' is not registered.", FactoryException::UnknownEngine);
            }

            if (isset($this->configs[$name])) {
                $cfg = new $this->configs[$name]($buildCfg->getGeneratorConfig(), $buildCfg->getBuildNode());
            } else {
                $cfg = $buildCfg;
            }

            if ($this->engines[$name] instanceof FactoryInterface) {
                return $this->engines[$name]->getInstanceFor($name, $cfg);
            }
            return new $this->engines[$name]($cfg);
        }

    }

    class FactoryException extends \Exception {
        const UnknownEngine = 1;
    }
}
