<?php
namespace TheSeer\phpDox\Generator\Enricher {

    interface FullEnricherInterface extends
        StartEnricherInterface,
        InterfaceEnricherInterface,
        TraitEnricherInterface,
        ClassEnricherInterface,
        EndEnricherInterface
    {
    }

}
