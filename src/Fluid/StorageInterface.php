<?php
namespace Fluid;

interface StorageInterface
{
    public function loadBranchData($filename);
    public function loadData($filename);
}