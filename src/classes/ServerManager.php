<?php
/**
 * Created by PhpStorm.
 * User: sergiovilar
 * Date: 23/09/14
 * Time: 4:40 PM
 */

class ServerManager {

    private $config;
    private $api;

    const MAX_CPU_USAGE = 90;
    const MIN_CPU_USAGE = 20;
    const UPDATE_TIME = 5;

    public function __construct(){

        $this->config = AppConfig::getInstance()->get();
        $this->api = new DigitalOcean($this->config->config->token);

        while(true){

            $this->checkDroplets();
            sleep(self::UPDATE_TIME);

        }

    }

    private function getDropletsByBox($box){

        $droplets = $this->api->get('/droplets');
        $a = [];

        foreach($droplets->droplets as $droplet){

            if(strpos($droplet->name, $box) > -1 && strpos($droplet->name, $this->config->config->app_id) > -1){

                $a[] = $droplet;

            }

        }

        return $a;

    }

    private function destroyDroplet($droplet){

        $this->api->post('/droplets/'.$droplet->id);

    }

    private function getRepo($box){

        foreach((array) $this->config->config->boxes as $repo){
            if($repo->name == $box)
                $repo_url = $repo->source;
        }

        return $repo_url;

    }

    private function updateLoadBalancer($box){

        $lb_location = "/etc/nginx/sites-available/default";
        $file = file_get_contents(getcwd()."/tpl/loadbalance.tpl");

        $droplets = $this->getDropletsByBox($box);
        $servers = "";

        foreach($droplets as $droplet){
            $servers .= $droplet->networks->v4[0]->ip_address. ";\n";
        }

        $str = str_replace('${servers}', $servers, $file);
        file_put_contents($lb_location, $str);

    }

    private function checkDroplets(){

        foreach((array) $this->config->boxes as $box => $type){

            $droplets = $this->getDropletsByBox($box);
            $repo_url = $this->getRepo($box);
            $bs = [];

            foreach($droplets as $droplet){

                    // Get Droplet CPU usage
                    $p = new ScaleManager($droplet->name, $this->config->config);
                    $bs[] = array('droplet' => $droplet, 'percent' => $p->getPercentage());

            }

            if(count($bs) == 0){

                echo "There is no server, turning on...\n";

                new Instantiator($box."1.".$this->config->config->app_id, $repo_url, $this->config->config);

            }else{

                $total = 0;

                foreach($bs as $b){
                    $total += $b['percent'];
                }

                $average = $total / count($bs);

                echo "Total: ".($total / count($bs)) . "\n";

                if($average > self::MAX_CPU_USAGE){

                    echo "Turning on ".$box.(count($bs) + 1).".".$this->config->config->app_id."...\n";
                    new Instantiator($box.(count($bs) + 1).".".$this->config->config->app_id, $repo_url, $this->config->config);

                    $this->updateLoadBalancer($box);

                }

                if($average < self::MIN_CPU_USAGE && count($bs) > 1){

                    echo "Turning off one server...\n";

                    $this->destroyDroplet($bs[count($bs) -1]['droplet']);
                    $this->updateLoadBalancer($box);

                }

            }

        }

    }

} 