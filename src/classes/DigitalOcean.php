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

    public function makeRequest($url){

        $ch = curl_init(self::API_URL.$url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: Bearer '.$this->token
        ));

        $resultado = curl_exec($ch);
        curl_close($ch);

        return json_decode($resultado);

    }

} 