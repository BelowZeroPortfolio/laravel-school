<?php

namespace Tests\Property;

use App\Http\Middleware\CheckRole;
use App\Models\User;
use Eris\Generator;
use Eris\TestTrait;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Tests\TestCase;

/**
 * Property tests for role-based access control.
 * **Feature: qr-attendance-laravel-migration**
 */
class RoleAccessPropertyTest extends TestCase
{
    use TestTrait;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->minimumEvaluationRatio(0.5);
    }

    /**
     * **Feature: qr-attendance-laravel-migration, Property 4: Admin bypasses all role checks**
     * **Validates: Requirements 2.1**
     * 
     * For any route protected by role middleware and any admin user,
     * the admin SHALL be granted access regardless of the route's specified role requirements.
     */
    public function testAdminBypassesAllRoleChecks(): void
    {
        $this->forAll(
            Generator\elements('teacher', 'principal', 'admin', 'teacher,principal', 'admin,teacher')
        )
        ->withMaxSize(100)
        ->then(function ($requiredRoles) {
            // Create an admin user
            $admin = User::factory()->admin()->create();

            // Create a mock request with the admin user
            $request = Request::create('/test', 'GET');
            $request->setUserResolver(fn () => $admin);

            // Create middleware instance
            $middleware = new CheckRole();

            // Parse roles
            $roles = explode(',', $requiredRoles);

            // Execute middleware - admin should always pass
            $response = $middleware->handle($request, function ($req) {
                return new Response('OK', 200);
            }, ...$roles);

            $this->assertEquals(
                200,
                $response->getStatusCode(),
                "Admin should bypass role check for roles: {$requiredRoles}"
            );
        });
    }

    /**
     * **Feature: qr-attendance-laravel-migration, Property 5: Principal inherits teacher access**
     * **Validates: Requirements 2.2**
     * 
     * For any route protected with 'teacher' role requirement and any principal user,
     * the principal SHALL be granted access.
     */
    public function testPrincipalInheritsTeacherAccess(): void
    {
        $this->forAll(
            Generator\constant('teacher')
        )
        ->withMaxSize(100)
        ->then(function ($requiredRole) {
            // Create a principal user
            $principal = User::factory()->principal()->create();

            // Create a mock request with the principal user
            $request = Request::create('/test', 'GET');
            $request->setUserResolver(fn () => $principal);

            // Create middleware instance
            $middleware = new CheckRole();

            // Execute middleware - principal should have teacher access
            $response = $middleware->handle($request, function ($req) {
                return new Response('OK', 200);
            }, $requiredRole);

            $this->assertEquals(
                200,
                $response->getStatusCode(),
                "Principal should inherit teacher access"
            );
        });
    }

    /**
     * **Feature: qr-attendance-laravel-migration, Property 6: Teacher denied admin routes**
     * **Validates: Requirements 2.3**
     * 
     * For any route protected with 'admin' role requirement and any teacher user,
     * the request SHALL return a 403 Forbidden response.
     */
    public function testTeacherDeniedAdminRoutes(): void
    {
        $this->forAll(
            Generator\constant('admin')
        )
        ->withMaxSize(100)
        ->then(function ($requiredRole) {
            // Create a teacher user
            $teacher = User::factory()->teacher()->create();

            // Create a mock request with the teacher user
            $request = Request::create('/test', 'GET');
            $request->setUserResolver(fn () => $teacher);

            // Create middleware instance
            $middleware = new CheckRole();

            // Execute middleware - teacher should be denied
            $exceptionThrown = false;
            $statusCode = null;

            try {
                $middleware->handle($request, function ($req) {
                    return new Response('OK', 200);
                }, $requiredRole);
            } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
                $exceptionThrown = true;
                $statusCode = $e->getStatusCode();
            }

            $this->assertTrue($exceptionThrown, "Teacher should be denied access to admin routes");
            $this->assertEquals(403, $statusCode, "Teacher should receive 403 Forbidden for admin routes");
        });
    }

    /**
     * **Feature: qr-attendance-laravel-migration, Property 7: Unauthenticated users redirected**
     * **Validates: Requirements 2.4**
     * 
     * For any protected route and any unauthenticated request,
     * the response SHALL be a redirect to the login page.
     */
    public function testUnauthenticatedUsersRedirected(): void
    {
        $this->forAll(
            Generator\elements('teacher', 'principal', 'admin', 'teacher,principal')
        )
        ->withMaxSize(100)
        ->then(function ($requiredRoles) {
            // Create a mock request with NO user (unauthenticated)
            $request = Request::create('/test', 'GET');
            $request->setUserResolver(fn () => null);

            // Create middleware instance
            $middleware = new CheckRole();

            // Parse roles
            $roles = explode(',', $requiredRoles);

            // Execute middleware - should redirect to login
            $response = $middleware->handle($request, function ($req) {
                return new Response('OK', 200);
            }, ...$roles);

            $this->assertTrue(
                $response->isRedirect(),
                "Unauthenticated user should be redirected"
            );
        });
    }
}
