<?php

namespace App\Services;

use App\Models\OlxProduct;
use App\Models\User;
use App\Models\UserOlxProductSubscription;
use App\Services\Loaders\OlxDataLoader;
use GuzzleHttp\Client;

class OlxTrackingService
{

    private OlxDataLoader $olxDataLoader;

    public function __construct(OlxDataLoader $olxDataLoader)
    {
        $this->olxDataLoader = $olxDataLoader;
    }

    public function subscribeUserToTracking($productUrl, $userEmail)
    {

        $this->validateProductUrl($productUrl);
        $this->validateUserEmail($userEmail);

        try {

            $productData = $this->olxDataLoader->fetchProductData($productUrl);
            if (!$productData || !is_array($productData) || empty($productData)) {
                throw new \Exception('Failed to load product data');
            }

            $this->saveProduct($productData);

            $subscription = $this->startUserTracking($userEmail, $productData['sku']);
            if (!$subscription) {
                throw new \Exception('Failed to subscribe user to tracking product');
            }

            return $subscription->id;

        } catch (\Exception $e) {
            throw new \Exception('Something went wrong');
        }

    }

    public function validateUserEmail($email)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Incorrect email format');
        }
    }

    public function validateProductUrl($productUrl)
    {
        if (!filter_var($productUrl, FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException('Incorrect product URL format');
        }

        $parsedUrl = parse_url($productUrl);
        if (!isset($parsedUrl['host']) || strpos($parsedUrl['host'], 'olx.ua') === false) {
            throw new \Exception('URL does not belong to olx.ua');
        }
    }

    public function saveProduct(array $productData)
    {
        OlxProduct::updateOrCreate(
            ['sku' => $productData['sku']],
            $productData
        );
    }

    public function startUserTracking($userEmail, $productSku)
    {
        $user = User::where('email', $userEmail)->first();
        if (!$user) {
            $user = User::create(['email' => $userEmail]);
        }
        if (!$user) {
            throw new \Exception("Unable to retrieve user with email: {$userEmail}");
        }

        $product = OlxProduct::where('sku', $productSku)->first();
        if (!$product) {
            throw new \Exception("Unable to retrieve product with SKU: {$productSku}");
        }

        $subscription = UserOlxProductSubscription::where('user_id', $user->id)->where('olx_product_id', $product->id)->first();
        if (!$subscription) {
            $subscription = UserOlxProductSubscription::create([
                'user_id' => $user->id,
                'olx_product_id' => $product->id,
            ]);
        }

        return $subscription;
    }

}
