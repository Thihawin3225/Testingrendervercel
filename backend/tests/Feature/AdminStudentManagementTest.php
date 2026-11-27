<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdminStudentManagementTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 120);
            $table->string('email')->unique();
            $table->string('password');
            $table->string('role')->default('student');
            $table->string('status')->default('active');
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('student_profiles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->restrictOnDelete();
            $table->string('student_number')->unique();
            $table->string('avatar_cloudinary_public_id')->nullable();
            $table->string('avatar_cloudinary_asset_id')->nullable();
            $table->text('bio')->nullable();
            $table->timestamps();
        });

        Schema::create('personal_access_tokens', function (Blueprint $table): void {
            $table->id();
            $table->morphs('tokenable');
            $table->string('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        Schema::create('marketplace_listings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('title');
            $table->timestamps();
        });

        Schema::create('student_academic_enrollments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->timestamps();
        });

        Schema::create('mentor_profiles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->timestamps();
        });

        Schema::create('peer_learning_requests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('learner_id')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });

        Schema::create('mentoring_bookings', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('peer_learning_match_id');
            $table->timestamps();
        });
    }

    public function test_public_registration_is_disabled_and_guest_cannot_create_a_student(): void
    {
        $this->postJson('/api/v1/auth/register', $this->studentPayload())->assertNotFound();

        $this->postJson('/api/v1/admin/students', $this->studentPayload())
            ->assertUnauthorized();
    }

    public function test_students_cannot_create_students_or_access_admin_endpoints(): void
    {
        $student = $this->makeUser('student');
        $token = $student->createToken('test')->plainTextToken;

        $this->withToken($token)
            ->postJson('/api/v1/admin/students', $this->studentPayload())
            ->assertForbidden();

        $this->withToken($token)
            ->getJson('/api/v1/admin/students')
            ->assertForbidden();

        $this->withToken($token)
            ->patchJson("/api/v1/admin/users/{$student->id}", ['role' => 'admin'])
            ->assertForbidden();

        $this->assertSame('student', $student->fresh()->role);
    }

    public function test_admin_can_create_an_active_student_with_a_hashed_password_and_profile(): void
    {
        $admin = $this->makeUser('admin');
        $payload = $this->studentPayload([
            'role' => 'admin',
            'status' => 'suspended',
            'user_id' => 999,
        ]);

        $response = $this->actingAs($admin)
            ->postJson('/api/v1/admin/students', $payload)
            ->assertCreated()
            ->assertJsonPath('user.role', 'student')
            ->assertJsonPath('user.status', 'active');

        $studentId = $response->json('user.id');
        $student = User::findOrFail($studentId);

        $this->assertSame('student', $student->role);
        $this->assertSame('active', $student->status);
        $this->assertNotSame($payload['password'], $student->password);
        $this->assertTrue(Hash::check($payload['password'], $student->password));
        $this->assertDatabaseHas('student_profiles', [
            'user_id' => $studentId,
            'student_number' => $payload['student_number'],
        ]);
    }

    public function test_creation_rejects_duplicate_email_student_number_and_invalid_domain(): void
    {
        $admin = $this->makeUser('admin');
        $existing = $this->makeUser('student');
        $existing->studentProfile()->create(['student_number' => 'STU-001']);

        $this->actingAs($admin)
            ->postJson('/api/v1/admin/students', $this->studentPayload([
                'email' => $existing->email,
            ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');

        $this->actingAs($admin)
            ->postJson('/api/v1/admin/students', $this->studentPayload([
                'student_number' => 'STU-001',
            ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('student_number');

        $this->actingAs($admin)
            ->postJson('/api/v1/admin/students', $this->studentPayload([
                'email' => 'student@example.com',
            ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');
    }

    public function test_admin_provisioned_student_can_log_in_and_login_creates_one_token(): void
    {
        $admin = $this->makeUser('admin');
        $this->actingAs($admin)
            ->postJson('/api/v1/admin/students', $this->studentPayload())
            ->assertCreated();

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'student@ucspyay.edu.mm',
            'password' => 'safe-password',
        ])->assertOk()->assertJsonStructure(['user', 'token', 'token_type']);

        $student = User::where('email', 'student@ucspyay.edu.mm')->firstOrFail();
        $this->assertSame(1, $student->tokens()->count());
        $this->assertNotEmpty($response->json('token'));
    }

    public function test_admin_can_list_and_edit_students_but_cannot_manage_admin_accounts_or_change_roles(): void
    {
        $admin = $this->makeUser('admin');
        $student = $this->makeUser('student');
        $otherAdmin = $this->makeUser('admin', ['email' => 'other@ucspyay.edu.mm']);

        $this->actingAs($admin)
            ->getJson('/api/v1/admin/students')
            ->assertOk()
            ->assertJsonFragment(['id' => $student->id])
            ->assertJsonMissing(['id' => $otherAdmin->id]);

        $this->actingAs($admin)
            ->patchJson("/api/v1/admin/users/{$student->id}", [
                'name' => 'Updated Student',
                'role' => 'admin',
            ])
            ->assertOk()
            ->assertJsonPath('user.name', 'Updated Student')
            ->assertJsonPath('user.role', 'student');

        $this->actingAs($admin)
            ->patchJson("/api/v1/admin/users/{$otherAdmin->id}", ['name' => 'Changed Admin'])
            ->assertNotFound();

        $this->actingAs($admin)
            ->deleteJson("/api/v1/admin/users/{$otherAdmin->id}")
            ->assertNotFound();

        $this->assertSame('admin', $otherAdmin->fresh()->role);
    }

    public function test_suspension_revokes_tokens_and_reactivation_requires_a_new_login(): void
    {
        $admin = $this->makeUser('admin');
        $student = $this->makeUser('student');
        $token = $student->createToken('existing')->plainTextToken;

        DB::table('users')->where('id', $student->id)->update(['status' => 'suspended']);
        $this->withToken($token)->getJson('/api/v1/auth/me')->assertForbidden();
        DB::table('users')->where('id', $student->id)->update(['status' => 'active']);

        $this->actingAs($admin)
            ->patchJson("/api/v1/admin/users/{$student->id}", ['status' => 'suspended'])
            ->assertOk();

        $this->assertSame(0, $student->tokens()->count());
        $this->app['auth']->forgetGuards();
        $this->withToken($token)->getJson('/api/v1/auth/me')->assertUnauthorized();
        $this->postJson('/api/v1/auth/login', [
            'email' => $student->email,
            'password' => 'safe-password',
        ])->assertForbidden();

        $this->actingAs($admin)
            ->patchJson("/api/v1/admin/users/{$student->id}", ['status' => 'active'])
            ->assertOk();

        $this->assertSame(0, $student->tokens()->count());
        $this->postJson('/api/v1/auth/login', [
            'email' => $student->email,
            'password' => 'safe-password',
        ])->assertOk();
    }

    public function test_soft_deletion_revokes_tokens_blocks_login_and_preserves_related_records(): void
    {
        $admin = $this->makeUser('admin');
        $student = $this->makeUser('student');
        $student->studentProfile()->create(['student_number' => 'STU-002']);
        $token = $student->createToken('existing')->plainTextToken;
        $listingId = DB::table('marketplace_listings')->insertGetId([
            'user_id' => $student->id,
            'title' => 'Kept listing',
        ]);
        $enrollmentId = DB::table('student_academic_enrollments')->insertGetId([
            'user_id' => $student->id,
        ]);
        $mentorProfileId = DB::table('mentor_profiles')->insertGetId([
            'user_id' => $student->id,
        ]);
        $learningRequestId = DB::table('peer_learning_requests')->insertGetId([
            'learner_id' => $student->id,
        ]);
        $bookingId = DB::table('mentoring_bookings')->insertGetId([
            'peer_learning_match_id' => 1,
        ]);

        $this->actingAs($admin)
            ->deleteJson("/api/v1/admin/users/{$student->id}")
            ->assertOk();

        $this->assertNotNull(User::withTrashed()->findOrFail($student->id)->deleted_at);
        $this->assertSame(0, $student->tokens()->count());
        $this->app['auth']->forgetGuards();
        $this->withToken($token)->getJson('/api/v1/auth/me')->assertUnauthorized();
        $this->postJson('/api/v1/auth/login', [
            'email' => $student->email,
            'password' => 'safe-password',
        ])->assertForbidden();
        $this->assertDatabaseHas('student_profiles', [
            'user_id' => $student->id,
            'student_number' => 'STU-002',
        ]);
        $this->assertDatabaseHas('marketplace_listings', ['id' => $listingId]);
        $this->assertDatabaseHas('student_academic_enrollments', ['id' => $enrollmentId]);
        $this->assertDatabaseHas('mentor_profiles', ['id' => $mentorProfileId]);
        $this->assertDatabaseHas('peer_learning_requests', ['id' => $learningRequestId]);
        $this->assertDatabaseHas('mentoring_bookings', ['id' => $bookingId]);

        $this->app['auth']->forgetGuards();
        $this->actingAs($admin)
            ->getJson('/api/v1/admin/students')
            ->assertOk()
            ->assertJsonMissing(['id' => $student->id]);
    }

    private function makeUser(string $role, array $attributes = []): User
    {
        return User::create(array_merge([
            'name' => ucfirst($role),
            'email' => $role.'-'.uniqid().'@ucspyay.edu.mm',
            'password' => 'safe-password',
            'role' => $role,
            'status' => 'active',
        ], $attributes));
    }

    private function studentPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'New Student',
            'email' => 'student@ucspyay.edu.mm',
            'student_number' => 'STU-NEW',
            'password' => 'safe-password',
            'password_confirmation' => 'safe-password',
        ], $overrides);
    }
}
