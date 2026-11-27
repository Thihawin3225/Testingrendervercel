<?php

namespace App\Services\Academic;

use App\Models\YearLevel;
use App\Repositories\Contracts\Academic\YearLevelRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class YearLevelService
{
    /**
     * Standard year names and their fixed sort orders.
     */
    private const STANDARD_YEAR_ORDERS = [
        'first year' => 1,
        'second year' => 2,
        'third year' => 3,
        'fourth year' => 4,
        'fifth year' => 5,
        'sixth year' => 6,
    ];

    public function __construct(
        protected YearLevelRepositoryInterface $yearLevelRepository
    ) {}

    /**
     * Get all year levels with pagination and filters.
     */
    public function getAll(array $filters = []): LengthAwarePaginator
    {
        return $this->yearLevelRepository->getAll($filters);
    }

    /**
     * Find year level by ID.
     */
    public function findById(int $id): ?YearLevel
    {
        return $this->yearLevelRepository->findById($id);
    }

    /**
     * Create a new year level.
     */
    public function create(array $data): YearLevel
    {
        $name = trim($data['name']);
        $normalizedName = mb_strtolower($name);

        // Assign fixed sort order for standard year names.
        if (isset(self::STANDARD_YEAR_ORDERS[$normalizedName])) {
            $data['name'] = $name;
            $data['sort_order'] =
                self::STANDARD_YEAR_ORDERS[$normalizedName];

            unset($data['final_year_confirmed']);

            return $this->yearLevelRepository->create($data);
        }

        // Custom names must contain the word "Final".
        if (!preg_match('/\bfinal\b/i', $name)) {
            throw ValidationException::withMessages([
                'name' => [
                    'Only standard year names or a custom name containing "Final" are allowed.',
                ],
            ]);
        }

        // Only one Final Year is allowed.
        if ($this->yearLevelRepository->finalYearExists()) {
            throw ValidationException::withMessages([
                'name' => [
                    'A Final Year already exists.',
                ],
            ]);
        }

        // Calculate the next sort order on the backend.
        $data['name'] = $name;
        $data['sort_order'] =
            $this->yearLevelRepository->getNextSortOrder();

        unset($data['final_year_confirmed']);

        return $this->yearLevelRepository->create($data);
    }

    /**
     * Update an existing year level.
     */
    public function update(
        YearLevel $yearLevel,
        array $data
    ): YearLevel {
        if (!array_key_exists('name', $data)) {
            return $yearLevel;
        }

        $name = trim($data['name']);
        $normalizedName = mb_strtolower($name);

        // Standard year names use their fixed sort orders.
        if (isset(self::STANDARD_YEAR_ORDERS[$normalizedName])) {
            $data['name'] = $name;
            $data['sort_order'] =
                self::STANDARD_YEAR_ORDERS[$normalizedName];

            unset($data['final_year_confirmed']);

            return $this->yearLevelRepository->update(
                $yearLevel,
                $data
            );
        }

        // Custom names must contain the word "Final".
        if (!preg_match('/\bfinal\b/i', $name)) {
            throw ValidationException::withMessages([
                'name' => [
                    'Only standard year names or a custom name containing "Final" are allowed.',
                ],
            ]);
        }

        $wasAlreadyFinal = preg_match(
            '/\bfinal\b/i',
            $yearLevel->name
        );

        // Do not allow another Final Year record.
        if (
            !$wasAlreadyFinal &&
            $this->yearLevelRepository->finalYearExists($yearLevel->id)
        ) {
            throw ValidationException::withMessages([
                'name' => [
                    'A Final Year already exists.',
                ],
            ]);
        }

        $data['name'] = $name;

        // Keep the existing order when editing an existing Final Year.
        // Otherwise, assign the next available order.
        $data['sort_order'] = $wasAlreadyFinal
            ? $yearLevel->sort_order
            : $this->yearLevelRepository->getNextSortOrder();

        unset($data['final_year_confirmed']);

        return $this->yearLevelRepository->update(
            $yearLevel,
            $data
        );
    }

    /**
     * Delete a year level.
     */
    public function delete(YearLevel $yearLevel): bool
    {
        return $this->yearLevelRepository->delete($yearLevel);
    }
}
