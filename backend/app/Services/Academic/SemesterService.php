<?php

namespace App\Services\Academic;

use App\Models\Semester;
use App\Repositories\Contracts\Academic\SemesterRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class SemesterService
{
    private const SEMESTER_SORT_ORDERS = [
        'first' => 1,
        'first semester' => 1,
        'second' => 2,
        'second semester' => 2,
    ];

    public function __construct(
        protected SemesterRepositoryInterface $semesterRepository
    ) {}

    /**
     * Get all semesters with pagination and filters.
     */
    public function getAll(array $filters = []): LengthAwarePaginator
    {
        return $this->semesterRepository->getAll($filters);
    }

    /**
     * Find semester by ID.
     */
    public function findById(int $id): ?Semester
    {
        return $this->semesterRepository->findById($id);
    }

    /**
     * Create a new semester.
     */
    public function create(array $data): Semester
    {
        $name = strtolower(trim($data['name']));

        if (!isset(self::SEMESTER_SORT_ORDERS[$name])) {
            throw ValidationException::withMessages([
                'name' => [
                    'Only First and Second semesters are allowed.',
                ],
            ]);
        }

        $data['name'] = ucfirst($name);
        $data['sort_order'] = self::SEMESTER_SORT_ORDERS[$name];

        return $this->semesterRepository->create($data);
    }

    /**
     * Update an existing semester.
     */
    public function update(Semester $semester, array $data): Semester
    {
        if (array_key_exists('name', $data)) {
            $name = strtolower(trim($data['name']));

            if (!isset(self::SEMESTER_SORT_ORDERS[$name])) {
                throw ValidationException::withMessages([
                    'name' => [
                        'Only First and Second semesters are allowed.',
                    ],
                ]);
            }

            $data['name'] = ucfirst($name);
            $data['sort_order'] = self::SEMESTER_SORT_ORDERS[$name];
        }

        // Never trust sort_order sent directly by the frontend.
        unset($data['sort_order']);

        // Reassign the automatically calculated sort order if name changed.
        if (isset($data['name'])) {
            $name = strtolower($data['name']);
            $data['sort_order'] = self::SEMESTER_SORT_ORDERS[$name];
        }

        return $this->semesterRepository->update($semester, $data);
    }

    /**
     * Delete a semester.
     */
    public function delete(Semester $semester): bool
    {
        return $this->semesterRepository->delete($semester);
    }
}
