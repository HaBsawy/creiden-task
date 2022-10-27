<?php

namespace Tests\Feature\Api;

use App\Models\Admin;
use App\Models\Storage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class StorageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = Admin::factory()->create();
        $this->user = User::factory()->create();
        $this->secUser = User::factory()->create();
        $this->storage = Storage::factory()->create([
            'user_id' => $this->user->id
        ]);
        $this->url = '/api/storages';
    }

    private function data($data = []) : array
    {
        $default = ['user_id' => $this->secUser->id];
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

    public function test_store_without_user_id_param()
    {
        Sanctum::actingAs($this->admin);
        $response = $this->actingAs($this->admin, 'admin')
            ->postJson($this->url, $this->data(['user_id' => null]));
        $response->assertStatus(422)
            ->assertJson($this->validationError('The user id field is required.'));
    }

    public function test_store_with_non_exist_user_id_param()
    {
        Sanctum::actingAs($this->admin);
        $response = $this->actingAs($this->admin, 'admin')
            ->postJson($this->url, $this->data(['user_id' => 1000]));
        $response->assertStatus(422)
            ->assertJson($this->validationError('The selected user id is invalid.'));
    }

    public function test_store_with_non_unique_user_id_param()
    {
        Sanctum::actingAs($this->admin);
        $response = $this->actingAs($this->admin, 'admin')
            ->postJson($this->url, $this->data(['user_id' => $this->user->id]));
        $response->assertStatus(422)
            ->assertJson($this->validationError('The user id has already been taken.'));
    }

    public function test_store_with_valid_params()
    {
        Sanctum::actingAs($this->admin);
        $response = $this->actingAs($this->admin, 'admin')
            ->postJson($this->url, $this->data());
        $response->assertStatus(201)
            ->assertJson([
                'msg'           => 'The storage created successfully',
                'isSuccess'     => true,
                'statusCode'    => 201,
            ]);
    }

    public function test_show_without_authentication()
    {
        $response = $this->getJson($this->url . '/' . $this->storage->id);
        $response->assertStatus(401)->assertJson(['message' => 'Unauthenticated.']);
    }

    public function test_show_with_user_authentication()
    {
        Sanctum::actingAs($this->user);
        $response = $this->actingAs($this->user, 'user')
            ->getJson($this->url . '/' . $this->storage->id);
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
            ->getJson($this->url . '/' . $this->storage->id);
        $response->assertOk()->assertJson([
            'msg'           => '',
            'isSuccess'     => true,
            'statusCode'    => 200,
        ]);
    }

    public function test_update_without_authentication()
    {
        $response = $this->putJson($this->url . '/' . $this->storage->id);
        $response->assertStatus(401)->assertJson(['message' => 'Unauthenticated.']);
    }

    public function test_update_with_user_authentication()
    {
        Sanctum::actingAs($this->user);
        $response = $this->actingAs($this->user, 'user')
            ->putJson($this->url . '/' . $this->storage->id);
        $response->assertStatus(401)->assertJson(['message' => 'Unauthenticated.']);
    }

    public function test_update_without_user_id_param()
    {
        Sanctum::actingAs($this->admin);
        $response = $this->actingAs($this->admin, 'admin')
            ->putJson($this->url . '/' . $this->storage->id, $this->data(['user_id' => null]));
        $response->assertStatus(422)
            ->assertJson($this->validationError('The user id field is required.'));
    }

    public function test_update_with_non_exist_user_id_param()
    {
        Sanctum::actingAs($this->admin);
        $response = $this->actingAs($this->admin, 'admin')
            ->putJson($this->url . '/' . $this->storage->id, $this->data(['user_id' => 1000]));
        $response->assertStatus(422)
            ->assertJson($this->validationError('The selected user id is invalid.'));
    }

    public function test_update_with_non_unique_user_id_param()
    {
        Storage::factory()->create(['user_id' => $this->secUser->id]);
        Sanctum::actingAs($this->admin);
        $response = $this->actingAs($this->admin, 'admin')
            ->putJson($this->url . '/' . $this->storage->id, $this->data(['user_id' => $this->secUser->id]));
        $response->assertStatus(422)
            ->assertJson($this->validationError('The user id has already been taken.'));
    }

    public function test_update_with_non_exist_storage()
    {
        Sanctum::actingAs($this->admin);
        $response = $this->actingAs($this->admin, 'admin')
            ->putJson($this->url . '/1000', $this->data());
        $response->assertNotFound()->assertJson($this->notFound());
    }

    public function test_update_with_valid_params()
    {
        Sanctum::actingAs($this->admin);
        $response = $this->actingAs($this->admin, 'admin')
            ->putJson($this->url . '/' . $this->storage->id, $this->data());
        $response->assertStatus(202)->assertJson([
            'msg'           => 'The storage updated successfully',
            'isSuccess'     => true,
            'statusCode'    => 202,
        ]);
    }

    public function test_delete_without_authentication()
    {
        $response = $this->deleteJson($this->url . '/' . $this->storage->id);
        $response->assertStatus(401)->assertJson(['message' => 'Unauthenticated.']);
    }

    public function test_delete_with_user_authentication()
    {
        Sanctum::actingAs($this->user);
        $response = $this->actingAs($this->user, 'user')
            ->deleteJson($this->url . '/' . $this->storage->id);
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
            ->deleteJson($this->url . '/' . $this->storage->id);
        $response->assertStatus(202)->assertJson([
            'msg'           => 'The storage deleted successfully',
            'isSuccess'     => true,
            'statusCode'    => 202,
        ]);
    }
}
