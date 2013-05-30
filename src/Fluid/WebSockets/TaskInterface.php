<?php

namespace Fluid\WebSockets;

interface TaskInterface
{
    public function message(array $data);
    public function execute();
    public function getLastExecutionTime();
    public function getInterval();
    public function getKey();
}