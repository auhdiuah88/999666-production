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

    public function postFormData($url, $params, $headers = [])
    {
        $params = $this->paramsFilter($params);
        $response = Http::withHeaders($headers)->asForm()->post($url, $params);
        return $response->json();
    }

    public function postJsonData($url, $params, $headers = [],$respType = 'json')
    {
        $params = $this->paramsFilter($params);
        $response = Http::withHeaders($headers)->post($url, $params);
        if ($respType == 'json'){
            return $response->json();
        }
        return $response->body();
    }

    public function get($url, $params = [], $headers = [])
    {
        $params = $this->paramsFilter($params);
        $params = http_build_query($params);
        $url = $params? $url.'?'.$params: $url;
        $response = Http::withHeaders($headers)->get($url);
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

