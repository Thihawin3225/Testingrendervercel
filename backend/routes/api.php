
<?php

use Illuminate\Support\Facades\Route;

// ==================================================
// AUTH CONTROLLERS
// ==================================================
use App\Http\Controllers\Api\V1\Auth\LoginController;
use App\Http\Controllers\Api\V1\Auth\LogoutController;
use App\Http\Controllers\Api\V1\Auth\MeController;
use App\Http\Controllers\Api\V1\Auth\PasswordResetController;
use App\Http\Controllers\Api\V1\Auth\ForgotPasswordController;

// ==================================================
// ADMIN CONTROLLERS
// ==================================================
use App\Http\Controllers\Api\V1\Admin\UserController;
use App\Http\Controllers\Api\V1\Admin\Campus\CampusLocationController;

// ==================================================
// ACADEMIC CONTROLLERS
// ==================================================
use App\Http\Controllers\Api\V1\Academic\AcademicYearController;
use App\Http\Controllers\Api\V1\Academic\MajorController;
use App\Http\Controllers\Api\V1\Academic\SemesterController;
use App\Http\Controllers\Api\V1\Academic\StudentEnrollmentController;
use App\Http\Controllers\Api\V1\Academic\SubjectController;
use App\Http\Controllers\Api\V1\Academic\SubjectOfferingController;
use App\Http\Controllers\Api\V1\Academic\YearLevelController;

// ==================================================
// PROFILE CONTROLLERS
// ==================================================
use App\Http\Controllers\Api\V1\Profile\ProfileController;

// ==================================================
// STUDY MATERIAL CONTROLLERS
// ==================================================
use App\Http\Controllers\Api\V1\StudyMaterial\StudyMaterialController;

// ==================================================
// MARKETPLACE CONTROLLERS
// ==================================================
use App\Http\Controllers\Api\V1\Marketplace\MarketplaceCategoryController;
use App\Http\Controllers\Api\V1\Marketplace\MarketplaceListingController;
use App\Http\Controllers\Api\V1\Marketplace\ListingRequestController;
use App\Http\Controllers\Api\V1\Marketplace\SavedListingController;

// ==================================================
// PEER LEARNING CONTROLLERS
// ==================================================
use App\Http\Controllers\Api\V1\PeerLearning\CustomTopicController;
use App\Http\Controllers\Api\V1\PeerLearning\PeerLearningRequestController;


// ==================================================
// API V1
// ==================================================

