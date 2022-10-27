<?php

namespace Tests\Feature\Api;

use App\Models\Admin;
use App\Models\Item;
use App\Models\Storage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ItemTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = Admin::factory()->create();
        $this->user = User::factory()->create();
        $this->storage = Storage::factory()->create([
            'user_id' => $this->user->id
        ]);
        $this->item = Item::factory()->create([
            'storage_id'    => $this->storage->id,
            'name'          => 'first item',
            'description'   => 'test description',
        ]);
        $this->url = '/api/items';
    }

    private function data($data = []) : array
    {
        $default = [
            'storage_id'    => $this->storage->id,
            'name'          => 'second item',
            'description'   => 'test description 2',
        ];

        return array_merge($default, $data);
    }

    private function validationError($msg): array
    {
        return [
            'msg'           => $msg,
            'isSuccess'     => false,
            'statusCode'    => 422,
            'payload'       => null
        ];
    }

    private function notFound(): array
    {
        return [
            'msg'           => 'Not Found',
            'isSuccess'     => false,
            'statusCode'    => 404,
            'payload'       => null
        ];
    }

    public function test_index_without_authentication()
    {
        $response = $this->getJson($this->url);
        $response->assertStatus(401)->assertJson(['message' => 'Unauthenticated.']);
    }

    public function test_index_with_user_authentication()
    {
        Sanctum::actingAs($this->user);
        $response = $this->actingAs($this->user, 'user')->getJson($this->url);
        $response->assertStatus(401)->assertJson(['message' => 'Unauthenticated.']);
    }

    public function test_index_with_admin_authentication()
    {
        Sanctum::actingAs($this->admin);
        $response = $this->actingAs($this->admin, 'admin')->getJson($this->url);
        $response->assertOk()->assertJson([
            'msg'           => '',
            'isSuccess'     => true,
            'statusCode'    => 200
        ]);
    }

    public function test_store_without_authentication()
    {
        $response = $this->postJson($this->url);
        $response->assertStatus(401)->assertJson(['message' => 'Unauthenticated.']);
    }

    public function test_store_with_user_authentication()
    {
        Sanctum::actingAs($this->user);
        $response = $this->actingAs($this->user, 'user')->postJson($this->url);
        $response->assertStatus(401)->assertJson(['message' => 'Unauthenticated.']);
    }

    public function test_store_without_storage_id_param()
    {
        Sanctum::actingAs($this->admin);
        $response = $this->actingAs($this->admin, 'admin')
            ->postJson($this->url, $this->data(['storage_id' => null]));
        $response->assertStatus(422)
            ->assertJson($this->validationError('The storage id field is required.'));
    }

    public function test_store_with_non_exist_storage_id_param()
    {
        Sanctum::actingAs($this->admin);
        $response = $this->actingAs($this->admin, 'admin')
            ->postJson($this->url, $this->data(['storage_id' => 1000]));
        $response->assertStatus(422)
            ->assertJson($this->validationError('The selected storage id is invalid.'));
    }

    public function test_store_without_name_param()
    {
        Sanctum::actingAs($this->admin);
        $response = $this->actingAs($this->admin, 'admin')
            ->postJson($this->url, $this->data(['name' => null]));
        $response->assertStatus(422)
            ->assertJson($this->validationError('The name field is required.'));
    }

    public function test_store_with_short_name_param()
    {
        Sanctum::actingAs($this->admin);
        $response = $this->actingAs($this->admin, 'admin')
            ->postJson($this->url, $this->data(['name' => 'es']));
        $response->assertStatus(422)
            ->assertJson($this->validationError('The name must be between 3 and 255 characters.'));
    }

    public function test_store_with_long_name_param()
    {
        Sanctum::actingAs($this->admin);
        $response = $this->actingAs($this->admin, 'admin')
            ->postJson($this->url, $this->data(['name' => Str::random(300)]));
        $response->assertStatus(422)
            ->assertJson($this->validationError('The name must be between 3 and 255 characters.'));
    }

    public function test_store_without_description_param()
    {
        Sanctum::actingAs($this->admin);
        $response = $this->actingAs($this->admin, 'admin')
            ->postJson($this->url, $this->data(['description' => null]));
        $response->assertStatus(422)
            ->assertJson($this->validationError('The description field is required.'));
    }

    public function test_store_with_short_description_param()
    {
        Sanctum::actingAs($this->admin);
        $response = $this->actingAs($this->admin, 'admin')
            ->postJson($this->url, $this->data(['description' => 'es']));
        $response->assertStatus(422)
            ->assertJson($this->validationError('The description must be at least 3 characters.'));
    }

    public function test_store_with_valid_params()
    {
        Sanctum::actingAs($this->admin);
        $response = $this->actingAs($this->admin, 'admin')
            ->postJson($this->url, $this->data());
        $response->assertStatus(201)
            ->assertJson([
                'msg'           => 'The item created successfully',
                'isSuccess'     => true,
                'statusCode'    => 201,
            ]);
    }

    public function test_show_without_authentication()
    {
        $response = $this->getJson($this->url . '/' . $this->item->id);
        $response->assertStatus(401)->assertJson(['message' => 'Unauthenticated.']);
    }

    public function test_show_with_user_authentication()
    {
        Sanctum::actingAs($this->user);
        $response = $this->actingAs($this->user, 'user')
            ->getJson($this->url . '/' . $this->item->id);
        $response->assertStatus(401)->assertJson(['message' => 'Unauthenticated.']);
    }

    public function test_show_with_non_exist_user()
    {
        Sanctum::actingAs($this->admin);
        $response = $this->actingAs($this->admin, 'admin')
            ->getJson($this->url . '/1000');
        $response->assertNotFound()->assertJson($this->notFound());
    }

    public function test_show_with_admin_authentication()
    {
        Sanctum::actingAs($this->admin);
        $response = $this->actingAs($this->admin, 'admin')
            ->getJson($this->url . '/' . $this->item->id);
        $response->assertOk()->assertJson([
            'msg'           => '',
            'isSuccess'     => true,
            'statusCode'    => 200,
        ]);
    }

    public function test_update_without_authentication()
    {
        $response = $this->putJson($this->url . '/' . $this->item->id);
        $response->assertStatus(401)->assertJson(['message' => 'Unauthenticated.']);
    }

    public function test_update_with_user_authentication()
    {
        Sanctum::actingAs($this->user);
        $response = $this->actingAs($this->user, 'user')
            ->putJson($this->url . '/' . $this->item->id);
        $response->assertStatus(401)->assertJson(['message' => 'Unauthenticated.']);
    }

    public function test_update_without_name_param()
    {
        Sanctum::actingAs($this->admin);
        $response = $this->actingAs($this->admin, 'admin')
            ->putJson($this->url . '/' . $this->item->id, $this->data(['name' => null]));
        $response->assertStatus(422)
            ->assertJson($this->validationError('The name field is required.'));
    }

    public function test_update_with_short_name_param()
    {
        Sanctum::actingAs($this->admin);
        $response = $this->actingAs($this->admin, 'admin')
            ->putJson($this->url . '/' . $this->item->id, $this->data(['name' => 'es']));
        $response->assertStatus(422)
            ->assertJson($this->validationError('The name must be between 3 and 255 characters.'));
    }

    public function test_update_with_long_name_param()
    {
        Sanctum::actingAs($this->admin);
        $response = $this->actingAs($this->admin, 'admin')
            ->putJson($this->url . '/' . $this->item->id, $this->data(['name' => Str::random(300)]));
        $response->assertStatus(422)
            ->assertJson($this->validationError('The name must be between 3 and 255 characters.'));
    }

    public function test_update_without_description_param()
    {
        Sanctum::actingAs($this->admin);
        $response = $this->actingAs($this->admin, 'admin')
            ->putJson($this->url . '/' . $this->item->id, $this->data(['description' => null]));
        $response->assertStatus(422)
            ->assertJson($this->validationError('The description field is required.'));
    }

    public function test_update_with_short_description_param()
    {
        Sanctum::actingAs($this->admin);
        $response = $this->actingAs($this->admin, 'admin')
            ->putJson($this->url . '/' . $this->item->id, $this->data(['description' => 'es']));
        $response->assertStatus(422)
            ->assertJson($this->validationError('The description must be at least 3 characters.'));
    }

    public function test_delete_without_authentication()
    {
        $response = $this->deleteJson($this->url . '/' . $this->item->id);
        $response->assertStatus(401)->assertJson(['message' => 'Unauthenticated.']);
    }

    public function test_delete_with_user_authentication()
    {
        Sanctum::actingAs($this->user);
        $response = $this->actingAs($this->user, 'user')
            ->deleteJson($this->url . '/' . $this->item->id);
        $response->assertStatus(401)->assertJson(['message' => 'Unauthenticated.']);
    }

    public function test_delete_with_non_exist_user()
    {
        Sanctum::actingAs($this->admin);
        $response = $this->actingAs($this->admin, 'admin')
            ->putJson($this->url . '/1000');
        $response->assertNotFound(202)->assertJson($this->notFound());
    }

    public function test_delete_with_admin_authentication()
    {
        Sanctum::actingAs($this->admin);
        $response = $this->actingAs($this->admin, 'admin')
            ->deleteJson($this->url . '/' . $this->item->id);
        $response->assertStatus(202)->assertJson([
            'msg'           => 'The item deleted successfully',
            'isSuccess'     => true,
            'statusCode'    => 202,
        ]);
    }
}
