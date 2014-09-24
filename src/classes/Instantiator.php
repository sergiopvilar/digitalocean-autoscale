<?php
/**
 * Created by PhpStorm.
 * User: sergiovilar
 * Date: 23/09/14
 * Time: 4:37 PM
 */

class Instantiator {

    private $id;
    private $repo;
    private $config;

    private $folder;
    private $abs_folder;
    const tmp = ".dg";

    public function __construct($id, $repository, $config){

        $this->id = $id;
        $this->repo = $repository;
        $this->config = $config;

        $this->createTempDirectory();
        $this->createVMDirectory();
        $this->cloneRepo();
        $this->updateVMData();
        $this->turnOn();

    }

    private function createTempDirectory(){

        if(!file_exists(self::tmp))
            mkdir(self::tmp);

    }

    private function createVMDirectory(){

        $this->folder = self::tmp."/".mt_rand(100000,999999);
        $this->abs_folder = getcwd()."/".$this->folder;
        mkdir($this->folder);

    }

    private function cloneRepo(){

        $command = "cd ".getcwd()."/".$this->folder."; git clone ".$this->repo." .";
        shell_exec($command);

    }

    private function updateVMData(){

        $filename = $this->abs_folder."/puphpet/config.yaml";

        $file = file_get_contents($filename);
        $file = str_replace('${token}', $this->config->token, $file);
        $file = str_replace('${hostname}', $this->id, $file);

        file_put_contents($filename, $file);

    }

    private function turnOn(){

        $command = "cd ".getcwd()."/".$this->folder."; vagrant up";
        shell_exec($command);

    }

} 