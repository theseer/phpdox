<?php
/**
 * Copyright (c) 2015 Arne Blankerts <arne@blankerts.de>
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
namespace TheSeer\phpDox {

    class DirectoryCleanerTest extends \PHPUnit\Framework\TestCase {

        /**
         * @var DirectoryCleaner
         */
        private $cleaner;

        protected function setUp() {
            $this->cleaner = new DirectoryCleaner();
        }

        /**
         * @expectedException \TheSeer\phpDox\DirectoryCleanerException
         * @expectedExceptionCode \TheSeer\phpDox\DirectoryCleanerException::SecurityLimitation
         */
        public function testTryingToDeleteAShortPathThrowsException() {
            $this->cleaner->process(new FileInfo('/tmp'));
        }

        public function testTryingToDeleteNonExistingDirectoryJustReturns() {
            $this->cleaner->process(new FileInfo('/not/existing/directory'));
            $this->assertTrue(true);
        }

        public function testCanDeleteRecursiveDirectoryStructure() {
            $base = '/tmp/'. uniqid('dctest-');
            $path = $base . '/a/b/c/d/e/f/g/h';
            mkdir( $path, 0700, true);
            touch( $path . '/test-h.txt' );
            touch( $path . '/../test-g.txt' );
            touch( $path . '/../../test-f.txt' );

            $this->assertTrue(file_exists($path. '/test-h.txt'));
            $this->assertTrue(is_dir($path));

            $this->cleaner->process(new FileInfo($base));

            $this->assertFalse(file_exists($path. '/test-h.txt'), 'File vanished');
            $this->assertFalse(is_dir($base), 'Directory vanished');

        }


    }

}
