<?php

namespace Tests\Feature\Api;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = Admin::factory()->create();
        $this->secUser = User::factory()->create();
        $this->user = User::factory()->create([
            'name'      => 'Eslam',
            'email'     => 'eslam2@eslam.com',
            'password'  => bcrypt('123123123'),
        ]);
        $this->url = '/api/users';
    }

    private function data($data = []) : array
    {
        $default = [
            'name'      => 'Eslam',
            'email'     => 'eslam@eslam.com',
            'password'  => '123123123',
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

    public function test_store_without_name_param()
    {
        Sanctum::actingAs($this->admin);
        $response = $this->actingAs($this->admin, 'admin')
            ->postJson($this->url, $this->data(['name' => null]));
        $response->assertStatus(422)
            ->assertJson($this->validationError('The name field is required.'));
    }

    public function test_store_with_small_name_param()
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

    public function test_store_without_email_param()
    {
        Sanctum::actingAs($this->admin);
        $response = $this->actingAs($this->admin, 'admin')
            ->postJson($this->url, $this->data(['email' => null]));
        $response->assertStatus(422)
            ->assertJson($this->validationError('The email field is required.'));
    }

    public function test_store_with_invalid_email_param()
    {
        Sanctum::actingAs($this->admin);
        $response = $this->actingAs($this->admin, 'admin')
            ->postJson($this->url, $this->data(['email' => 'test']));
        $response->assertStatus(422)
            ->assertJson($this->validationError('The email must be a valid email address.'));
    }

    public function test_store_with_non_unique_email_param()
    {
        Sanctum::actingAs($this->admin);
        $response = $this->actingAs($this->admin, 'admin')
            ->postJson($this->url, $this->data(['email' => 'eslam2@eslam.com']));
        $response->assertStatus(422)
            ->assertJson($this->validationError('The email has already been taken.'));
    }

    public function test_store_without_password_param()
    {
        Sanctum::actingAs($this->admin);
        $response = $this->actingAs($this->admin, 'admin')
            ->postJson($this->url, $this->data(['password' => null]));
        $response->assertStatus(422)
            ->assertJson($this->validationError('The password field is required.'));
    }

    public function test_store_with_small_password_param()
    {
        Sanctum::actingAs($this->admin);
        $response = $this->actingAs($this->admin, 'admin')
            ->postJson($this->url, $this->data(['password' => '123123']));
        $response->assertStatus(422)
            ->assertJson($this->validationError('The password must be at least 8 characters.'));
    }

    public function test_store_with_valid_params()
    {
        Sanctum::actingAs($this->admin);
        $response = $this->actingAs($this->admin, 'admin')
            ->postJson($this->url, $this->data());
        $response->assertStatus(201)
            ->assertJson([
                'msg'           => 'The user created successfully',
                'isSuccess'     => true,
                'statusCode'    => 201,
            ]);
    }

    public function test_show_without_authentication()
    {
        $response = $this->getJson($this->url . '/' . $this->user->id);
        $response->assertStatus(401)->assertJson(['message' => 'Unauthenticated.']);
    }

    public function test_show_with_user_authentication()
    {
        Sanctum::actingAs($this->user);
        $response = $this->actingAs($this->user, 'user')
            ->getJson($this->url . '/' . $this->user->id);
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
            ->getJson($this->url . '/' . $this->user->id);
        $response->assertOk()->assertJson([
            'msg'           => '',
            'isSuccess'     => true,
            'statusCode'    => 200,
        ]);
    }

    public function test_update_without_authentication()
    {
        $response = $this->putJson($this->url . '/' . $this->user->id);
        $response->assertStatus(401)->assertJson(['message' => 'Unauthenticated.']);
    }

    public function test_update_with_user_authentication()
    {
        Sanctum::actingAs($this->user);
        $response = $this->actingAs($this->user, 'user')
            ->putJson($this->url . '/' . $this->user->id);
        $response->assertStatus(401)->assertJson(['message' => 'Unauthenticated.']);
    }

    public function test_update_without_name_param()
    {
        Sanctum::actingAs($this->admin);
        $response = $this->actingAs($this->admin, 'admin')
            ->putJson($this->url . '/' . $this->secUser->id, $this->data(['name' => null]));
        $response->assertStatus(422)
            ->assertJson($this->validationError('The name field is required.'));
    }

    public function test_update_with_small_name_param()
    {
        Sanctum::actingAs($this->admin);
        $response = $this->actingAs($this->admin, 'admin')
            ->putJson($this->url . '/' . $this->secUser->id, $this->data(['name' => 'es']));
        $response->assertStatus(422)
            ->assertJson($this->validationError('The name must be between 3 and 255 characters.'));
    }

    public function test_update_with_long_name_param()
    {
        Sanctum::actingAs($this->admin);
        $response = $this->actingAs($this->admin, 'admin')
            ->putJson($this->url . '/' . $this->secUser->id, $this->data(['name' => Str::random(300)]));
        $response->assertStatus(422)
            ->assertJson($this->validationError('The name must be between 3 and 255 characters.'));
    }

    public function test_update_without_email_param()
    {
        Sanctum::actingAs($this->admin);
        $response = $this->actingAs($this->admin, 'admin')
            ->putJson($this->url . '/' . $this->secUser->id, $this->data(['email' => null]));
        $response->assertStatus(422)
            ->assertJson($this->validationError('The email field is required.'));
    }

    public function test_update_with_invalid_email_param()
    {
        Sanctum::actingAs($this->admin);
        $response = $this->actingAs($this->admin, 'admin')
            ->putJson($this->url . '/' . $this->secUser->id, $this->data(['email' => 'test']));
        $response->assertStatus(422)
            ->assertJson($this->validationError('The email must be a valid email address.'));
    }

    public function test_update_with_non_unique_email_param()
    {
        Sanctum::actingAs($this->admin);
        $response = $this->actingAs($this->admin, 'admin')
            ->putJson($this->url . '/' . $this->secUser->id, $this->data(['email' => 'eslam2@eslam.com']));
        $response->assertStatus(422)
            ->assertJson($this->validationError('The email has already been taken.'));
    }

    public function test_update_without_password_param()
    {
        Sanctum::actingAs($this->admin);
        $response = $this->actingAs($this->admin, 'admin')
            ->putJson($this->url . '/' . $this->secUser->id, $this->data(['password' => null]));
        $response->assertStatus(422)
            ->assertJson($this->validationError('The password field is required.'));
    }

    public function test_update_with_small_password_param()
    {
        Sanctum::actingAs($this->admin);
        $response = $this->actingAs($this->admin, 'admin')
            ->putJson($this->url . '/' . $this->secUser->id, $this->data(['password' => '123123']));
        $response->assertStatus(422)
            ->assertJson($this->validationError('The password must be at least 8 characters.'));
    }

    public function test_update_with_non_exist_user()
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
            ->putJson($this->url . '/' . $this->secUser->id, $this->data());
        $response->assertStatus(202)->assertJson([
            'msg'           => 'The user updated successfully',
            'isSuccess'     => true,
            'statusCode'    => 202,
        ]);
    }

    public function test_delete_without_authentication()
    {
        $response = $this->deleteJson($this->url . '/' . $this->user->id);
        $response->assertStatus(401)->assertJson(['message' => 'Unauthenticated.']);
    }

    public function test_delete_with_user_authentication()
    {
        Sanctum::actingAs($this->user);
        $response = $this->actingAs($this->user, 'user')
            ->deleteJson($this->url . '/' . $this->user->id);
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
            ->deleteJson($this->url . '/' . $this->secUser->id);
        $response->assertStatus(202)->assertJson([
            'msg'           => 'The user deleted successfully',
            'isSuccess'     => true,
            'statusCode'    => 202,
        ]);
    }
}
