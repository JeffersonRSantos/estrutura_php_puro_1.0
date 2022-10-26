<?php

namespace Core\Helpers;

use Exception;

date_default_timezone_set('America/Sao_Paulo');

Class CurlImplements {

    public $url;
    public $method;
    public $header = [];
    public $data = [];
    private $output;
    private $httpcode;
    private $json_data;

    public function __construct($url = null, $method = 'GET', $data = null, $header = [], $json_data = true)
    {
        $this->url = $url;
        $this->method = $method;
        $this->data = $data;
        $this->header = $header;
        $this->json_data = $json_data;
    }

    public function getHeader()
    {
        return $this->header;
    }

    public function setHeader($header)
    {
        $this->header = $header;
        return $this;
    }
    

    public function getMethod()
    {
        return $this->method;
    }

    public function setMethod($method)
    {
        $this->method = $method;
        return $this;
    }

    public function getData()
    {
        return $this->data;
    }

    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    public function getUrl()
    {
        return $this->url;
    }
    
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    public function execute()
    {

        try {
            if($this->json_data === true && $this->data !== null) {
                $this->data = json_encode($this->data, JSON_UNESCAPED_UNICODE);
                $this->header[] = "Content-Type: application/json";
            }
            
            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => $this->getUrl(),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => strtoupper($this->getMethod()),
                CURLOPT_POSTFIELDS => $this->getData(),
                CURLOPT_HTTPHEADER => $this->getHeader(),
            ));

            $response = curl_exec($curl);
            $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

            curl_close($curl);

            $this->output = $response;
            $this->httpcode = $httpcode;
            return $this;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public function body() {
        return $this->output;
    }

    public function json() {
        return json_decode($this->output,true);
    }

    public function object() {
        return json_decode($this->output);
    }

    public function status() {
        return $this->httpcode;
    }
}
