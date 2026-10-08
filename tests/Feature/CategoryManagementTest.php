<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_and_rename_a_category_for_its_products(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));

        $this->post(route('categories.store'), ['name' => 'Perlengkapan'])
            ->assertRedirect(route('categories.index'));

        $category = Category::where('name', 'Perlengkapan')->firstOrFail();
        $product = Product::create([
            'name' => 'Botol minum',
            'category' => $category->name,
            'price' => 25000,
            'stock' => 4,
        ]);

        $this->put(route('categories.update', $category), ['name' => 'Perlengkapan Rumah'])
            ->assertRedirect(route('categories.index'));

        $this->assertDatabaseHas('categories', ['id' => $category->id, 'name' => 'Perlengkapan Rumah']);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'category' => 'Perlengkapan Rumah']);

        $this->get(route('products.create'))
            ->assertOk()
            ->assertSee('name="category"', false)
            ->assertSee('Perlengkapan Rumah');

        $this->get(route('categories.index'))->assertOk()->assertSee('Daftar kategori');
        $this->get(route('products.index'))->assertOk()->assertSee('Daftar produk');
        $this->get(route('products.index', ['category' => 'Perlengkapan Rumah']))
            ->assertOk()
            ->assertSee('Botol minum');
        $this->get(route('admin.dashboard'))->assertOk()->assertSee('Kategori');
    }

    public function test_category_in_use_cannot_be_deleted(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $category = Category::create(['name' => 'Peralatan Dapur']);
        Product::create([
            'name' => 'Gelas',
            'category' => $category->name,
            'price' => 5000,
            'stock' => 3,
        ]);

        $this->delete(route('categories.destroy', $category))
            ->assertSessionHasErrors('category');

        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    public function test_cashier_can_filter_the_product_catalog_by_category(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'kasir']));
        $category = Category::create(['name' => 'Alat Berkebun']);
        Product::create([
            'name' => 'Topi',
            'category' => $category->name,
            'price' => 35000,
            'stock' => 5,
            'is_active' => true,
        ]);

        $this->get(route('transactions.create'))
            ->assertOk()
            ->assertSee('id="categoryFilter"', false)
            ->assertSee('Alat Berkebun');

        $this->get(route('kasir.dashboard'))->assertOk();
        $this->get(route('transactions.index'))->assertOk();
    }

    public function test_cashiers_cannot_manage_product_categories(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'kasir']));

        $this->get(route('categories.index'))->assertForbidden();
    }
}
