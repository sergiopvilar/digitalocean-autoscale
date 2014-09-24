<?php
/**
 * Created by PhpStorm.
 * User: sergiovilar
 * Date: 23/09/14
 * Time: 5:24 PM
 */

class ScaleManager {

    private $id;
    private $config;
    private $droplet;
    private $api;

    public function __construct($id, $config){

        $this->id = $id;
        $this->config = $config;
        $this->api = new DigitalOcean($this->config->token);

        $this->getDroplet();
        $this->getStatus();

    }

    private function getStatus(){

        $ip = $this->droplet->networks->v4[0]->ip_address;
        $folder = getcwd()."/.dg";

        // Gera o arquivo
        shell_exec("ssh root@".$ip." 'top -n 1 -b > /root/status.txt'");

        // Baixa ele
        shell_exec("scp root@".$ip.":/root/status.txt $folder/".$this->droplet->name);

    }

    public function getPercentage(){

        $folder = getcwd()."/.dg";

        $file = file_get_contents($folder."/".$this->droplet->name);
        $str = explode("%Cpu(s): ", $file);
        $str2 = explode(" us,", $str[1]);

        return $str2[0];

    }

    private function getDroplet(){

        $droplets = $this->api->makeRequest('/droplets');
        $my_droplet = null;

        foreach($droplets->droplets as $droplet){

            if($droplet->name == $this->id)
                $my_droplet = $droplet;

        }

        $this->droplet = $droplet;

    }

} 