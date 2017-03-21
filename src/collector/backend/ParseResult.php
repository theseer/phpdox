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
     */
namespace TheSeer\phpDox\Collector\Backend {

    use TheSeer\fDOM\fDOMDocument;
    use TheSeer\phpDox\Collector\TraitObject;
    use TheSeer\phpDox\Collector\InterfaceObject;
    use TheSeer\phpDox\Collector\ClassObject;

    /**
     *
     */
    class ParseResult {

        /**
         * @var \SplFileInfo
         */
        private $file;

        /**
         * @var ClassObject[]
         */
        private $classes = array();

        /**
         * @var InterfaceObject[]
         */
        private $interfaces  = array();

        /**
         * @var TraitObject[]
         */
        private $traits  = array();

        /**
         * @param \SplFileInfo $file
         */
        public function __construct(\SplFileInfo $file) {
            $this->file = $file;
        }

        public function getFileName() {
            return $this->file->getRealPath();
        }

        /**
         * @param $name
         * @return ClassObject
         */
        public function addClass($name) {
            $obj = new ClassObject($name, $this->file);
            $this->classes[$name] = $obj;
            return $obj;
        }

        /**
         * @param $name
         * @return InterfaceObject
         */
        public function addInterface($name) {
            $obj = new InterfaceObject($name, $this->file);
            $this->interfaces[$name] = $obj;
            return $obj;
        }

        /**
         * @param $name
         * @return TraitObject
         */
        public function addTrait($name) {
            $obj = new TraitObject($name, $this->file);
            $this->traits[$name] = $obj;
            return $obj;
        }

        /**
         * @return bool
         */
        public function hasClasses() {
            return count($this->classes) > 0;
        }

        /**
         * @return bool
         */
        public function hasInterfaces() {
            return count($this->interfaces) > 0;
        }

        /**
         * @return bool
         */
        public function hasTraits() {
            return count($this->traits) > 0;
        }

        /**
         * @return ClassObject[]
         */
        public function getClasses() {
            return $this->classes;
        }

        /**
         * @return InterfaceObject[]
         */
        public function getInterfaces() {
            return $this->interfaces;
        }

        /**
         * @return TraitObject[]
         */
        public function getTraits() {
            return $this->traits;
        }
    }

}
