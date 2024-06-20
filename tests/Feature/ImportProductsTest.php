<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;

class ImportProductsTest extends TestCase
{
    use RefreshDatabase;

    public function test_import_products()
    {
        Storage::fake('local');
        Storage::put('stock.csv', "Product Code,Product Name,Product Description,Stock,Cost in GBP,Discontinued\nP0001,TV,32” Tv,10,399.99,\nP0002,Cd Player,Nice CD player,11,50.12,yes");
        Artisan::call('import:products', ['file' => storage_path('framework/testing/disks/local/stock.csv')]);
        $this->assertEquals(2, Product::count());
    }

    public function test_import_products_in_test_mode()
    {
        Storage::fake('local');
        Storage::put('stock.csv', "Product Code,Product Name,Product Description,Stock,Cost in GBP,Discontinued\nP0001,TV,32” Tv,10,399.99,\nP0002,Cd Player,Nice CD player,11,50.12,yes");

        Artisan::call('import:products', ['file' => storage_path('framework/testing/disks/local/stock.csv'), '--test' => true]);

        $this->assertEquals(0, Product::count());
    }
}
