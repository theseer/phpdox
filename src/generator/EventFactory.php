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
namespace TheSeer\phpDox\Generator {

    class EventFactory implements \TheSeer\phpDox\FactoryInterface {

        protected $eventTypes = array(
            'phpdox.raw' => array(),
            'phpdox.start' => array('namespaces', 'classes', 'interfaces'),
            'phpdox.end' => array('namespaces', 'classes', 'interfaces'),

            'phpdox.namespaces.start' => array('namespaces'),
            'phpdox.namespaces.end' => array('namespaces'),

            'phpdox.classes.start' => array('classes'),
            'phpdox.classes.end' => array('classes'),
            'phpdox.interfaces.start' => array('interfaces'),
            'phpdox.interfaces.end' => array('interfaces'),

            'namespace.start' => array('namespace'),
            'namespace.classes.start' => array('classes', 'namespace'),
            'namespace.classes.end' => array('classes', 'namespace'),
            'namespace.interfaces.start' => array('interfaces', 'namespace'),
            'namespace.interfaces.end' => array('interfaces', 'namespace'),
            'namespace.end' => array('namespace'),

            'class.start' => array('class'),
            'class.constant' => array('constant', 'class'),
            'class.member' => array('member', 'class'),
            'class.method' => array('method', 'class'),
            'class.end' => array('class'),

            'interface.start' => array('interface'),
            'interface.constant' => array('constant', 'interface'),
            'interface.method' => array('method', 'interface'),
            'interface.end' => array('interface')
        );

        public function getInstanceFor($name, array $payload = array()) {
            if (!isset($this->eventTypes[$name])) {
                throw new EventFactoryException("Unkown event type '$name'", EventFactoryException::UnknownType);
            }
            $data = array();
            foreach($this->eventTypes[$name] as $pos => $value) {
                $data[$value] = $payload[$pos];
            }
            return new Event($name, $data);
        }
    }

    class EventFactoryException extends \Exception {
        const UnknownType = 1;
    }
}