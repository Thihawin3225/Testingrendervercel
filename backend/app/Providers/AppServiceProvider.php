<?php

namespace App\Providers;

// Models
use App\Models\AcademicYear;
use App\Models\CampusLocation;
use App\Models\CustomTopic;
use App\Models\Major;
use App\Models\MarketplaceCategory;
use App\Models\MarketplaceListing;
use App\Models\PeerLearningRequest;
use App\Models\Semester;
use App\Models\StudentAcademicEnrollment;
use App\Models\StudyMaterial;
use App\Models\Subject;
use App\Models\SubjectOffering;
use App\Models\YearLevel;

// Policies
use App\Policies\Academic\AcademicYearPolicy;
use App\Policies\Academic\MajorPolicy;
use App\Policies\Academic\SemesterPolicy;
use App\Policies\Academic\StudentAcademicEnrollmentPolicy;
use App\Policies\Academic\SubjectOfferingPolicy;
use App\Policies\Academic\SubjectPolicy;
use App\Policies\Academic\YearLevelPolicy;
use App\Policies\CampusLocationPolicy;
use App\Policies\MarketplaceCategoryPolicy;
use App\Policies\MarketplaceListingPolicy;
use App\Policies\PeerLearning\CustomTopicPolicy;
use App\Policies\PeerLearning\PeerLearningRequestPolicy;
use App\Policies\StudyMaterial\StudyMaterialPolicy;

// Repository Interfaces
use App\Repositories\Contracts\Academic\AcademicYearRepositoryInterface;
use App\Repositories\Contracts\Academic\MajorRepositoryInterface;
use App\Repositories\Contracts\Academic\SemesterRepositoryInterface;
use App\Repositories\Contracts\Academic\StudentAcademicEnrollmentRepositoryInterface;
use App\Repositories\Contracts\Academic\SubjectOfferingRepositoryInterface;
use App\Repositories\Contracts\Academic\SubjectRepositoryInterface;
use App\Repositories\Contracts\Academic\YearLevelRepositoryInterface;
use App\Repositories\Contracts\Campus\CampusLocationRepositoryInterface;
use App\Repositories\Contracts\Marketplace\ListingRequestRepositoryInterface;
use App\Repositories\Contracts\Marketplace\MarketplaceCategoryRepositoryInterface;
use App\Repositories\Contracts\Marketplace\MarketplaceListingRepositoryInterface;
use App\Repositories\Contracts\PeerLearning\CustomTopicRepositoryInterface;
use App\Repositories\Contracts\PeerLearning\PeerLearningRequestRepositoryInterface;
use App\Repositories\Contracts\StudyMaterial\StudyMaterialRepositoryInterface;

// Eloquent Repositories
use App\Repositories\Eloquent\Academic\AcademicYearRepository;
use App\Repositories\Eloquent\Academic\MajorRepository;
use App\Repositories\Eloquent\Academic\SemesterRepository;
use App\Repositories\Eloquent\Academic\StudentAcademicEnrollmentRepository;
use App\Repositories\Eloquent\Academic\SubjectOfferingRepository;
use App\Repositories\Eloquent\Academic\SubjectRepository;
use App\Repositories\Eloquent\Academic\YearLevelRepository;
use App\Repositories\Eloquent\Campus\EloquentCampusLocationRepository;
use App\Repositories\Eloquent\Marketplace\EloquentMarketplaceCategoryRepository;
use App\Repositories\Eloquent\Marketplace\EloquentMarketplaceListingRepository;
use App\Repositories\Eloquent\Marketplace\ListingRequestRepository;
use App\Repositories\Eloquent\PeerLearning\CustomTopicRepository;
use App\Repositories\Eloquent\PeerLearning\PeerLearningRequestRepository;
use App\Repositories\Eloquent\StudyMaterial\StudyMaterialRepository;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register application services.
     */
    public function register(): void
    {
        // Academic Repositories
        $this->app->bind(
            AcademicYearRepositoryInterface::class,
            AcademicYearRepository::class
        );

        $this->app->bind(
            YearLevelRepositoryInterface::class,
            YearLevelRepository::class
        );

        $this->app->bind(
            MajorRepositoryInterface::class,
            MajorRepository::class
        );

        $this->app->bind(
            SemesterRepositoryInterface::class,
            SemesterRepository::class
        );

        $this->app->bind(
            SubjectRepositoryInterface::class,
            SubjectRepository::class
        );

        $this->app->bind(
            SubjectOfferingRepositoryInterface::class,
            SubjectOfferingRepository::class
        );

        $this->app->bind(
            StudentAcademicEnrollmentRepositoryInterface::class,
            StudentAcademicEnrollmentRepository::class
        );

        // Marketplace Repositories
        $this->app->bind(
            MarketplaceListingRepositoryInterface::class,
            EloquentMarketplaceListingRepository::class
        );

        $this->app->bind(
            MarketplaceCategoryRepositoryInterface::class,
            EloquentMarketplaceCategoryRepository::class
        );

        $this->app->bind(
            ListingRequestRepositoryInterface::class,
            ListingRequestRepository::class
        );

        // Campus Location Repository
        $this->app->bind(
            CampusLocationRepositoryInterface::class,
            EloquentCampusLocationRepository::class
        );

        // Peer Learning Repositories
        $this->app->bind(
            PeerLearningRequestRepositoryInterface::class,
            PeerLearningRequestRepository::class
        );

        $this->app->bind(
            CustomTopicRepositoryInterface::class,
            CustomTopicRepository::class
        );

        // Study Material Repository
        $this->app->bind(
            StudyMaterialRepositoryInterface::class,
            StudyMaterialRepository::class
        );
    }

    /**
     * Register application policies.
     */
    public function boot(): void
    {
        // Academic Policies
        Gate::policy(
            AcademicYear::class,
            AcademicYearPolicy::class
        );

        Gate::policy(
            YearLevel::class,
            YearLevelPolicy::class
        );

        Gate::policy(
            Major::class,
            MajorPolicy::class
        );

        Gate::policy(
            Semester::class,
            SemesterPolicy::class
        );

        Gate::policy(
            Subject::class,
            SubjectPolicy::class
        );

        Gate::policy(
            SubjectOffering::class,
            SubjectOfferingPolicy::class
        );

        Gate::policy(
            StudentAcademicEnrollment::class,
            StudentAcademicEnrollmentPolicy::class
        );

        // Marketplace Policies
        Gate::policy(
            MarketplaceCategory::class,
            MarketplaceCategoryPolicy::class
        );

        Gate::policy(
            MarketplaceListing::class,
            MarketplaceListingPolicy::class
        );

        // Campus Location Policy
        Gate::policy(
            CampusLocation::class,
            CampusLocationPolicy::class
        );

        // Peer Learning Policies
        Gate::policy(
            CustomTopic::class,
            CustomTopicPolicy::class
        );

        Gate::policy(
            PeerLearningRequest::class,
            PeerLearningRequestPolicy::class
        );

        // Study Material Policy
        Gate::policy(
            StudyMaterial::class,
            StudyMaterialPolicy::class
        );
    }
}
