<?php

namespace App\Services;

use App\Models\OlxProduct;
use GuzzleHttp\Client;

class OlxTrackingService
{

    public function subscribeUserToTracking($productUrl, $userEmail)
    {

        // validate input data
        $this->validateProductUrl($productUrl);
        $this->validateUserEmail($userEmail);

        try {

            $normalizedProductUrl = $this->normalizeProduct($productUrl);
            $productData = $this->retrieveProduct($normalizedProductUrl);

            if (!$productData || !is_array($productData) || empty($productData)) {
                throw new \Exception('Failed to load product data');
            }

            $this->saveProduct($productData);

            // subscribe user to SKU tracking
            // $this->startUserTracking($userEmail, $productData['sku']); TODO implement

        } catch (\Exception $e) {
            throw $e;
            throw new \Exception('Something went wrong');
        }

    }

    public function validateUserEmail($email)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \Exception('Incorrect email format');
        }
    }

    public function validateProductUrl($productUrl)
    {
        if (!filter_var($productUrl, FILTER_VALIDATE_URL)) {
            throw new \Exception('Incorrect product URL format');
        }

        $parsedUrl = parse_url($productUrl);
        if (!isset($parsedUrl['host']) || strpos($parsedUrl['host'], 'olx.ua') === false) {
            throw new \Exception('URL does not belong to olx.ua');
        }
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

                /*
                array:9 [ // app/Services/OlxTrackingService.php:57
                  "@context" => "https://schema.org"
                  "@type" => "Product"
                  "name" => "Здам 1 кімн кв Новояворівськ"
                  "image" => array:5 [
                    0 => "https://ireland.apollo.olxcdn.com:443/v1/files/vpvmlzu302at3-UA/image"
                    1 => "https://ireland.apollo.olxcdn.com:443/v1/files/bf0ehx4ubdfr-UA/image"
                    2 => "https://ireland.apollo.olxcdn.com:443/v1/files/d3weg004eqwc1-UA/image"
                    3 => "https://ireland.apollo.olxcdn.com:443/v1/files/cxxt078bfp4p1-UA/image"
                    4 => "https://ireland.apollo.olxcdn.com:443/v1/files/p7d1htnw56nr2-UA/image"
                  ]
                  "url" => "https://www.olx.ua/d/uk/obyavlenie/zdam-1-kmn-kv-novoyavorvsk-IDVZYXO.html"
                  "description" => "Ремонт, меблі, техніка, пральня машинка є, холодильник. 6000 грн + кп"
                  "category" => "https://www.olx.ua/nedvizhimost/kvartiry/dolgosrochnaya-arenda-kvartir/"
                  "sku" => "871799844"
                  "offers" => array:5 [
                    "@type" => "Offer"
                    "availability" => "https://schema.org/InStock"
                    "areaServed" => array:2 [
                      "@type" => "City"
                      "name" => "Новояворівськ"
                    ]
                    "priceCurrency" => "UAH"
                    "price" => 6000
                  ]
                ]
                */

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
                    'priceCurrency' => $priceCurrency,
                    'price'         => $price,
                ];

            }
        }
    }

    public function saveProduct(array $productData)
    {
        $productData['lastRefreshTime'] = now();
        OlxProduct::updateOrCreate(
            ['sku' => $productData['sku']],
            $productData
        );
    }

    public function startUserTracking($userEmail, $productSku)
    {
        throw new \Exception('Not implemented');
    }

}
