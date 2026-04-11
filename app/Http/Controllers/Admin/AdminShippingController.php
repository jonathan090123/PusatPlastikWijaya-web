<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShippingCost;
use App\Services\RajaOngkirService;
use Illuminate\Http\Request;

class AdminShippingController extends Controller
{
    private function ensureAllMethods(): void
    {
        ShippingCost::firstOrCreate(
            ['type' => 'pickup'],
            ['name' => 'Pickup (Ambil di Toko)', 'description' => 'Customer datang langsung ke toko', 'cost' => 0, 'estimation' => null, 'is_active' => true]
        );
        ShippingCost::firstOrCreate(
            ['type' => 'local'],
            ['name' => 'Kurir Toko', 'description' => 'Pengiriman dalam kota Blitar menggunakan kurir toko', 'cost' => 10000, 'estimation' => '1-2 hari', 'is_active' => true]
        );
        ShippingCost::firstOrCreate(
            ['type' => 'outside'],
            ['name' => 'Pengiriman Luar Kota', 'description' => 'Pengiriman ke luar kota Blitar via ekspedisi (JNE, J&T, dll)', 'cost' => 0, 'estimation' => null, 'is_active' => false]
        );
    }

    public function index(RajaOngkirService $rajaOngkir)
    {
        $this->ensureAllMethods();

        $pickup  = ShippingCost::where('type', 'pickup')->first();
        $local   = ShippingCost::where('type', 'local')->first();
        $outside = ShippingCost::where('type', 'outside')->first();
        $rajaOngkirConfigured = $rajaOngkir->isConfigured();

        return view('admin.shipping.index', compact('pickup', 'local', 'outside', 'rajaOngkirConfigured'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'cost'        => 'required|numeric|min:0',
            'estimation'  => 'nullable|string|max:100',
            'description' => 'nullable|string|max:500',
        ]);

        ShippingCost::where('type', 'local')->update([
            'cost'        => $request->cost,
            'estimation'  => $request->estimation,
            'description' => $request->description,
        ]);

        return redirect()->route('admin.shipping.index')->with('success', 'Pengaturan ongkir kurir toko berhasil diperbarui!');
    }

    public function toggleActive(Request $request)
    {
        $request->validate(['type' => 'required|in:pickup,local,outside']);

        $method = ShippingCost::where('type', $request->type)->first();

        if (!$method) {
            return response()->json(['success' => false], 404);
        }

        // Luar kota hanya bisa diaktifkan jika RajaOngkir sudah dikonfigurasi
        if ($request->type === 'outside' && !$method->is_active) {
            $rajaOngkir = app(RajaOngkirService::class);
            if (!$rajaOngkir->isConfigured()) {
                return response()->json(['success' => false, 'message' => 'API Key RajaOngkir belum dikonfigurasi. Tambahkan RAJAONGKIR_API_KEY di file .env.'], 422);
            }
        }

        $method->update(['is_active' => !$method->is_active]);

        return response()->json(['success' => true, 'is_active' => $method->is_active]);
    }
}
