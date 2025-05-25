<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Order::class;
    
    public function definition(): array
    {
        $subtotal = $this->faker->randomFloat(2, 15, 500); // Random subtotal between 15 and 500
        $shipping = $this->faker->randomFloat(2, 5, 50);
        $tax = $subtotal * 0.1; // Example 10% tax
        $total = $subtotal + $shipping + $tax;

        $statuses = Order::getStatuses();
        $paymentStatuses = Order::getPaymentStatuses();
        $status = $this->faker->randomElement($statuses);
        $paymentStatus = ($status === Order::STATUS_CANCELLED || $status === Order::STATUS_REFUNDED)
                         ? $this->faker->randomElement([Order::PAYMENT_REFUNDED, Order::PAYMENT_PAID]) // Refunded orders were likely paid first
                         : $this->faker->randomElement($paymentStatuses);

        return [
            // Link to an existing user or leave null for guest
            'user_id' => User::query()->inRandomOrder()->first()?->id ?? null, // Get random user ID or null
            'order_number' => 'ORD-' . strtoupper(Str::random(8)),
            'status' => $status,
            'payment_status' => $paymentStatus,
            'payment_method' => $this->faker->randomElement(['Credit Card', 'PayPal', 'Stripe']),
            'subtotal' => $subtotal,
            'shipping_cost' => $shipping,
            'tax_amount' => $tax,
            'total_amount' => $total,
            'shipping_address' => [ // Example JSON structure
                'name' => $this->faker->name(),
                'address1' => $this->faker->streetAddress(),
                'address2' => $this->faker->optional(0.2)->secondaryAddress(), // 20% chance of address2
                'city' => $this->faker->city(),
                'state' => $this->faker->stateAbbr(),
                'zip' => $this->faker->postcode(),
                'country' => 'US', // Or use $this->faker->countryCode()
                'phone' => $this->faker->optional()->phoneNumber(),
                'email' => $this->faker->safeEmail(),
            ],
            'billing_address' => function (array $attributes) { // Use shipping if not specified
                return $attributes['shipping_address'];
            },
            'shipping_method' => $this->faker->randomElement(['Standard Ground', 'Express Shipping']),
            'tracking_number' => ($status === Order::STATUS_SHIPPED || $status === Order::STATUS_DELIVERED) ? 'TRK' . $this->faker->randomNumber(8) : null,
            'notes' => $this->faker->optional(0.3)->sentence(), // 30% chance of having notes
            'paid_at' => ($paymentStatus === Order::PAYMENT_PAID || $paymentStatus === Order::PAYMENT_REFUNDED) ? $this->faker->dateTimeBetween('-1 month', '-1 day') : null,
            'shipped_at' => ($status === Order::STATUS_SHIPPED || $status === Order::STATUS_DELIVERED) ? $this->faker->dateTimeBetween('-1 month', '-1 day') : null,
            'delivered_at' => ($status === Order::STATUS_DELIVERED) ? $this->faker->dateTimeBetween('-1 month', '-1 day') : null,
            'cancelled_at' => ($status === Order::STATUS_CANCELLED) ? $this->faker->dateTimeBetween('-1 month', '-1 day') : null,
            'created_at' => $this->faker->dateTimeBetween('-2 months', 'now'),
            'updated_at' => fn (array $attributes) => $attributes['created_at'],
        ];
    }
}