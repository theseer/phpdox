# Changelog of phpDox

## phpDox 0.10.1 (2 Juli 2017)

### Changed
* Added warning to output if PHP <7.0 is used

### Fixed
* Parameter and Varible type resolving issues (partially regression from [#300](https://github.com/theseer/phpdox/issues/300)) 

## phpDox 0.10.0 (16 June 2017)

### Added
* [#300](https://github.com/theseer/phpdox/issues/300): Support for PHP 7.1 nullable type
* Added support for PHP 7 style return types

### Changed
* Bump minimum PHP version to 5.5
* Use project source directory setting for relation lookup in PHPUnit logs
* Add additional check to ensure correct file formats
* Changed autoload handling for vendor directory
* Updated PHPParser to latest
* Updated other dependencies
* Use phive for tool dependencies
* Updated php core classes and interfaces data

### Fixed
* [#273](https://github.com/theseer/phpdox/issues/273): Overview "Average Class Length" and "Average Method Length" Display NaN
* [#304](https://github.com/theseer/phpdox/issues/304): Fix phpcs not actually enriching
* [#255](https://github.com/theseer/phpdox/issues/255): Failed to load external entity components.xsl
* [#288](https://github.com/theseer/phpdox/issues/288): Generate Error

## phpDox 0.9.0 (26 August 2016)

* Added check if disable_functions forbids use of exec
* Updated to PHPParser 3.0.0
* Raised minimum PHP version to 5.5 due to PHPParser 3
* Removed PHP 5.3 support
* Update Error handling to support PHP7's \Throwable
* Fix: Issue #244 (Error : Unterminated comment starting line 1)
* Fix: Issue #243 (Trait in Trait causes UnitObjectException if the nested trait is missing)
* Fix: Issue #266 (Page Title "phpdox - Test Method")

## phpDox 0.8.1.1 (09 Juli 2015)

* Fix phar building, remove pear building target

## phpDox 0.8.1 (30 June 2015)

* Updated Dependency (PHPParser 1.3.0, PHP-Timer 1.0.6)
* Added new exit codes for issues with environment and config
* Explicitly handle (crasher) exceptions with a dedicated exit code
* Fix: Issue #230 (collector does not detect "static" modifier)
* Fix: Issue #232 (native "null" return type is parsed as "object")
* Fix: Issue #235 (paths for php-timer changed)
* Fix: Issue #225 (Error in GlobalConfig with hhvm)
* Fix: Issue #226 (vendor include at binary file)

## phpDox 0.8.0 (06 May 2015)

* Updated Dependency (PHPParser 1.2.2, fDOMDocument 1.6.0)
* PHP 7 Compatiblity changes
* Added support for @var self and @return self
* Preserve original name when alias is used
* Trait usage now rendered and resolved
* Added Token XML output
* Added Source HTML output
* Updated XSL Templates
* Fix: Issue #214 (Oups... phpDox encountered a problem... with DirectoryCleaner)
* Fix: Issue #211 (Compilation failed: support for \P, \p, and \X has not been compiled at offset 31)
* Fix: Issue #208 (class constants of type boolean are not fetched)
* Fix: Issue #190 (AbstractUnitObject.php (Line 542): Call to a member function getAttribute() on null)
* Fix: Issue #178 (makedir() problem if not root user / AbstractEngine)
* Fix: Issue #164, #165, #166 (TokenFileException - file not found)
* Fix: Issue #218: Ensure git cache directory exists before trying to write to it
* Merge PR [#199](https://github.com/theseer/phpdox/pull/199): Fix the "Source" links extensions
* Merge PR [#198](https://github.com/theseer/phpdox/pull/198): Change build state logic
* Merge PR [#196](https://github.com/theseer/phpdox/pull/196): fix array to string conversion notices
* Merge PR [#194](https://github.com/theseer/phpdox/pull/194): Update phpdox
* Merge PR [#183](https://github.com/theseer/phpdox/pull/183): Vendor directory location change for phpdox as dependency
* Merge PR [#180](https://github.com/theseer/phpdox/pull/180): add PHPDOX_HOME, instead of PHPDOX_PHAR
* Merge PR [#163](https://github.com/theseer/phpdox/pull/163): Drop now useless requirement on ZetaComponents
* Merge PR [#219](https://github.com/theseer/phpdox/pull/219): Make GlobalConfig::resolveValue() recursive again

## phpDox 0.7.0 (11 September 2014)

* Fix: Set default resolution of ${basename} to dirname of realpath of config file instead of only relative dir
* Fix: Crash on invalid encoding / control chars in source (Issue #146, #148)
* Fix: Crash on empty namespace name (Issue #150)
* Fix: Broken cache handling for files that no longer exist (Issue #149)
* Fix: DocBlock parsing generates invalid tag names in xml in some cases (Thanks to Reno Reckling)
* Fix: Crash on custom bootstrapping (Thanks to Sebastian Heuer)
* Updated XSL Templates
* Added tokenizer xml and highlighted source output
* Added support for native PHPCS xml format (Thanks to Reno Reckling)
* Removed dependency to Zeta Components by own (simplified) implementation
* Unified xml namespace uri format by stripping the # where it was still in place
* Minor performance tweaks

## phpDox 0.6.6.1 (4 May 2014, composer only)

* Fix: Issue with composer based installs

## phpDox 0.6.6 (4 May 2014)

* Updated Dependencies (DirectoryScanner to 1.3.0, PHPParser to 1.0.0, fDOMDocument to 1.5.0)
* Internal Adjustments for updated Dependencies and API Changes
* Enhanced HTML Output for Parameter lists
* Refactored @inheritdoc support
* Better Windows support (Thanks to Thomas Weinert)
* Added XSD for phpdox.xml config file (Thanks to Thomas Weinert)
* Enhanced PHPUnit enricher to not claim empty classes are untested

## phpDox 0.6.5 (17 Feb 2014)

* Pear installation now a PHAR wrapper only

## phpDox 0.6.4.1 (17 Feb 2014)

* Fix Regression: getPath() shouldn't call getPathname()

## phpDox 0.6.4 (17 Feb 2014)

* Added PHPUnit enricher
* Added Coverage enriching
* Simplified bootstrapping
* Updated XSL Templats

## phpDox 0.6.3 (8 Jan 2014)

* Performance optimizations for enrichers
* Updated XSL Templates
* Fix git enricher issues with older GIT versions
* Enhanced error reporting

## phpDox 0.6.2 (19 Dez 2013)

* Fix SourceFile to actually return cleaned code
* Ensure ext/mbstring is loaded

## phpDox 0.6.1 (19 Dec 2013)

* Updated XSL Templates
* Upgraded Dependencies (DirectoryScanner to 1.2.1)
* Merge PR #122
* Merge PR #119 (Fixes issue #118)

## phpDox 0.6 (3 Dec 2013)

* Updated Dependencies (PHPParser 0.9.4)
* Updated XSL Templates

## phpDox 0.5 (5 May 2013)

* Added support for inheritance resolving
* Removed StaticReflection
* PHPParser based backend

## phpDox 0.4 (15 Jan 2012)

* Introduced xml based configuration
* New Templates

## phpDox 0.3 (23 Nov 2011)
