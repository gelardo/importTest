<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ImportProducts extends Command
{
    protected $signature = 'import:products {file} {--test}';
    protected $description = 'Import products from a CSV file';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $file = $this->argument('file');
        $testMode = $this->option('test');
        
        if (!file_exists($file)) {
            $this->error('File not found.');
            return;
        }
        // Read the CSV file
        $csvData = array_map('str_getcsv', file($file));
        $header = array_shift($csvData);

        $processed = $successful = $skipped = $duplicate = 0;

        DB::beginTransaction();

        foreach ($csvData as $row) {
            $processed++;

            // Check if the number of columns matches the header
            if (count($header) != count($row)) {
                $skipped++;
                $this->error('Skipping row due to column mismatch: ' . json_encode($row));
                continue;
            }

            $data = array_combine($header, $row);

            if (!$this->isValidProduct($data)) {
                $skipped++;
                $this->info('Skipped: ' . json_encode($data));
                continue;
            }
            $productCode = $data['Product Code'];
            // Check for duplicate product codes in the CSV file
            $productCodes = array_column($csvData, 0);
            $productCodeCount = array_count_values($productCodes);
            if ($productCodeCount[$productCode] > 1) {
                $duplicate++;
                $this->info("Duplicate product found in CSV file: " . json_encode($data));
                continue;
            }
            // Check if product already exists
            $existingProduct = Product::where('strProductCode', $productCode)->first();            
            if ($existingProduct) {
                $duplicate++;
                $this->info("Duplicate product found: ' . json_encode($data)");
                continue;
            }
            $productData = [
                'strProductName' => $this->sanitizeValue($data['Product Name']),
                'strProductDesc' => $this->sanitizeValue($data['Product Description']),
                'strProductCode' => $data['Product Code'],
                'stock_level' => $data['Stock'] ? $data['Stock'] : 0,
                'price' => $this->removeCurrencySymbol($data['Cost in GBP']),
                'dtmAdded' => now(),
                'dtmDiscontinued' => $data['Discontinued'] === 'yes' ? now() : null,
            ];

            try {
                if (!$testMode) {
                    Product::create($productData);
                }
                $successful++;
            } catch (\Exception $e) {
                
                     dd($e);
                $skipped++;
                $this->error("Failed to insert product: " . $data['Product Name']);
            }
        }

        if ($testMode) {
            DB::rollBack();
        } else {
            DB::commit();
        }

        $this->info("Processed: $processed, Successful: $successful, Skipped: $skipped, Duplicate: $duplicate");
    }
    private function sanitizeValue($value)
    {
        // Replace double quotes with single quotes
        $value = str_replace('"', "'", $value);
        $value = preg_replace('/[^A-Za-z0-9\-\_\.\' ]/', '', $value);
    
        return $value;
    }
    private function removeCurrencySymbol($price) {
        $currencySymbols = ['$', '£', '€', '¥']; // Add more symbols as needed
        $pattern = '/^[' . implode('', $currencySymbols) . ']|[' . implode('', $currencySymbols) . ']$/';
        $cleanPrice = preg_replace($pattern, '', $price);
        return $cleanPrice;
    }
    private function isValidProduct($data)
    {
        $cost = (float)$data['Cost in GBP'];
        $stock = (int)$data['Stock'];

        if ($cost < 5 && $stock < 10) {
            return false;
        }

        if ($cost > 1000) {
            return false;
        }

        return true;
    }
}
