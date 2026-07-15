<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FilterAwareExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_export_includes_category_column(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $category = Category::create([
            'name' => 'Plastik',
            'slug' => 'plastik',
            'is_active' => true,
        ]);

        Product::create([
            'category_id' => $category->id,
            'product_code' => 'P-001',
            'name' => 'Botol 500ml',
            'slug' => 'botol-500ml',
            'unit' => 'pcs',
            'price' => 12000,
            'discount_price' => null,
            'weight' => 0.5,
            'stock' => 10,
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.products.exportPriceTemplate'));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        // Check that category columns are present
        $response->assertSee('category_id');
        $response->assertSee('category_name');
    }

    public function test_export_respects_search_filter(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $category = Category::create([
            'name' => 'Plastik',
            'slug' => 'plastik',
            'is_active' => true,
        ]);

        $matchingProduct = Product::create([
            'category_id' => $category->id,
            'product_code' => 'P-GELAS-001',
            'name' => 'Gelas Plastik 200ml',
            'slug' => 'gelas-plastik',
            'unit' => 'pcs',
            'price' => 5000,
            'discount_price' => null,
            'weight' => 0.2,
            'stock' => 100,
            'is_active' => true,
        ]);

        Product::create([
            'category_id' => $category->id,
            'product_code' => 'P-BOTOL-001',
            'name' => 'Botol Air 1L',
            'slug' => 'botol-air',
            'unit' => 'pcs',
            'price' => 15000,
            'discount_price' => null,
            'weight' => 0.8,
            'stock' => 50,
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get(
            route('admin.products.exportPriceTemplate', [
                'search' => 'Gelas',
            ])
        );

        $response->assertOk();
        $response->assertSee('Gelas Plastik 200ml');
        $response->assertDontSee('Botol Air 1L');
    }

    public function test_export_respects_category_filter(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $plastikCategory = Category::create([
            'name' => 'Plastik',
            'slug' => 'plastik',
            'is_active' => true,
        ]);

        $kertasCategory = Category::create([
            'name' => 'Kertas',
            'slug' => 'kertas',
            'is_active' => true,
        ]);

        $plastikProduct = Product::create([
            'category_id' => $plastikCategory->id,
            'product_code' => 'P-001',
            'name' => 'Gelas Plastik',
            'slug' => 'gelas-plastik',
            'unit' => 'pcs',
            'price' => 5000,
            'discount_price' => null,
            'weight' => 0.2,
            'stock' => 100,
            'is_active' => true,
        ]);

        Product::create([
            'category_id' => $kertasCategory->id,
            'product_code' => 'K-001',
            'name' => 'Tas Kertas',
            'slug' => 'tas-kertas',
            'unit' => 'pcs',
            'price' => 8000,
            'discount_price' => null,
            'weight' => 0.3,
            'stock' => 50,
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get(
            route('admin.products.exportPriceTemplate', [
                'category' => $plastikCategory->id,
            ])
        );

        $response->assertOk();
        $response->assertSee('Gelas Plastik');
        $response->assertDontSee('Tas Kertas');
    }

    public function test_selected_export_includes_category_column(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $category = Category::create([
            'name' => 'Plastik',
            'slug' => 'plastik',
            'is_active' => true,
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'product_code' => 'P-001',
            'name' => 'Gelas Plastik',
            'slug' => 'gelas-plastik',
            'unit' => 'pcs',
            'price' => 5000,
            'discount_price' => null,
            'weight' => 0.2,
            'stock' => 100,
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->post(
            route('admin.products.exportSelected'),
            ['ids' => [$product->id]]
        );

        $response->assertOk();
        $response->assertSee('category_id');
        $response->assertSee('category_name');
        $response->assertSee('Plastik');
    }
}
