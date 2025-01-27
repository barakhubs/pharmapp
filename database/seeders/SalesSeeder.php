<?php

namespace Database\Seeders;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Medicine;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SalesSeeder extends Seeder
{
    public function run()
    {
        $faker = \Faker\Factory::create();
        $medicines = Medicine::all(); // Ensure you have medicines in the database

        if ($medicines->isEmpty()) {
            $this->command->error('No medicines found! Please seed medicines before running this seeder.');
            return;
        }

        DB::transaction(function () use ($faker, $medicines) {
            foreach (range(1, 200) as $i) {
                // Create Sale
                $sale = Sale::create([
                    'customer_id' => $faker->numberBetween(1, 2), // Assume 50 customers exist
                    'total_amount' => 0,
                    'branch_id' => $faker->numberBetween(1, 3), // Assume 2 branches exist
                    'created_at' => $faker->dateTimeBetween('2024-01-01', '2025-12-31'),
                    'updated_at' => now(),
                ]);

                $totalAmount = 0;

                // Generate Sale Items
                foreach (range(1, $faker->numberBetween(1, 2)) as $j) { // Each sale has 1-5 items
                    $medicine = $medicines->random();
                    $quantity = $faker->numberBetween(1, 10);
                    $price = $faker->numberBetween(100, 1500); // Assume price is a property in the Medicine model
                    $total = $quantity * $price;

                    SaleItem::create([
                        'sale_id' => $sale->id,
                        'medicine_id' => $medicine->id,
                        'quantity' => $quantity,
                        'price' => $price,
                        'total' => $total,
                        'branch_id' => $faker->numberBetween(1, 3),
                        'created_at' => $sale->created_at,
                        'updated_at' => now(),
                    ]);

                    // Reduce stock
                    $medicine->decrement('stock_quantity', $quantity);

                    $totalAmount += $total;
                }

                // Update total amount for the sale
                $sale->update([
                    'total_amount' => $totalAmount,
                ]);
            }
        });
    }
}
