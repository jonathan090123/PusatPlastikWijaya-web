<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\RajaOngkirService;
use Illuminate\Http\Request;

class ShippingController extends Controller
{
    public function __construct(protected RajaOngkirService $rajaOngkir) {}

    /**
     * Search domestic destinations (subdistrict-level autocomplete).
     * GET /api/shipping/search-destinations?query=blitar
     */
    public function searchDestinations(Request $request)
    {
        $request->validate(['query' => 'required|string|min:2|max:100']);

        $results = $this->rajaOngkir->searchDestination($request->query('query'), 10);

        return response()->json(['success' => true, 'data' => $results]);
    }

    /**
     * Calculate shipping cost from store origin to given subdistrict ID.
     * POST /api/shipping/cost  { destination_id, weight }
     */
    public function cost(Request $request)
    {
        $request->validate([
            'destination_id' => 'required|integer|min:1',
            'weight'         => 'required|integer|min:1',
        ]);

        $options = $this->rajaOngkir->getShippingOptions(
            $request->destination_id,
            $request->weight
        );

        return response()->json(['success' => true, 'data' => $options]);
    }
}