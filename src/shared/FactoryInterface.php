<?php declare(strict_types = 1);
namespace TheSeer\phpDox;

interface FactoryInterface {
    public function getInstanceFor($name);
}
