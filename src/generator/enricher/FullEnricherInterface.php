<?php declare(strict_types = 1);
namespace TheSeer\phpDox\Generator\Enricher;

interface FullEnricherInterface extends
    StartEnricherInterface,
    InterfaceEnricherInterface,
    TraitEnricherInterface,
    ClassEnricherInterface,
    EndEnricherInterface {
}
