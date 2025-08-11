<?php

namespace PitouFW\Core;

use PitouFW\Entity\ExportQueue;

abstract class Exporter
{
    protected ExportQueue $export;

    public function __construct(ExportQueue $export) {
        $this->export = $export;
    }

    public abstract function execute(): string;
}