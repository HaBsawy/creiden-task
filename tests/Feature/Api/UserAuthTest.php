<?php

namespace Tests\Feature\Api;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create([
            'name'      => 'Eslam',
            'email'     => 'eslam2@eslam.com',
            'password'  => bcrypt('123123123'),
        ]);
        $this->url = '/api/users/auth';
    }

    private function data($data = []) : array
    {
        $default = [
            'name'                  => 'Eslam',
            'email'                 => 'eslam@eslam.com',
            'password'              => '123123123',
            'password_confirmation' => '123123123',
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

    private function unauthenticated(): array
    {
        return [
            'msg'           => 'Not Authenticated',
            'isSuccess'     => false,
            'statusCode'    => 401,
            'payload'       => null
        ];
    }

    public function test_register_without_name_param()
    {
        $response = $this->postJson($this->url . '/register',
            $this->data(['name' => null]));
        $response->assertStatus(422)
            ->assertJson($this->validationError('The name field is required.'));
    }

    public function test_register_with_small_name_param()
    {
        $response = $this->postJson($this->url . '/register',
            $this->data(['name' => 'es']));
        $response->assertStatus(422)
            ->assertJson($this->validationError('The name must be between 3 and 255 characters.'));
    }

    public function test_register_with_long_name_param()
    {
        $response = $this->postJson($this->url . '/register',
            $this->data(['name' => Str::random(300)]));
        $response->assertStatus(422)
            ->assertJson($this->validationError('The name must be between 3 and 255 characters.'));
    }

    public function test_register_without_email_param()
    {
        $response = $this->postJson($this->url . '/register',
            $this->data(['email' => null]));
        $response->assertStatus(422)
            ->assertJson($this->validationError('The email field is required.'));
    }

    public function test_register_with_invalid_email_param()
    {
        $response = $this->postJson($this->url . '/register',
            $this->data(['email' => 'test']));
        $response->assertStatus(422)
            ->assertJson($this->validationError('The email must be a valid email address.'));
    }

    public function test_register_with_non_unique_email_param()
    {
        $response = $this->postJson($this->url . '/register',
            $this->data(['email' => 'eslam2@eslam.com']));
        $response->assertStatus(422)
            ->assertJson($this->validationError('The email has already been taken.'));
    }

    public function test_register_without_password_param()
    {
        $response = $this->postJson($this->url . '/register',
            $this->data(['password' => null]));
        $response->assertStatus(422)
            ->assertJson($this->validationError('The password field is required.'));
    }

    public function test_register_with_small_password_param()
    {
        $response = $this->postJson($this->url . '/register',
            $this->data(['password' => '123123']));
        $response->assertStatus(422)
            ->assertJson($this->validationError('The password must be at least 8 characters.'));
    }

    public function test_register_with_unmatched_password_param()
    {
        $response = $this->postJson($this->url . '/register',
            $this->data(['password' => '12341234']));
        $response->assertStatus(422)
            ->assertJson($this->validationError('The password confirmation does not match.'));
    }

    public function test_register_with_valid_params()
    {
        $response = $this->postJson($this->url . '/register', $this->data());
        $response->assertStatus(201)
            ->assertJson([
                'msg'           => 'The user registered successfully',
                'isSuccess'     => true,
                'statusCode'    => 201
            ]);
    }

    public function test_login_without_email_param()
    {
        $response = $this->postJson($this->url . '/login',
            $this->data(['email' => null]));
        $response->assertStatus(422)
            ->assertJson($this->validationError('The email field is required.'));
    }

    public function test_login_with_invalid_email_param()
    {
        $response = $this->postJson($this->url . '/login',
            $this->data(['email' => 'test']));
        $response->assertStatus(422)
            ->assertJson($this->validationError('The email must be a valid email address.'));
    }

    public function test_login_without_password_param()
    {
        $response = $this->postJson($this->url . '/login',
            $this->data(['password' => null]));
        $response->assertStatus(422)
            ->assertJson($this->validationError('The password field is required.'));
    }

    public function test_login_with_non_exist_email()
    {
        $response = $this->postJson($this->url . '/login',
            $this->data(['email' => 'eslam3@eslam.com']));
        $response->assertStatus(401)->assertJson($this->unauthenticated());
    }

    public function test_login_with_non_matched_password()
    {
        $response = $this->postJson($this->url . '/login',
            $this->data(['email' => 'eslam2@eslam.com', 'password' => '12341234']));
        $response->assertStatus(401)->assertJson($this->unauthenticated());
    }

    public function test_login_with_valid_params()
    {
        $response = $this->postJson($this->url . '/login',
            $this->data(['email' => 'eslam2@eslam.com']));
        $response->assertStatus(202)->assertJson([
            'msg'           => 'Login Successfully',
            'isSuccess'     => true,
            'statusCode'    => 202
        ]);
    }

    public function test_logout_without_authentication()
    {
        $response = $this->postJson($this->url . '/logout');
        $response->assertStatus(401)->assertJson(['message' => 'Unauthenticated.']);
    }

    public function test_logout_with_user_authentication()
    {
        Sanctum::actingAs($this->user);
        $response = $this->actingAs($this->user, 'user')->postJson($this->url . '/logout');
        $response->assertStatus(202)->assertJson([
            'msg'           => "Logout Successfully",
            'isSuccess'     => true,
            'statusCode'    => 202,
            'payload'       => null
        ]);
    }

    public function test_logout_with_admin_authentication()
    {
        $admin = Admin::factory()->create();
        Sanctum::actingAs($admin);
        $response = $this->actingAs($admin, 'admin')->postJson($this->url . '/logout');
        $response->assertStatus(401)->assertJson(['message' => 'Unauthenticated.']);
    }
}