Route::prefix('v1')->group(function () {

    // ==================================================
    // 1. AUTHENTICATION
    // ==================================================

    Route::prefix('auth')->group(function () {

        // Login
        Route::post('/login', LoginController::class)
            ->name('api.v1.auth.login');

        // Forgot Password
        Route::post('/forgot-password', [
            ForgotPasswordController::class,
            'sendOtp',
        ])->middleware('throttle:3,1')
            ->name('api.v1.auth.forgot-password');

        // Verify OTP
        Route::post('/verify-otp', [
            PasswordResetController::class,
            'verifyOtp',
        ]);

        // Reset Password
        Route::post('/reset-password', [
            PasswordResetController::class,
            'resetPassword',
        ])->middleware('throttle:5,1');

        // Authenticated Routes
        Route::middleware(['auth:sanctum', 'active'])->group(function () {

            Route::post('/logout', LogoutController::class);

            Route::get('/me', MeController::class);
        });
    });


    // ==================================================
    // 2. ADMIN MODULE
    // ==================================================

    Route::prefix('admin')
        ->middleware(['auth:sanctum', 'active', 'role:admin'])
        ->group(function () {

            // Get Students
            Route::get('/students', [
                UserController::class,
                'index',
            ]);

            // Create Student
            Route::post('/students', [
                UserController::class,
                'store',
            ]);

            // Create Student with Academic Enrollment
            Route::post('/students/with-enrollment', [
                UserController::class,
                'storeWithEnrollment',
            ]);

            // Update User
            Route::patch('/users/{user}', [
                UserController::class,
                'update',
            ]);

            // Delete User
            Route::delete('/users/{user}', [
                UserController::class,
                'destroy',
            ]);

            // Campus Location CRUD
            Route::apiResource(
                'campus/locations',
                CampusLocationController::class
            );

            // Deactivate Campus Location
            Route::patch('/campus/locations/{id}/deactivate', [
                CampusLocationController::class,
                'deactivate',
            ]);
        });


    // ==================================================
    // 3. ACADEMIC MODULE
    // ==================================================

    Route::prefix('academic')
        ->middleware(['auth:sanctum', 'active'])
        ->group(function () {

            // Academic Years - Latest Start Date
            Route::get(
                'academic-years/latest-start-date',
                [AcademicYearController::class, 'latestStartDate']
            );

            // Academic Years CRUD
            Route::apiResource(
                'academic-years',
                AcademicYearController::class
            );

            // Year Levels
            Route::apiResource(
                'year-levels',
                YearLevelController::class
            );

            // Majors
            Route::apiResource(
                'majors',
                MajorController::class
            );

            // Semesters
            Route::apiResource(
                'semesters',
                SemesterController::class
            );

            // Subjects
            Route::apiResource(
                'subjects',
                SubjectController::class
            );

            // Subject Offerings
            Route::apiResource(
                'subject-offerings',
                SubjectOfferingController::class
            );

            // Student Academic Enrollments
            Route::apiResource(
                'studentacademicenrollments',
                StudentEnrollmentController::class
            );
        });


    // ==================================================
    // 4. CAMPUS LOCATION MODULE
    // ==================================================

    Route::prefix('campus')
        ->middleware(['auth:sanctum', 'active'])
        ->group(function () {

            // Active Locations for Dropdowns
            Route::get('/locations/active', [
                CampusLocationController::class,
                'activeLocations',
            ]);
        });


    // ==================================================
    // 5. PROFILE MODULE
    // ==================================================

    Route::prefix('profile')
        ->middleware(['auth:sanctum', 'active'])
        ->group(function () {

            // Upload Avatar
            Route::post('/avatar', [
                ProfileController::class,
                'uploadAvatar',
            ])->name('api.v1.profile.avatar.upload');
        });


    // ==================================================
    // 6. STUDY MATERIAL MODULE
    // ==================================================

    Route::prefix('study-materials')
        ->middleware(['auth:sanctum'])
        ->group(function () {

            // Create Study Material
            Route::post('/', [
                StudyMaterialController::class,
                'store',
            ])->name('api.v1.study-materials.store');
        });


    // ==================================================
    // 7. MARKETPLACE MODULE
    // ==================================================

    Route::prefix('marketplace')
        ->middleware(['auth:sanctum', 'active'])
        ->group(function () {

            // Marketplace Categories
            Route::apiResource(
                'categories',
                MarketplaceCategoryController::class
            );

            // Seller's Own Listings
            Route::get('/my-listings', [
                MarketplaceListingController::class,
                'myListings',
            ]);

            // Marketplace Listings CRUD
            Route::apiResource(
                'listings',
                MarketplaceListingController::class
            );

            // Buyer's Own Requests
            Route::get('/my-requests', [
                ListingRequestController::class,
                'myRequests',
            ]);

            // Buyer Creates a Request
            Route::post('/listings/{listing}/requests', [
                ListingRequestController::class,
                'store',
            ]);

            // Seller Views Requests for a Listing
            Route::get('/listings/{listing}/requests', [
                ListingRequestController::class,
                'index',
            ]);

            // Buyer Confirms Updated Listing
            Route::patch('/requests/{listingRequest}/confirm', [
                ListingRequestController::class,
                'confirm',
            ]);

            // Seller Accepts and Schedules a Request
            Route::post('/requests/{listingRequest}/accept', [
                ListingRequestController::class,
                'accept',
            ]);

            // Seller Updates Request Status
            Route::patch('/requests/{listingRequest}/status', [
                ListingRequestController::class,
                'updateStatus',
            ]);

            // Buyer Cancels a Request
            Route::patch('/requests/{listingRequest}/cancel', [
                ListingRequestController::class,
                'cancel',
            ]);

            // Release Listing Schedule
            Route::patch('/listings/{listing}/release-schedule', [
                ListingRequestController::class,
                'releaseSchedule',
            ]);

            // Complete Listing
            Route::patch('/listings/{listing}/complete', [
                ListingRequestController::class,
                'complete',
            ]);

            // Saved Listings - Get
            Route::get('/saved-listings', [
                SavedListingController::class,
                'index',
            ])->name('api.v1.marketplace.saved-listings.index');

            // Save Listing
            Route::post('/listings/{listing}/save', [
                SavedListingController::class,
                'store',
            ])->name('api.v1.marketplace.listings.save');

            // Unsave Listing
            Route::delete('/listings/{listing}/save', [
                SavedListingController::class,
                'destroy',
            ])->name('api.v1.marketplace.listings.unsave');
        });


    // ==================================================
    // 8. PEER LEARNING MODULE
    // ==================================================

    Route::prefix('peer-learning')
        ->middleware(['auth:sanctum', 'active'])
        ->group(function () {

            // Peer Learning Feed
            Route::get('/feed', [
                PeerLearningRequestController::class,
                'feed',
            ]);

            // Peer Learning Requests
            Route::apiResource(
                'requests',
                PeerLearningRequestController::class
            )->parameters([
                'requests' => 'peerLearningRequest',
            ]);

            // Recent Custom Topics
            Route::get('/custom-topics/recent', [
                CustomTopicController::class,
                'recent',
            ]);

            // Create Custom Topic
            Route::post('/custom-topics', [
                CustomTopicController::class,
                'store',
            ]);

            // Delete Custom Topic
            Route::delete('/custom-topics/{customTopic}', [
                CustomTopicController::class,
                'destroy',
            ]);
        });
    Route::delete('custom-topics/{customTopic}', [
        CustomTopicController::class,
        'destroy',
    ]);
});

