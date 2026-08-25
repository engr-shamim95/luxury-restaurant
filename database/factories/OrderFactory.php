<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 15, 100);
        $tax = round($subtotal * 0.10, 2);
        $total = $subtotal + $tax;

        return [
            'user_id' => null,
            'customer_name' => fake()->name(),
            'customer_email' => fake()->safeEmail(),
            'customer_phone' => fake()->phoneNumber(),
            'order_type' => fake()->randomElement(['pickup', 'delivery']),
            'delivery_address' => fake()->address(),
            'order_notes' => fake()->optional()->sentence(),
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $total,
            'payment_method' => fake()->randomElement(['cash', 'stripe', 'cod']),
            'payment_status' => 'pending',
            'order_status' => 'new',
            'transaction_id' => fake()->optional()->uuid(),
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'order_status' => 'completed',
            'payment_status' => 'paid',
        ]);
    }

    public function preparing(): static
    {
        return $this->state(fn (array $attributes) => [
            'order_status' => 'preparing',
        ]);
    }

    public function ready(): static
    {
        return $this->state(fn (array $attributes) => [
            'order_status' => 'ready',
        ]);
    }

    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
            'customer_name' => $user->name,
            'customer_email' => $user->email,
        ]);
    }
}
