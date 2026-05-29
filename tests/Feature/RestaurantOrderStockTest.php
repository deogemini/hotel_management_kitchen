<?php

namespace Tests\Feature;

use App\Models\MenuItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RestaurantOrderStockTest extends TestCase
{
    use RefreshDatabase;

    public function test_restaurant_order_deducts_menu_item_stock(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'cashier']));

        $menuItem = MenuItem::create([
            'name' => 'Soda',
            'category' => 'Drinks',
            'price' => 2000,
            'stock_quantity' => 10,
            'low_stock_quantity' => 3,
            'is_available' => true,
        ]);

        $response = $this->post(route('restaurant-orders.store'), [
            'customer_type' => 'Walk-in customer',
            'walk_in_customer_name' => 'Walk-in Guest',
            'payment_method' => 'Cash',
            'paid_amount' => 4000,
            'menu_item_id' => [$menuItem->id],
            'quantity' => [2],
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertSame(8, $menuItem->fresh()->stock_quantity);
    }

    public function test_restaurant_order_cannot_exceed_available_stock(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'cashier']));

        $menuItem = MenuItem::create([
            'name' => 'Water',
            'category' => 'Drinks',
            'price' => 1000,
            'stock_quantity' => 1,
            'low_stock_quantity' => 3,
            'is_available' => true,
        ]);

        $response = $this->post(route('restaurant-orders.store'), [
            'customer_type' => 'Walk-in customer',
            'walk_in_customer_name' => 'Walk-in Guest',
            'payment_method' => 'Cash',
            'paid_amount' => 2000,
            'menu_item_id' => [$menuItem->id],
            'quantity' => [2],
        ]);

        $response->assertSessionHasErrors('quantity');
        $this->assertSame(1, $menuItem->fresh()->stock_quantity);
    }
}
