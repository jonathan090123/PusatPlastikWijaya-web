<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class RajaOngkirService
{
    protected string $apiKey;
    protected string $baseUrl = 'https://rajaongkir.komerce.id/api/v1';
    protected string $originId;

    public function __construct()
    {
        $this->apiKey   = config('services.rajaongkir.api_key', '');
        $this->originId = (string) config('services.rajaongkir.origin_city_id', '47040');
    }

    public function isConfigured(): bool
    {
        return !empty($this->apiKey)
            && $this->apiKey !== 'your-api-key-here'
            && strlen($this->apiKey) > 10;
    }

    /**
     * Search domestic destinations by query string.
     * Returns subdistrict-level results with their IDs,
     * suitable for an autocomplete / search input.
     *
     * @return array<int, array{id: int, label: string, province_name: string, city_name: string, district_name: string, subdistrict_name: string, zip_code: string}>
     */
    public function searchDestination(string $query, int $limit = 10): array
    {
        $response = Http::withHeaders(['key' => $this->apiKey])
            ->get("{$this->baseUrl}/destination/domestic-destination", [
                'search' => $query,
                'limit'  => $limit,
                'offset' => 0,
            ]);

        if ($response->successful()) {
            return $response->json('data') ?? [];
        }

        return [];
    }

    /**
     * Calculate shipping costs from the store origin to a destination subdistrict.
     *
     * @param  int|string $destinationId  Subdistrict ID from searchDestination()
     * @param  int        $weight         Total weight in grams (min 100)
     * @param  array      $couriers       Courier codes, e.g. ['jne','tiki','pos','sicepat','jnt']
     * @return array  Flat list sorted by cost: [{name, code, service, description, cost, etd}, ...]
     */
    public function getShippingOptions(string|int $destinationId, int $weight, array $couriers = ['jne', 'sicepat', 'jnt', 'tiki', 'pos']): array
    {
        if ($weight < 100) {
            $weight = 500;
        }

        $response = Http::withHeaders([
                'key'          => $this->apiKey,
                'Content-Type' => 'application/x-www-form-urlencoded',
            ])
            ->asForm()
            ->post("{$this->baseUrl}/calculate/domestic-cost", [
                'origin'      => $this->originId,
                'destination' => (string) $destinationId,
                'weight'      => $weight,
                'courier'     => implode(':', $couriers),
                'price'       => 'lowest',
            ]);

        if (!$response->successful()) {
            return [];
        }

        $results = $response->json('data') ?? [];

        usort($results, fn($a, $b) => $a['cost'] <=> $b['cost']);

        return $results;
    }
}
