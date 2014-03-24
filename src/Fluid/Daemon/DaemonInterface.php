<?php

namespace Fluid\Daemon;

interface DaemonInterface
{
    public function run();
    public function runBackground();
}