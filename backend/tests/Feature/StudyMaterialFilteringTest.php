<?php

namespace Tests\Feature;

use App\Repositories\Eloquent\StudyMaterial\StudyMaterialRepository;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class StudyMaterialFilteringTest extends TestCase
{
    private StudyMaterialRepository $repository;

    private int $firstMaterialId;

    private int $secondMaterialId;

    private int $customMaterialId;

    private int $archivedMaterialId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createSchema();
        $this->seedRecords();

        $this->repository = app(StudyMaterialRepository::class);
    }

    public function test_materials_report_whether_the_viewer_bookmarked_them(): void
    {
        $bookmarkedForViewer = $this->repository
            ->paginateActive([], null, 2)
            ->getCollection()
            ->keyBy('id');

        $this->assertTrue((bool) $bookmarkedForViewer[$this->firstMaterialId]->is_bookmarked);

        $notBookmarkedForViewer = $this->repository
            ->paginateActive([], null, 1)
            ->getCollection()
            ->keyBy('id');

        $this->assertFalse((bool) $notBookmarkedForViewer[$this->firstMaterialId]->is_bookmarked);

        $savedMaterial = $this->repository
            ->paginateBookmarkedByUser(2, [])
            ->getCollection()
            ->first();

        $this->assertTrue((bool) $savedMaterial->is_bookmarked);
    }

    public function test_active_materials_can_be_filtered_by_type_year_and_level(): void
    {
        $this->assertSame(
            [$this->secondMaterialId, $this->firstMaterialId],
            $this->repository->paginateActive(['material_type' => 'official'])->pluck('id')->all()
        );

        $this->assertSame(
            [$this->customMaterialId],
            $this->repository->paginateActive(['material_type' => 'custom'])->pluck('id')->all()
        );

        $this->assertSame(
            [$this->firstMaterialId],
            $this->repository->paginateActive(['academic_year_id' => 1])->pluck('id')->all()
        );

        $this->assertSame(
            [$this->secondMaterialId],
            $this->repository->paginateActive(['year_level_id' => 2])->pluck('id')->all()
        );

        $this->assertSame(
            [$this->firstMaterialId],
            $this->repository->paginateActive([
                'material_type' => 'official',
                'academic_year_id' => 1,
                'year_level_id' => 1,
            ])->pluck('id')->all()
        );
    }

    public function test_search_matches_titles_filenames_and_subject_names(): void
    {
        $this->assertSame(
            [$this->firstMaterialId],
            $this->repository->paginateActive(['search' => 'Data Structures'])->pluck('id')->all()
        );

        $this->assertSame(
            [$this->customMaterialId],
            $this->repository->paginateActive(['search' => 'exam'])->pluck('id')->all()
        );

        $this->assertSame(
            [$this->secondMaterialId],
            $this->repository->paginateActive(['search' => 'week-2'])->pluck('id')->all()
        );
    }

    public function test_browse_hides_archived_materials_but_uploads_include_them(): void
    {
        $this->assertNotContains(
            $this->archivedMaterialId,
            $this->repository->paginateActive([])->pluck('id')->all()
        );

        $this->assertContains(
            $this->archivedMaterialId,
            $this->repository->paginateForUser(1, [])->pluck('id')->all()
        );
    }

    public function test_bookmarked_materials_can_be_filtered(): void
    {
        $this->assertSame(
            [$this->customMaterialId, $this->secondMaterialId, $this->firstMaterialId],
            $this->repository->paginateBookmarkedByUser(2, [])->pluck('id')->all()
        );

        $this->assertSame(
            [$this->secondMaterialId, $this->firstMaterialId],
            $this->repository->paginateBookmarkedByUser(2, [
                'material_type' => 'official',
            ])->pluck('id')->all()
        );

        $this->assertSame(
            [$this->customMaterialId],
            $this->repository->paginateBookmarkedByUser(2, ['search' => 'exam'])->pluck('id')->all()
        );

        $this->assertSame(
            [$this->secondMaterialId],
            $this->repository->paginateBookmarkedByUser(2, [
                'year_level_id' => 2,
            ])->pluck('id')->all()
        );

        $this->assertNotContains(
            $this->archivedMaterialId,
            $this->repository->paginateBookmarkedByUser(2, [])->pluck('id')->all()
        );
    }

    private function createSchema(): void
    {
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 120);
            $table->string('email')->unique();
            $table->string('password');
            $table->string('role')->default('student');
            $table->string('status')->default('active');
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('academic_years', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 20);
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });

        Schema::create('year_levels', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 30);
            $table->unsignedTinyInteger('sort_order');
            $table->timestamps();
        });

        Schema::create('majors', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 10);
            $table->string('name', 100);
            $table->timestamps();
        });

        Schema::create('semesters', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 30);
            $table->unsignedTinyInteger('sort_order');
            $table->timestamps();
        });

        Schema::create('subjects', function (Blueprint $table): void {
            $table->id();
            $table->string('subject_code', 30)->unique();
            $table->string('subject_name', 150);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('subject_offerings', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('academic_year_id');
            $table->unsignedBigInteger('year_level_id');
            $table->unsignedBigInteger('major_id');
            $table->unsignedBigInteger('semester_id');
            $table->unsignedBigInteger('subject_id');
            $table->timestamps();
        });

        Schema::create('study_materials', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('subject_offering_id')->nullable();
            $table->string('title', 180);
            $table->text('description')->nullable();
            $table->string('original_filename', 255);
            $table->string('status')->default('pending');
            $table->timestamps();
        });

        Schema::create('material_bookmarks', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('study_material_id');
            $table->timestamps();
        });
    }

    private function seedRecords(): void
    {
        $now = now();

        DB::table('users')->insert([
            [
                'id' => 1,
                'name' => 'Uploader',
                'email' => 'uploader@example.com',
                'password' => 'secret',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'name' => 'Bookmarker',
                'email' => 'bookmarker@example.com',
                'password' => 'secret',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        DB::table('academic_years')->insert([
            ['id' => 1, 'name' => '2025/2026', 'start_date' => '2025-09-01', 'end_date' => '2026-06-30'],
            ['id' => 2, 'name' => '2026/2027', 'start_date' => '2026-09-01', 'end_date' => '2027-06-30'],
        ]);

        DB::table('year_levels')->insert([
            ['id' => 1, 'name' => 'Year 1', 'sort_order' => 1],
            ['id' => 2, 'name' => 'Year 2', 'sort_order' => 2],
        ]);

        DB::table('majors')->insert(['id' => 1, 'code' => 'CS', 'name' => 'Computer Science']);
        DB::table('semesters')->insert(['id' => 1, 'name' => 'Semester 1', 'sort_order' => 1]);

        DB::table('subjects')->insert([
            ['id' => 1, 'subject_code' => 'CS101', 'subject_name' => 'Data Structures'],
            ['id' => 2, 'subject_code' => 'CS201', 'subject_name' => 'Networks'],
        ]);

        DB::table('subject_offerings')->insert([
            [
                'id' => 1,
                'academic_year_id' => 1,
                'year_level_id' => 1,
                'major_id' => 1,
                'semester_id' => 1,
                'subject_id' => 1,
            ],
            [
                'id' => 2,
                'academic_year_id' => 2,
                'year_level_id' => 2,
                'major_id' => 1,
                'semester_id' => 1,
                'subject_id' => 2,
            ],
        ]);

        $this->firstMaterialId = DB::table('study_materials')->insertGetId([
            'user_id' => 1,
            'subject_offering_id' => 1,
            'title' => 'Week 1 Slides',
            'original_filename' => 'week-1-notes.pdf',
            'status' => 'active',
        ]);

        $this->secondMaterialId = DB::table('study_materials')->insertGetId([
            'user_id' => 1,
            'subject_offering_id' => 2,
            'title' => 'Networks Slides',
            'original_filename' => 'week-2-networks.pdf',
            'status' => 'active',
        ]);

        $this->customMaterialId = DB::table('study_materials')->insertGetId([
            'user_id' => 1,
            'subject_offering_id' => null,
            'title' => 'Exam Tips',
            'original_filename' => 'exam-tips.docx',
            'status' => 'active',
        ]);

        $this->archivedMaterialId = DB::table('study_materials')->insertGetId([
            'user_id' => 1,
            'subject_offering_id' => 1,
            'title' => 'Archived Notes',
            'original_filename' => 'archived-notes.pdf',
            'status' => 'archived',
        ]);

        DB::table('material_bookmarks')->insert([
            [
                'user_id' => 2,
                'study_material_id' => $this->archivedMaterialId,
                'created_at' => $now->copy()->subMinutes(4),
                'updated_at' => $now->copy()->subMinutes(4),
            ],
            [
                'user_id' => 2,
                'study_material_id' => $this->firstMaterialId,
                'created_at' => $now->copy()->subMinutes(3),
                'updated_at' => $now->copy()->subMinutes(3),
            ],
            [
                'user_id' => 2,
                'study_material_id' => $this->secondMaterialId,
                'created_at' => $now->copy()->subMinutes(2),
                'updated_at' => $now->copy()->subMinutes(2),
            ],
            [
                'user_id' => 2,
                'study_material_id' => $this->customMaterialId,
                'created_at' => $now->copy()->subMinutes(1),
                'updated_at' => $now->copy()->subMinutes(1),
            ],
        ]);
    }
}
