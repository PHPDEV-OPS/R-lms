<?php

namespace Tests\Feature;

use App\Http\Middleware\AdminMiddleware;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class AdminMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_middleware_allows_admin_users()
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin);

        $response = $this->get('/admin/courses');

        $response->assertStatus(200);
    }

    public function test_admin_middleware_blocks_non_admin_users()
    {
        $user = User::factory()->create(['is_admin' => false]);

        $request = Request::create('/admin/courses', 'GET');
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        $middleware = new AdminMiddleware();

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);

        $middleware->handle($request, function ($req) {
            return response('OK');
        });
    }

    public function test_admin_middleware_blocks_unauthenticated_users()
    {
        $request = Request::create('/admin/courses', 'GET');
        $request->setUserResolver(function () {
            return null;
        });

        $middleware = new AdminMiddleware();

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);

        $middleware->handle($request, function ($req) {
            return response('OK');
        });
    }

    public function test_admin_middleware_returns_403_for_non_admin_users()
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user);

        $response = $this->get('/admin/courses');

        $response->assertStatus(403);
    }

    public function test_admin_middleware_allows_admin_users_to_access_admin_routes()
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)
            ->get('/admin/courses');

        $response->assertStatus(200);
    }
}