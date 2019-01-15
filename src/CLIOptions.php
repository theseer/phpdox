<?php declare(strict_types = 1);
namespace TheSeer\phpDox;

class CLIOptions {
    private $argv;

    private $parsed;

    public function __construct(array $argv) {
        $this->argv = $argv;
    }

    public function getHelpScreen() {
        return <<<EOF
Usage: phpdox [switches]

  -f, --file       Configuration file to use (defaults to ./phpdox.xml[.dist])

  -h, --help       Prints this usage information
  -v, --version    Prints the version and exits

  -c, --collector  Run only collector process
  -g, --generator  Run only generator process

      --backends   Show a list of available backends and exit
      --engines    Show a list of available output engines and exit
      --enrichers  Show a list of available output enrichers and exit

      --skel       Show an annotated skeleton config xml file and exit
      --strip      Strip comments from skeleton config xml when showing


EOF;
    }

    public function showHelp() {
        $this->parse();

        return $this->parsed['help'];
    }

    public function showVersion() {
        $this->parse();

        return $this->parsed['version'];
    }

    public function listBackends() {
        $this->parse();

        return $this->parsed['backends'];
    }

    public function listEngines() {
        $this->parse();

        return $this->parsed['engines'];
    }

    public function listEnrichers() {
        $this->parse();

        return $this->parsed['enrichers'];
    }

    public function generateSkel() {
        $this->parse();

        return $this->parsed['skel'];
    }

    public function generateStrippedSkel() {
        $this->parse();

        return $this->parsed['strip'];
    }

    public function configFile() {
        $this->parse();

        if (!\is_string($this->parsed['file'])) {
            return '';
        }

        return $this->parsed['file'];
    }

    public function generatorOnly() {
        $this->parse();

        return $this->parsed['generator'];
    }

    public function collectorOnly() {
        $this->parse();

        return $this->parsed['collector'];
    }

    private function parse(): void {
        $options = [
            'file', 'help', 'version', 'collector', 'generator', 'engines', 'enrichers', 'backends', 'skel', 'strip'
        ];
        $shortMap = [
            'f' => 'file',
            'h' => 'help',
            'c' => 'collector',
            'g' => 'generator',
            'v' => 'version'
        ];
        $valueOptions = [
            'file'
        ];

        $conflictingOptions = [
            'collector' => ['generator'],
            'generator' => ['collector']
        ];

        $argv = $this->argv;
        \array_map('trim', $argv);

        if (isset($argv[0][0]) && $argv[0][0] != '-') {
            \array_shift($argv);
        }

        foreach ($options as $opt) {
            $this->parsed[$opt] = false;
        }

        $valueExcepted = false;
        $argName       = '';

        foreach ($argv as $arg) {
            if ($arg[0] == '-') {
                if (\strlen($arg) == 1) {
                    throw new CLIOptionsException(
                        \sprintf('Syntax error while parsing option (unnamed switch or option)')
                    );
                }

                if ($arg[1] == '-') {
                    $argName = \mb_substr($arg, 2);

                    if (!\in_array($argName, $options)) {
                        throw new CLIOptionsException(
                            \sprintf('Option "%s" is not defined', $argName)
                        );
                    }
                } else {
                    $argChar = \mb_substr($arg, 1);

                    if (!isset($shortMap[$argChar])) {
                        throw new CLIOptionsException(
                            \sprintf('Option "%s" is not defined', $argChar)
                        );
                    }
                    $argName = $shortMap[$argChar];
                }

                if (isset($conflictingOptions[$argName])) {
                    foreach ($conflictingOptions[$argName] as $conflict) {
                        if ($this->parsed[$conflict]) {
                            throw new CLIOptionsException(
                                \sprintf('Option "%s" conflicts with already set option "%s"', $argName, $conflict)
                            );
                        }
                    }
                }
                $this->parsed[$argName] = true;
                $valueExcepted          = \in_array($argName, $valueOptions);

                continue;
            }

            if (!$valueExcepted) {
                throw new CLIOptionsException(
                    \sprintf('Value for option "%s" provided but none expected', $argName)
                );
            }
            $this->parsed[$argName] = $arg;
        }
    }
}