Route::prefix('v1/profile')
    ->middleware(['auth:sanctum', 'active'])
    ->group(function () {

        Route::put('/', [
            ProfileController::class,
            'updateBio',
        ])->name('api.v1.profile.bio.update');

        Route::post('/avatar', [
            ProfileController::class,
            'uploadAvatar',
        ])->name('api.v1.profile.avatar.upload');
    });

Route::prefix('v1/study-materials')
    ->middleware('auth:sanctum')
    ->group(function () {
        Route::post('/', [StudyMaterialController::class, 'store'])
            ->name('api.v1.study-materials.store');
        Route::patch('/{studyMaterial}', [StudyMaterialController::class, 'update'])
            ->name('api.v1.study-materials.update');
        Route::delete('/{studyMaterial}', [StudyMaterialController::class, 'destroy'])
            ->name('api.v1.study-materials.destroy');
        Route::get('/', [StudyMaterialController::class, 'index'])
            ->name('api.v1.study-materials.index');
        Route::get('/mine', [StudyMaterialController::class, 'mine'])
            ->name('api.v1.study-materials.mine');
        Route::get('/bookmarks', [StudyMaterialController::class, 'bookmarks'])
            ->name('api.v1.study-materials.bookmarks');
        Route::get('/{studyMaterial}/download', [StudyMaterialController::class, 'download'])
            ->name('api.v1.study-materials.download');
        Route::get('/{studyMaterial}/preview-url', [StudyMaterialController::class, 'previewUrl'])
            ->name('api.v1.study-materials.preview-url');
        Route::post('/{studyMaterial}/bookmark', [StudyMaterialController::class, 'bookmark'])
            ->name('api.v1.study-materials.bookmark');
        Route::delete('/{studyMaterial}/bookmark', [StudyMaterialController::class, 'removeBookmark'])
            ->name('api.v1.study-materials.bookmark.remove');
    });

Route::prefix('v1/academic')
    ->middleware(['auth:sanctum', 'active'])
    ->group(function () {
        Route::apiResource(
            'year-levels',
            YearLevelController::class
        );
    });

Route::prefix('v1/academic')
    ->middleware(['auth:sanctum', 'active'])
    ->group(function () {
        Route::apiResource(
            'majors',
            MajorController::class
        );
    });

Route::prefix('v1/academic')
    ->middleware(['auth:sanctum', 'active'])
    ->group(function () {
        Route::apiResource(
            'semesters',
            SemesterController::class
        );
    });

Route::prefix('v1/academic')
    ->middleware(['auth:sanctum', 'active'])
    ->group(function () {
        Route::apiResource(
            'subjects',
            SubjectController::class
        );
    });

Route::prefix('v1/academic')
    ->middleware(['auth:sanctum', 'active'])
    ->group(function () {
        Route::apiResource(
            'subject-offerings',
            SubjectOfferingController::class
        );
    });

Route::prefix('v1/academic')
    ->middleware('auth:sanctum')
    ->group(function () {
        Route::apiResource(
            'studentacademicenrollments',
            StudentEnrollmentController::class
        );
    });

Route::prefix('v1')->middleware('auth:sanctum')->group(function () {

    // Student and Admin: Active location dropdown
    Route::get(
        '/campus/locations/active',
        [CampusLocationController::class, 'activeLocations']
    );

    // Admin: Campus location CRUD
    Route::prefix('admin')->group(function () {

        Route::apiResource(
            'campus/locations',
            CampusLocationController::class
        );
        Route::patch(
            '/campus/locations/{id}/deactivate',
            [CampusLocationController::class, 'deactivate']
        );
    });
});
Route::prefix('v1/marketplace')
    ->middleware('auth:sanctum')
    ->group(function () {
        Route::apiResource(
            'categories',
            MarketplaceCategoryController::class
        );

        Route::apiResource(
            'listings',
            MarketplaceListingController::class
        );
    });
Route::prefix('v1/auth')->group(function () {

    Route::post(
        '/forgot-password',
        [ForgotPasswordController::class, 'sendOtp']
    )->name('api.v1.auth.forgot-password');
});
