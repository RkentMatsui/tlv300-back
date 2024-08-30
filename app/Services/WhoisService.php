<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class WhoisService
{
    protected $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.whois.api_key');
    }

    public function lookupDomain($domain)
    {
        $url = "https://www.whoisxmlapi.com/whoisserver/WhoisService";

        $response = Http::get($url, [
            'apiKey' => $this->apiKey,
            'domainName' => $domain,
            'outputFormat' => 'json',
        ]);

        if ($response->successful()) {
            return $response->json();
        }

        return null;
    }
}
