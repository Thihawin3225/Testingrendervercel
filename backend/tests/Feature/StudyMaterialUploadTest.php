<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Storage\CloudinaryService;
use App\Services\StudyMaterial\StudyMaterialService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Mockery\MockInterface;
use Tests\TestCase;

class StudyMaterialUploadTest extends TestCase
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
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('study_materials', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('subject_offering_id')->nullable();
            $table->string('title', 180);
            $table->text('description')->nullable();
            $table->string('original_filename', 255);
            $table->string('cloudinary_public_id', 255);
            $table->string('cloudinary_asset_id', 64);
            $table->string('cloudinary_resource_type', 20);
            $table->string('cloudinary_delivery_type', 20);
            $table->string('file_type', 10);
            $table->string('mime_type', 127);
            $table->unsignedBigInteger('file_size');
            $table->string('file_hash', 64)->nullable();
            $table->string('status', 20)->default('pending');
            $table->timestamps();
        });

        DB::table('users')->insert([
            'id' => 1,
            'name' => 'Uploader',
            'email' => 'uploader@example.com',
            'password' => 'secret',
        ]);
    }

    public function test_duplicate_file_is_rejected_before_uploading_to_cloudinary(): void
    {
        $contents = 'duplicate study material contents';

        DB::table('study_materials')->insert([
            'user_id' => 1,
            'title' => 'Existing material',
            'original_filename' => 'existing.pdf',
            'cloudinary_public_id' => 'campus_bridge/study_materials/existing.pdf',
            'cloudinary_asset_id' => 'existing-asset-id',
            'cloudinary_resource_type' => 'raw',
            'cloudinary_delivery_type' => 'authenticated',
            'file_type' => 'pdf',
            'mime_type' => 'application/pdf',
            'file_size' => strlen($contents),
            'file_hash' => hash('sha256', $contents),
            'status' => 'active',
        ]);

        $this->mock(CloudinaryService::class, function (MockInterface $mock): void {
            $mock->shouldNotReceive('uploadRawFile');
        });

        $file = UploadedFile::fake()->createWithContent('duplicate.pdf', $contents);

        try {
            app(StudyMaterialService::class)->upload(
                User::query()->findOrFail(1),
                [
                    'subject_offering_id' => 1,
                    'title' => 'Duplicate material',
                    'file' => $file,
                ]
            );

            $this->fail('Expected a validation exception for a duplicate file.');
        } catch (ValidationException $exception) {
            $this->assertSame(
                'This study material has already been uploaded.',
                $exception->errors()['file'][0]
            );
        }
    }

    public function test_new_official_material_is_uploaded_with_a_file_hash(): void
    {
        $contents = 'new study material contents';

        $this->mock(CloudinaryService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('uploadRawFile')
                ->once()
                ->andReturn([
                    'public_id' => 'campus_bridge/study_materials/new.pdf',
                    'asset_id' => 'new-asset-id',
                    'resource_type' => 'raw',
                    'delivery_type' => 'authenticated',
                ]);
        });

        $file = UploadedFile::fake()->createWithContent('new.pdf', $contents);

        $material = app(StudyMaterialService::class)->upload(
            User::query()->findOrFail(1),
            [
                'subject_offering_id' => 4,
                'title' => 'New official material',
                'file' => $file,
            ]
        );

        $this->assertSame(4, $material->subject_offering_id);
        $this->assertSame(hash('sha256', $contents), $material->file_hash);
        $this->assertSame('active', $material->status);
    }
}
