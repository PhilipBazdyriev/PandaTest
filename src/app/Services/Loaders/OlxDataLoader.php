<?php

namespace App\Services\Loaders;

use GuzzleHttp\Client;

class OlxDataLoader
{

    public function fetchProductData($productUrl)
    {
        $normalizedProductUrl = $this->normalizeProduct($productUrl);
        $productData = $this->retrieveProduct($normalizedProductUrl);
        return $productData;
    }

    public function normalizeProduct($productUrl)
    {
        $urlComponents = parse_url($productUrl);

        if (!isset($urlComponents['host'])) {
            return null;
        }

        if ($urlComponents['host'] === 'www.olx.ua') {
            return str_replace('www.olx.ua', 'm.olx.ua', $productUrl);
        }

        return $urlComponents['host'] === 'm.olx.ua' ? $productUrl : null;
    }

    public function retrieveProduct($productUrl)
    {

        $client = new Client();

        $response = $client->get($productUrl);
        $html = $response->getBody()->getContents();

        $dom = new \DOMDocument();
        @$dom->loadHTML($html);

        $scripts = $dom->getElementsByTagName('script');

        foreach ($scripts as $script) {
            if ($script->hasAttribute('data-rh') && $script->getAttribute('type') === 'application/ld+json') {
                $json = $script->nodeValue;
                $data = json_decode($json, true);

                $sku = (int)$data['sku'];
                if (!$sku) {
                    throw new \Exception('Undefined SKU');
                }

                $image = '';
                if (is_array($data['image']) && !empty($data['image'])) {
                    $image = $data['image'][0];
                }

                if (!isset($data['offers']) || !isset($data['offers']['priceCurrency']) || !isset($data['offers']['price'])) {
                    throw new \Exception('Undefined price');
                }

                $priceCurrency = $data['offers']['priceCurrency'];
                $price = $data['offers']['price'];

                return [
                    'sku'           => $sku,
                    'url'           => $data['url'],
                    'name'          => $data['name'],
                    'image'         => $image,
                    'description'   => $data['description'],
                    'price_currency' => $priceCurrency,
                    'price'         => $price,
                ];
            }
        }
    }

}
