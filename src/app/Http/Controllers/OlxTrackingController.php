<?php

namespace App\Http\Controllers;

use App\Services\OlxTrackingService;
use Illuminate\Http\Request;

class OlxTrackingController extends Controller
{

    protected $olxService;

    public function __construct(OlxTrackingService $olxService)
    {
        $this->olxService = $olxService;
    }

    public function subscribeProduct(Request $request)
    {

        $productUrl = $request->post('productUrl');
        $userEmail = $request->post('userEmail');

        try {
            $subscriptionId = $this->olxService->subscribeUserToTracking($productUrl, $userEmail);
            return response()->json([
                'status' => 'ok',
                'id' => $subscriptionId,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'bad',
                'result' => $e->getMessage(),
            ]);
        }
    }

}
