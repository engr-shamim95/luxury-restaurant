<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderItem>
 */
class OrderItemFactory extends Factory
{
    protected $model = OrderItem::class;

    public function definition(): array
    {
        $quantity = fake()->numberBetween(1, 4);
        $unitPrice = fake()->randomFloat(2, 5, 25);
        $totalPrice = round($quantity * $unitPrice, 2);

        return [
            'order_id' => Order::factory(),
            'product_id' => Product::factory(),
            'product_name' => fake()->words(2, true),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'variants_selected' => null,
            'total_price' => $totalPrice,
        ];
    }
}
