<?php
namespace Fluid;

interface StorageInterface
{
    public function getBranchDir();
    public function getDir();
    public function loadBranchData($filename);
    public function loadData($filename);
    public function saveBranchData($filename, array $data);
    public function saveData($filename, array $data);
    public function getBranchFilename($file);
    public function getFilename($file);
    public function getBranchFileList($dir);
    public function getFileList($dir);
    public function branchFileExists($file);
    public function fileExists($file);
    public function loadBranchFile($file);
    public function loadFile($file);
    public function uploadBranchFile($tmpfile, $newfile);
    public function uploadFile($tmpfile, $newfile);
}