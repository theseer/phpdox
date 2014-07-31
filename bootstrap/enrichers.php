<?php
/**
 * Copyright (c) 2010-2014 Arne Blankerts <arne@blankerts.de>
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
    /**
     * @var BootstrapApi $phpDox
     */

    $phpDox->registerEnricher('build', 'Build information enricher')
        ->implementedByClass('TheSeer\\phpDox\\Generator\\Enricher\\Build');

    $phpDox->registerEnricher('git', 'GIT repository information enricher')
        ->implementedByClass('TheSeer\\phpDox\\Generator\\Enricher\\Git')
        ->withConfigClass('TheSeer\\phpDox\\Generator\\Enricher\\GitConfig');

    $phpDox->registerEnricher('checkstyle', 'checkstyle.xml enricher')
        ->implementedByClass('TheSeer\\phpDox\\Generator\\Enricher\\CheckStyle')
        ->withConfigClass('TheSeer\\phpDox\\Generator\\Enricher\\CheckStyleConfig');

    $phpDox->registerEnricher('phpcs', 'phpcs.xml enricher')
        ->implementedByClass('TheSeer\\phpDox\\Generator\\Enricher\\PHPCs')
        ->withConfigClass('TheSeer\\phpDox\\Generator\\Enricher\\PHPCsConfig');

    $phpDox->registerEnricher('pmd', 'PHPMessDetector (pmd.xml) enricher')
        ->implementedByClass('TheSeer\\phpDox\\Generator\\Enricher\\PHPMessDetector')
        ->withConfigClass('TheSeer\\phpDox\\Generator\\Enricher\\PHPMessDetectorConfig');

    $phpDox->registerEnricher('phpunit', 'PHPUnit code coverage enricher')
        ->implementedByClass('TheSeer\\phpDox\\Generator\\Enricher\\PHPUnit')
        ->withConfigClass('TheSeer\\phpDox\\Generator\\Enricher\\PHPUnitConfig');

    $phpDox->registerEnricher('phploc', 'PHPLoc code statistic enricher')
        ->implementedByClass('TheSeer\\phpDox\\Generator\\Enricher\\PHPLoc')
        ->withConfigClass('TheSeer\\phpDox\\Generator\\Enricher\\PHPLocConfig');

}