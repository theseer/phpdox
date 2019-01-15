<?php declare(strict_types = 1);
namespace TheSeer\phpDox\Collector\Backend;

use TheSeer\phpDox\Factory as MasterFactory;

class Factory {
    /** @var MasterFactory */
    private $master;

    public function __construct(MasterFactory $factory) {
        $this->master = $factory;
    }

    public function getInstanceFor($type) {
        switch ($type) {
            case 'parser':
                {
                    return new PHPParser(
                        $this->master->getDocblockParser(),
                        $this->master->getErrorHandler()
                    );
                }
            default:
                {
                    throw new FactoryException("'$type' is not a known backend.");
                }
        }
    }
}

class FactoryException extends \Exception {
}
