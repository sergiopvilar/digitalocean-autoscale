<?php
/**
 * Created by PhpStorm.
 * User: sergiovilar
 * Date: 23/09/14
 * Time: 3:51 PM
 */

class AppConfig {

    public static $instance;
    private $config;

    private function __construct(){

        $this->config = json_decode(file_get_contents("boxes.json"));

    }

    public function getInstance(){

        if (!isset(self::$instance)) {
            $c = __CLASS__;
            self::$instance = new $c;
        }

        return self::$instance;

    }

    public function get(){
        return $this->config;
    }

} 