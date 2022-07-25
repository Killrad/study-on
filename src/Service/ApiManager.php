<?php

namespace App\Service;

use App\Exception\BillingUnavailableException;

class ApiManager
{
    private $options;

    public function __construct(
        string $url,
        string $method,
        ?array $header,
               $parameters = null
    ) {
        $this->options = [CURLOPT_RETURNTRANSFER => true];
        $this->options[CURLOPT_URL] = $_ENV['BILLING_URL'] . $url;
        if ($header !== null) {
            $this->options[CURLOPT_HTTPHEADER] = [];
            array_push($this->options[CURLOPT_HTTPHEADER], ...$header);
        }

        if ($method === 'POST') {
            $this->options[CURLOPT_POST] = true;
            if ($parameters !== null) {
                $this->options[CURLOPT_POSTFIELDS] = $parameters;
            }
        }
        else{
            if ($parameters !== null){
                $this->options[CURLOPT_URL] .= '?' . http_build_query($parameters);
            }
        }

    }

    public function exec()
    {

        $ch = curl_init();
        curl_setopt_array($ch, $this->options);
        $jsonResponse = curl_exec($ch);
        if ($jsonResponse === false) {
            throw new BillingUnavailableException('Сервис временно недоступен!');
        }
        curl_close($ch);
        return $jsonResponse;
    }
}