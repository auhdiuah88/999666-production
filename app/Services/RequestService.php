<?php


namespace App\Services;

use Illuminate\Support\Facades\Http;

class RequestService
{
    private $header = [];

    /**
     * @return array
     */
    public function getHeader()
    {
        return $this->header;
    }

    /**
     * @param array $header
     */
    public function setHeader(array $header)
    {
        $this->header = $header;
    }

    public function postFormData($url, $params, $headers = [], $respType = 'json')
    {
        $params = $this->paramsFilter($params);
        $response = Http::withHeaders($headers)->asForm()->post($url, $params);
        if ($respType == 'json') {
            return $response->json();
        }
        return $response->body();
    }

    public function postJsonData($url, $params, $headers = [], $respType = 'json')
    {
        $params = $this->paramsFilter($params);
        $response = Http::withHeaders($headers)->post($url, $params);
        if ($respType == 'json') {
            return $response->json();
        }
        return $response->body();
    }

    public function postHttpBuildQuery($url, $params, $header=[])
    {
        $ch = curl_init();
        if($header){
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }
//        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $res = curl_exec($ch);
        curl_close($ch);
        return $res;
    }

    public function get($url, $params = [], $headers = [], $respType = 'json')
    {
        $params = $this->paramsFilter($params);
        $params = http_build_query($params);
        $url = $params ? $url . '?' . $params : $url;
        $response = Http::withHeaders($headers)->get($url);
        if ($respType == 'json') {
            return $response->json();
        }
        return $response->body();
    }

    /**
     * 过滤参数
     */
    private function paramsFilter($params)
    {
        $params = array_filter($params, function ($value) {
            return $value !== '';
        });
        return $params;
    }

}

