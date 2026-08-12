<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/categories')->assertRedirect(route('login'));
    }

    public function test_non_admin_gets_forbidden(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/categories')->assertForbidden();
    }

    public function test_category_crud_flow_as_admin(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->get('/categories')->assertStatus(200);

        $this->actingAs($admin)->post('/categories', ['name' => 'Tech'])
            ->assertRedirect(route('categories.index'));

        $category = Category::where('name', 'Tech')->first();
        $this->assertNotNull($category);

        $this->actingAs($admin)->get("/categories/{$category->id}/edit")->assertStatus(200);

        $this->actingAs($admin)->put("/categories/{$category->id}", ['name' => 'Technology'])
            ->assertRedirect(route('categories.index'));

        $this->assertDatabaseHas('categories', ['id' => $category->id, 'name' => 'Technology']);

        $this->actingAs($admin)->delete("/categories/{$category->id}")
            ->assertRedirect(route('categories.index'));

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }
}
