<?php
/**
 * Copyright (c) 2010 Arne Blankerts <arne@blankerts.de>
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

   use \TheSeer\Tools\PHPFilterIterator;
   use \TheSeer\fDom\fDomDocument;

   class CLI {

      /**
       * Version identifier
       *
       * @var string
       */
      const VERSION = "%version%";
      
      /**
       * Base path files are stored in
       * 
       * @var string
       */
      protected $outputDir;
      
      /**
       * Array of Container DOM Documents
       *
       * @var array
       */
      protected $container = array();

      /**
       * Main executor for CLI process
       */
      public function run() {
         try {
            $input = new \ezcConsoleInput();
            $this->registerOptions($input);
            $input->process();
            
            //Display Help
            if($input->getOption('help')->value) {
                echo $input->getHelpText("PHPDox is an alternative to PHPDocumentor. It aim to generate API Documentation of PHP project, based on standard DocBlocks.");
                exit(0);
            }
            
            $this->outputDir = $input->getOption('output')->value;
            $processor = new Processor(
               $this->outputDir,
               $this->getContainerDocument('namespaces'),
               $this->getContainerDocument('interfaces'),
               $this->getContainerDocument('classes')
            );
            if ($input->getOption('public')->value) {
               $processor->setPublicOnly(true);
            }
            $processor->run($this->getScanner($input));
            $this->saveContainer();            
         } catch (\ezcConsoleException $e) {
            fwrite(STDERR, $e->getMessage()."\n");
            exit(3);
         } catch (\Exception $e) {
            fwrite(STDERR, "Error while processing request:\n");
            fwrite(STDERR, ' - ' . $e."\n");
            exit(1);
         }
      }

      protected function registerOptions(\ezcConsoleInput $input) {
         $versionOption = $input->registerOption( new \ezcConsoleOption( 'v', 'version' ) );
         $versionOption->shorthelp    = 'Prints the version and exits';
         $versionOption->isHelpOption = true;

         $helpOption = $input->registerOption( new \ezcConsoleOption( 'h', 'help' ) );
         $helpOption->isHelpOption = true;
         $helpOption->shorthelp    = 'Prints this usage information';

         $input->registerOption( new \ezcConsoleOption(
            'i', 'include', \ezcConsoleInput::TYPE_STRING, '*.php', true,
            'File pattern to include (default: *.php)'
         ));
         $input->registerOption( new \ezcConsoleOption(
            'e', 'exclude', \ezcConsoleInput::TYPE_STRING, null, true,
            'File pattern to exclude'
         ));

         $outputOption = $input->registerOption( new \ezcConsoleOption(
            'o', 'output', \ezcConsoleInput::TYPE_STRING, 'phpdox', false,
            'Output directory for generated (default: phpdox)'
         ));

         $input->registerOption( new \ezcConsoleOption(
            'p', 'public', \ezcConsoleInput::TYPE_NONE, null, false,
            'Only show public member and methods'
         ));
         
         $input->argumentDefinition = new \ezcConsoleArguments();
         $input->argumentDefinition[0] = new \ezcConsoleArgument( "directory" );
         $input->argumentDefinition[0]->shorthelp = "The directory to process.";
         
      }

      /**
       * Helper to get instance of DirectoryScanner with cli options applied
       *
       * @param ezcConsoleInput $input  CLI Options pased to app
       *
       * @return Theseer\Tools\IncludeExcludeFilterIterator
       */
      protected function getScanner(\ezcConsoleInput $input) {
         $scanner = new \TheSeer\Tools\DirectoryScanner;

         $include = $input->getOption('include');
         if (is_array($include->value)) {
            $scanner->setIncludes($include->value);
         } else {
            $scanner->addInclude($include->value);
         }

         $exclude = $input->getOption('exclude');
         if ($exclude->value) {
            if (is_array($exclude->value)) {
               $scanner->setExcludes($exclude->value);
            } else {
               $scanner->addExclude($exclude->value);
            }
         }

         $args = $input->getArguments();
         return $scanner($args[0]);
      }
      
      /**
       * Helper to load or create Container DOM Documents for namespaces, classes, interfaces, ...
       * 
       * @param $name name of the file (identical to root node) 
       * 
       * @return \TheSeer\fDom\fDomDocument
       */
      protected function getContainerDocument($name) {
         $fname = $this->outputDir . '/' . $name .'.xml';
         $dom = new fDOMDocument('1.0', 'UTF-8');         
         if (file_exists($fname)) {
            $dom->load($fname);                     
         } else {
            $rootNode = $dom->createElementNS('http://phpdox.de/xml#', $name);
            $dom->appendChild($rootNode);            
         }
         $dom->registerNamespace('dox', 'http://phpdox.de/xml#');
         $dom->formatOutput = true;
         $this->container[$fname] = $dom; 
         return $dom;
      }
      
      /**
       * Helper to save all known and (updated) container files  
       */
      protected function saveContainer() {
         foreach($this->container as $fname => $dom) {
            $dom->save($fname);
         }
      }
   }
}
