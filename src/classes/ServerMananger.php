<?php
/**
 * Created by PhpStorm.
 * User: sergiovilar
 * Date: 23/09/14
 * Time: 4:40 PM
 */

class ServerMananger {

    private $config;
    private $api;

    const MAX_CPU_USAGE = 90;

    public function __construct(){

        $this->config = AppConfig::getInstance()->get();
        $this->api = new DigitalOcean($this->config->config->token);

        $this->checkDropletsExitence();

    }

    private function checkDropletsExitence(){

        $droplets = $this->api->makeRequest('/droplets');

        foreach((array) $this->config->boxes as $box => $type){

            // Get the repo
            $repo_url = null;

            foreach((array) $this->config->config->boxes as $repo){
                if($repo->name == $box)
                    $repo_url = $repo->source;
            }

            $bs = [];

            foreach($droplets->droplets as $droplet){

                if(strpos($droplet->name, $box) > -1 && strpos($droplet->name, $this->config->config->app_id) > -1){

                    // Get Droplet CPU usage
                    $p = new ScaleManager($droplet->name, $this->config->config);
                    $bs[] = array('droplet' => $droplet, 'percent' => $p->getPercentage());

                }

            }

            if(count($bs) == 0){

                echo "There is no server, turning on...\n";

                new Instantiator($box."1.".$this->config->config->app_id, $repo_url, $this->config->config);

            }else{

                $total = 0;

                foreach($bs as $b){
                    $total += $b['percent'];
                }

                echo "Total: ".($total / count($bs)) . "\n";

                if(($total / count($bs)) > self::MAX_CPU_USAGE){

                    echo "Turning on ".$box.(count($bs) + 1).".".$this->config->config->app_id."...\n";
                    new Instantiator($box.(count($bs) + 1).".".$this->config->config->app_id, $repo_url, $this->config->config);

                }

            }

        }

    }

} 