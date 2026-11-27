<?php

namespace App\Http\Requests\PeerLearning;

use App\Models\PeerLearningRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use App\Repositories\Contracts\PeerLearning\PeerLearningRequestRepositoryInterface;
use Illuminate\Validation\Validator;

class StorePeerLearningRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', PeerLearningRequest::class) ?? false;
    }

    public function rules(): array
    {
        return [
            // A request must use exactly one topic source.
            'subject_offering_id' => [
                'nullable',
                'required_without:custom_topic_id',
                'prohibits:custom_topic_id',
                'integer',
                Rule::exists('subject_offerings', 'id'),
            ],

            'custom_topic_id' => [
                'nullable',
                'required_without:subject_offering_id',
                'prohibits:subject_offering_id',
                'integer',
                Rule::exists('custom_topics', 'id'),
            ],

            'title' => ['required', 'string', 'max:180'],
            'description' => ['required', 'string'],
            'learning_goal' => ['nullable', 'string'],
            'preferred_mode' => [
                'sometimes',
                Rule::in(['online', 'in_person', 'either']),
            ],
            'preferred_availability' => ['nullable', 'string'],
            'preferred_start_date' => [
                'nullable',
                'required_with:preferred_end_date,preferred_start_time,preferred_end_time',
                'date_format:Y-m-d',
                'after_or_equal:today',
            ],
            'preferred_end_date' => [
                'nullable',
                'date_format:Y-m-d',
                'after_or_equal:today',
                'after_or_equal:preferred_start_date',
            ],
            'preferred_start_time' => [
                'nullable',
                'required_with:preferred_start_date,preferred_end_date,preferred_end_time',
                'date_format:H:i',
            ],
            'preferred_end_time' => [
                'nullable',
                'required_with:preferred_start_date,preferred_end_date,preferred_start_time',
                'date_format:H:i',
                'after:preferred_start_time',
            ],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (
                $validator->errors()->has('preferred_start_date')
                || $validator->errors()->has('preferred_start_time')
                || $validator->errors()->has('preferred_end_time')
            ) {
                return;
            }

            $startDate = $this->input('preferred_start_date');
            $startTime = $this->input('preferred_start_time');
            $endTime = $this->input('preferred_end_time');

            if ($startDate && $startTime) {
                $now = Carbon::now();
                $startDateTime = Carbon::createFromFormat('Y-m-d H:i', "{$startDate} {$startTime}");

                if ($startDateTime->lt($now)) {
                    $validator->errors()->add(
                        'preferred_start_time',
                        'The selected start date and time has already passed. Choose current or future time.'
                    );
                }
            }

            if ($startTime && $endTime && $endTime <= $startTime) {
                $validator->errors()->add(
                    'preferred_end_time',
                    'The end time must be later than the start time.'
                );
            }

            if ($startTime && $endTime && $endTime > $startTime) {
                $durationMinutes = Carbon::parse($startTime)
                    ->diffInMinutes(Carbon::parse($endTime));

                if ($durationMinutes < 30) {
                    $validator->errors()->add(
                        'preferred_end_time',
                        'The time range must be at least 30 minutes.'
                    );
                }
            }
        });

        $startDate = $this->input('preferred_start_date');
            $endDate = $this->input('preferred_end_date');
            $startTime = $this->input('preferred_start_time');
            $endTime = $this->input('preferred_end_time');
            $userId = (int) ($this->user()?->getAuthIdentifier() ?? 0);

            if ($userId > 0 && $startDate && $startTime && $endTime) {
                /** @var PeerLearningRequestRepositoryInterface $repository */
                $repository = app(PeerLearningRequestRepositoryInterface::class);

                $hasConflict = $repository->hasConflictingScheduleForLearner(
                    $userId,
                    $startDate,
                    $endDate,
                    $startTime,
                    $endTime
                );

                if ($hasConflict) {
                    $validator->errors()->add(
                        'preferred_start_time',
                        'You already have a request scheduled during this time on this date. Please choose a different time.'
                    );
                }
            }
    }
}
