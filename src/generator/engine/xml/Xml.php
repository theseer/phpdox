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

namespace TheSeer\phpDox\Generator\Engine {

    use TheSeer\phpDox\BuildConfig;
    use TheSeer\phpDox\Generator\AbstractEvent;
    use TheSeer\phpDox\Generator\ClassStartEvent;
    use TheSeer\phpDox\Generator\InterfaceStartEvent;
    use TheSeer\phpDox\Generator\PHPDoxEndEvent;
    use TheSeer\phpDox\Generator\TokenFileStartEvent;
    use TheSeer\phpDox\Generator\TraitStartEvent;

    class Xml extends AbstractEngine {

        private $outputDir;

        public function __construct(BuildConfig $config) {
            $this->outputDir = $config->getOutputDirectory();
        }

        public function registerEventHandlers(EventHandlerRegistry $registry) {
            $registry->addHandler('phpdox.end', $this, 'handleIndex');
            $registry->addHandler('class.start', $this, 'handle');
            $registry->addHandler('trait.start', $this, 'handle');
            $registry->addHandler('interface.start', $this, 'handle');
            $registry->addHandler('token.file.start', $this, 'handleToken');
        }

        public function handle(AbstractEvent $event) {
            if ($event instanceof ClassStartEvent) {
                $ctx = $event->getClass();
                $path = 'classes';
            } else if ($event instanceof TraitStartEvent) {
                $ctx = $event->getTrait();
                $path = 'traits';
            } else if ($event instanceof InterfaceStartEvent) {
                $ctx = $event->getInterface();
                $path = 'interfaces';
            } else {
                throw new EngineException(
                    'Unexpected Event of type ' . get_class($event),
                    XMLEngineException::UnexpectedType
                );
            }
            $dom = $ctx->asDom();
            $this->saveDomDocument($dom,
                $this->outputDir . '/' . $path . '/' . str_replace('\\', '_', $dom->documentElement->getAttribute('full')) . '.xml'
            );
        }

        public function handleIndex(PHPDoxEndEvent $event) {
            $dom = $event->getIndex()->asDom();
            $this->saveDomDocument($dom, $this->outputDir . '/index.xml');
        }

        public function handleToken(TokenFileStartEvent $event) {
            $dom = $event->getTokenFile()->asDom();
            $this->saveDomDocument($dom,
                $this->outputDir . '/tokens/' . $dom->queryOne('//phpdox:file')->getAttribute('relative') . '.xml'
            );

        }
    }

    class XMLEngineException extends EngineException {
        const UnexpectedType = 2;
    }

}
