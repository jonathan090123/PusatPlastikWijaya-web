<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class AdminProductPriceBulkUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_exports_a_csv_template_with_current_product_prices(): void
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
            'stock' => 10,
            'stock_alert' => 3,
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.products.exportPriceTemplate'));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $response->assertSee('product_id');
        $response->assertSee('category_name');
        $response->assertSee('product_code');
        $response->assertSee('name');
        $response->assertSee('unit');
        $response->assertSee('price');
        $response->assertSee('discount_price');
        $response->assertSee('weight');
        $response->assertSee('stock');
    }

    public function test_exports_product_unit_columns_in_csv(): void
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
            'product_code' => 'P-003',
            'name' => 'Botol 1L',
            'slug' => 'botol-1l',
            'unit' => 'KG',
            'price' => 18000,
            'discount_price' => null,
            'weight' => 1.5,
            'stock' => 15,
            'stock_alert' => 5,
            'is_active' => true,
        ]);

        $product->productUnits()->create([
            'unit' => 'BAL',
            'conversion_value' => 25,
            'price' => 400000,
        ]);

        $product->productUnits()->create([
            'unit' => 'PAK',
            'conversion_value' => 5,
            'price' => 85000,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.products.exportPriceTemplate'));

        $response->assertOk();
        $response->assertSee('product_unit_1_unit');
        $response->assertSee('product_unit_1_conversion');
        $response->assertSee('product_unit_1_price');
        $response->assertSee('product_unit_2_unit');
        $response->assertSee('product_unit_2_conversion');
        $response->assertSee('product_unit_2_price');
        $response->assertSee('BAL');
        $response->assertSee('400000');
        $response->assertSee('PAK');
        $response->assertSee('85000');
    }

    public function test_exports_a_full_product_template_with_product_database_columns(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $category = Category::create([
            'name' => 'Plastik',
            'slug' => 'plastik-export',
            'is_active' => true,
        ]);

        Product::create([
            'category_id' => $category->id,
            'product_code' => 'P-003',
            'name' => 'Botol 1L',
            'slug' => 'botol-1l',
            'unit' => 'pcs',
            'description' => 'Botol sampel',
            'price' => 18000,
            'discount_price' => null,
            'weight' => 1.5,
            'stock' => 15,
            'stock_alert' => 5,
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.products.exportPriceTemplate'));

        $response->assertOk();
        $response->assertSee('product_id');
        $response->assertSee('category_name');
        $response->assertSee('product_code');
        $response->assertSee('stock');
    }

    public function test_exports_only_selected_products_when_requested(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $category = Category::create([
            'name' => 'Plastik',
            'slug' => 'plastik-selected',
            'is_active' => true,
        ]);

        $selectedProduct = Product::create([
            'category_id' => $category->id,
            'product_code' => 'P-004',
            'name' => 'Botol 2L',
            'slug' => 'botol-2l',
            'unit' => 'pcs',
            'price' => 25000,
            'discount_price' => null,
            'stock' => 12,
            'stock_alert' => 4,
            'is_active' => true,
        ]);

        Product::create([
            'category_id' => $category->id,
            'product_code' => 'P-005',
            'name' => 'Wadah 5L',
            'slug' => 'wadah-5l',
            'unit' => 'pcs',
            'price' => 30000,
            'discount_price' => null,
            'stock' => 8,
            'stock_alert' => 2,
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.products.exportSelected'), [
            'ids' => [$selectedProduct->id],
        ]);

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $response->assertSee($selectedProduct->id);
        $response->assertDontSee('Wadah 5L');
    }

    public function test_imports_updated_prices_from_csv_and_updates_products(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $category = Category::create([
            'name' => 'Plastik',
            'slug' => 'plastik-2',
            'is_active' => true,
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'product_code' => 'P-002',
            'name' => 'Tabung 1L',
            'slug' => 'tabung-1l',
            'unit' => 'pcs',
            'price' => 15000,
            'discount_price' => null,
            'stock' => 20,
            'stock_alert' => 5,
            'is_active' => true,
        ]);

        $csv = "product_id,category_name,product_code,name,unit,price,discount_price,weight,stock\n{$product->id},Plastik,{$product->product_code},{$product->name},{$product->unit},18000,,,20\n";
        $file = UploadedFile::fake()->createWithContent('prices.csv', $csv);

        $response = $this->actingAs($admin)->post(route('admin.products.importPriceUpdates'), [
            'file' => $file,
        ]);

        $response->assertRedirect(route('admin.products.index'));
        $response->assertSessionHas('success');

        $product->refresh();
        $this->assertSame(18000.0, (float) $product->price);
    }

    public function test_imports_product_units_from_csv(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $category = Category::create([
            'name' => 'Plastik',
            'slug' => 'plastik-3',
            'is_active' => true,
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'product_code' => 'P-010',
            'name' => 'Kantong PE 50x75',
            'slug' => 'kantong-pe',
            'unit' => 'KG',
            'price' => 15000,
            'discount_price' => null,
            'stock' => 100,
            'stock_alert' => 10,
            'is_active' => true,
        ]);

        $csv = "product_id,category_name,product_code,name,unit,price,discount_price,weight,stock,product_unit_1_unit,product_unit_1_conversion,product_unit_1_price,product_unit_2_unit,product_unit_2_conversion,product_unit_2_price\n" .
               "{$product->id},Plastik,{$product->product_code},{$product->name},{$product->unit},15000,,,100,BAL,25,350000,PAK,5,72000\n";

        $file = UploadedFile::fake()->createWithContent('prices.csv', $csv);

        $response = $this->actingAs($admin)->post(route('admin.products.importPriceUpdates'), [
            'file' => $file,
        ]);

        $response->assertRedirect(route('admin.products.index'));
        $response->assertSessionHas('success');

        $product->refresh();
        $product->load('productUnits');

        $this->assertCount(2, $product->productUnits);

        $balUnit = $product->productUnits->firstWhere('unit', 'BAL');
        $this->assertNotNull($balUnit);
        $this->assertSame(25, $balUnit->conversion_value);
        $this->assertSame(350000.0, (float) $balUnit->price);

        $pakUnit = $product->productUnits->firstWhere('unit', 'PAK');
        $this->assertNotNull($pakUnit);
        $this->assertSame(5, $pakUnit->conversion_value);
        $this->assertSame(72000.0, (float) $pakUnit->price);
    }
}
