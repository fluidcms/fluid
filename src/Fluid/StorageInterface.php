<?php
namespace Fluid;

interface StorageInterface
{
    public function loadBranchData($filename);
    public function loadData($filename);
    public function saveBranchData($filename, array $data);
    public function saveData($filename, array $data);
    public function getBranchFileList($dir);
    public function getFileList($dir);
    public function branchFileExists($file);
    public function fileExists($file);
    public function uploadBranchFile($tmpfile, $newfile);
    public function uploadFile($tmpfile, $newfile);
}