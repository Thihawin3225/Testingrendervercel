<?php

namespace App\Services\Academic;

use App\Models\Subject;
use App\Models\SubjectOffering;
use App\Repositories\Contracts\Academic\SubjectOfferingRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class SubjectOfferingService
{
    public function __construct(
        protected SubjectOfferingRepositoryInterface $subjectOfferingRepository
    ) {}

    /**
     * Get all subject offerings with pagination and filters.
     */
    public function getAll(array $filters = []): LengthAwarePaginator
    {
        return $this->subjectOfferingRepository->getAll($filters);
    }

    /**
     * Find subject offering by ID.
     */
    public function findById(int $id): ?SubjectOffering
    {
        return $this->subjectOfferingRepository->findById($id);
    }

    /**
     * Create a subject and its offering together.
     */
    public function createWithSubject(array $data): SubjectOffering
    {
        return DB::transaction(function () use ($data) {

            // Step 1: Create subject
            $subject = Subject::create($data['subject']);

            // Step 2: Prepare offering data
            $offeringData = $data['offering'];

            // Step 3: Attach newly created subject ID
            $offeringData['subject_id'] = $subject->id;

            // Step 4: Create subject offering
            return $this->subjectOfferingRepository->create(
                $offeringData
            );
        });
    }

    /**
     * Create a subject offering using an existing subject.
     */
    public function create(array $data): SubjectOffering
    {
        return $this->subjectOfferingRepository->create($data);
    }

    /**
     * Update an existing subject offering.
     */
    public function update(
        SubjectOffering $subjectOffering,
        array $data
    ): SubjectOffering {
        return $this->subjectOfferingRepository->update(
            $subjectOffering,
            $data
        );
    }

    /**
     * Delete a subject offering.
     */
    public function delete(SubjectOffering $subjectOffering): bool
    {
        return $this->subjectOfferingRepository->delete($subjectOffering);
    }
}
