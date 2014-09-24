<?php
/**
 * Created by PhpStorm.
 * User: sergiovilar
 * Date: 23/09/14
 * Time: 4:25 PM
 */

class DigitalOcean {

    private $token;
    const API_URL = "https://api.digitalocean.com/v2";

    public function __construct($token){
        $this->token = $token;
    }

    public function get($url){
        return $this->makeRequest($url, true);
    }

    public function post($url){
        return $this->makeRequest($url, false);
    }

    private function makeRequest($url, $get = false){

        $ch = curl_init(self::API_URL.$url);
        $headers = array('Authorization: Bearer '.$this->token);

        if(!$get){

            $headers[] = 'Content-Type: application/x-www-form-urlencoded';
            curl_setopt($ch,CURLOPT_POST, 0);

        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $resultado = curl_exec($ch);
        curl_close($ch);

        return json_decode($resultado);

    }

} 