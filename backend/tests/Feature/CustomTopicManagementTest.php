<?php

namespace Tests\Feature;

use App\Models\CustomTopic;
use App\Models\PeerLearningRequest;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CustomTopicManagementTest extends TestCase
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

        Schema::create('custom_topics', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('peer_learning_requests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('learner_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('custom_topic_id')->nullable()->constrained('custom_topics')->restrictOnDelete();
            $table->string('title', 180);
            $table->timestamps();
        });
    }

    public function test_recent_topics_include_recent_shared_topics_and_all_topics_created_by_viewer(): void
    {
        $viewer = $this->makeUser('Viewer');
        $other = $this->makeUser('Other Student');

        $sharedIds = [];

        foreach (range(1, 6) as $offset) {
            $sharedIds[] = DB::table('custom_topics')->insertGetId([
                'user_id' => $other->id,
                'title' => "Shared topic {$offset}",
                'created_at' => Carbon::now()->subMinutes($offset),
                'updated_at' => Carbon::now()->subMinutes($offset),
            ]);
        }

        $ownId = DB::table('custom_topics')->insertGetId([
            'user_id' => $viewer->id,
            'title' => 'My older topic',
            'created_at' => Carbon::now()->subDays(10),
            'updated_at' => Carbon::now()->subDays(10),
        ]);

        $response = $this->actingAs($viewer, 'sanctum')
            ->getJson('/api/v1/peer-learning/custom-topics/recent')
            ->assertOk();

        $topics = $response->json('data');
        $returnedIds = array_column($topics, 'id');

        $ownTopic = collect($topics)->firstWhere('id', $ownId);
        $sharedTopic = collect($topics)->firstWhere('id', $sharedIds[0]);

        $this->assertCount(6, $topics);
        $this->assertContains($ownId, $returnedIds);
        $this->assertNotContains($sharedIds[5], $returnedIds);
        $this->assertIsArray($ownTopic);
        $this->assertIsArray($sharedTopic);
        $this->assertTrue($ownTopic['is_mine']);
        $this->assertFalse($sharedTopic['is_mine']);
    }

    public function test_creator_can_delete_their_custom_topic_and_it_disappears_from_recent_topics(): void
    {
        $creator = $this->makeUser('Creator');
        $topic = $this->makeTopic($creator, 'My deletable topic');

        $this->actingAs($creator, 'sanctum')
            ->deleteJson("/api/v1/peer-learning/custom-topics/{$topic->id}")
            ->assertOk()
            ->assertJsonPath('message', 'Custom topic deleted.');

        $this->assertSoftDeleted('custom_topics', ['id' => $topic->id]);
        $this->assertSame([], $this->actingAs($creator, 'sanctum')
            ->getJson('/api/v1/peer-learning/custom-topics/recent')
            ->assertOk()
            ->json('data'));
    }

    public function test_non_creator_cannot_delete_a_custom_topic(): void
    {
        $creator = $this->makeUser('Creator');
        $other = $this->makeUser('Other Student');
        $topic = $this->makeTopic($creator, 'Not yours');

        $this->actingAs($other, 'sanctum')
            ->deleteJson("/api/v1/peer-learning/custom-topics/{$topic->id}")
            ->assertForbidden();

        $this->assertNotSoftDeleted('custom_topics', ['id' => $topic->id]);
    }

    public function test_existing_requests_keep_their_deleted_custom_topic_title(): void
    {
        $creator = $this->makeUser('Creator');
        $topic = $this->makeTopic($creator, 'Preserved topic');
        $request = PeerLearningRequest::create([
            'learner_id' => $creator->id,
            'custom_topic_id' => $topic->id,
            'title' => 'Learning request',
        ]);

        $this->actingAs($creator, 'sanctum')
            ->deleteJson("/api/v1/peer-learning/custom-topics/{$topic->id}")
            ->assertOk();

        $this->assertSame('Preserved topic', $request->fresh()->customTopic?->title);
    }

    private function makeUser(string $name): User
    {
        return User::create([
            'name' => $name,
            'email' => strtolower(str_replace(' ', '.', $name)).'@example.test',
            'password' => 'password',
            'role' => 'student',
            'status' => 'active',
        ]);
    }

    private function makeTopic(User $user, string $title): CustomTopic
    {
        return CustomTopic::create([
            'user_id' => $user->id,
            'title' => $title,
        ]);
    }
}
