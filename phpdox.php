#!/usr/bin/env php
<?php
/**
 * Copyright (c) 2009-2010 Arne Blankerts <arne@blankerts.de>
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
 * Exit codes:
 *   0 - No error
 *   1 - Execution Error
 *   3 - Parameter Error
 *   4 - Lint Error
 */

use \TheSeer\phpDox;
use \pdepend\reflection\Autoloader;

if (file_exists(__DIR__ . '/.git')) {
    require __DIR__ . '/lib/DirectoryScanner/autoload.php';
    require __DIR__ . '/lib/Autoload/src/phpfilter.php';
    require __DIR__ . '/lib/fDOMDocument/autoload.php';
    require __DIR__ . '/lib/staticReflection/source/pdepend/reflection/Autoloader.php';
    require __DIR__ . '/lib/docblock/DocBlock.php';
} else {
    // ...
}

require_once 'ezc/Base/base.php';

require __DIR__ . '/src/cli.php';
require __DIR__ . '/src/builder.php';
require __DIR__ . '/src/processor.php';
require __DIR__ . '/src/classbuilder.php';

spl_autoload_register( array('\ezcBase','autoload'));
spl_autoload_register( array(new Autoloader(),'autoload'));

$exec = new \TheSeer\phpDox\CLI();
$exec->run();
exit(0);
