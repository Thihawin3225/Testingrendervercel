<?php

namespace App\Http\Requests\PeerLearning;

use App\Models\PeerLearningRequest as PeerLearningRequestModel;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdatePeerLearningRequest extends FormRequest
{
    public function authorize(): bool
    {
        $request = $this->currentRequest();
        $user = $this->user();

        return $request !== null
            && $user !== null
            && $user->can('update', $request);
    }

    public function rules(): array
    {
        return [
            'subject_offering_id' => [
                'sometimes',
                'nullable',
                'integer',
                Rule::exists('subject_offerings', 'id'),
            ],

            'custom_topic_id' => [
                'sometimes',
                'nullable',
                'integer',
                Rule::exists('custom_topics', 'id'),
            ],

            'title' => ['sometimes', 'required', 'string', 'max:180'],
            'description' => ['sometimes', 'nullable', 'string'],
            'learning_goal' => ['sometimes', 'nullable', 'string'],
            'preferred_mode' => [
                'sometimes',
                Rule::in(['online', 'in_person', 'either']),
            ],
            'preferred_availability' => ['sometimes', 'nullable', 'string'],
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

    /**
     * Dates are covered by 'after_or_equal:today', but a date of today paired
     * with a time earlier than right now must also be blocked, and the end
     * time must come after the start time.
     */
    private function validateScheduleNotInThePast(Validator $validator): void
    {
        if (
            $validator->errors()->has('preferred_start_date')
            || $validator->errors()->has('preferred_start_time')
            || $validator->errors()->has('preferred_end_time')
        ) {
            return;
        }

        $startDate = $this->input('preferred_start_date',
            $this->currentRequest()?->preferred_start_date
        );
        $startTime = $this->input('preferred_start_time',
            $this->currentRequest()?->preferred_start_time
        );
        $endTime = $this->input('preferred_end_time',
            $this->currentRequest()?->preferred_end_time
        );

        if (! $startDate || ! $startTime) {
            return;
        }

        $startDateTime = Carbon::createFromFormat(
            'Y-m-d H:i',
            "{$startDate} {$startTime}"
        );

        if ($startDateTime !== false && $startDateTime->lt(Carbon::now())) {
            $validator->errors()->add(
                'preferred_start_time',
                'The selected start date and time has already passed. Choose current or future time.'
            );
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
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $this->validateScheduleNotInThePast($validator);

            if (
                $validator->errors()->has('subject_offering_id')
                || $validator->errors()->has('custom_topic_id')
            ) {
                return;
            }

            $request = $this->currentRequest();

            if ($request === null) {
                return;
            }

            $offeringId = $this->input(
                'subject_offering_id',
                $request->subject_offering_id
            );

            $customTopicId = $this->input(
                'custom_topic_id',
                $request->custom_topic_id
            );

            // Exactly one source must remain after applying this PATCH.
            if (($offeringId === null) === ($customTopicId === null)) {
                $validator->errors()->add(
                    'subject_offering_id',
                    'The request must use exactly one subject offering or custom topic.'
                );
            }
        });

        
    }

    private function currentRequest(): ?PeerLearningRequestModel
    {
        $routeValue = $this->route('peerLearningRequest');

        if ($routeValue instanceof PeerLearningRequestModel) {
            return $routeValue;
        }

        if (is_numeric($routeValue)) {
            return PeerLearningRequestModel::find((int) $routeValue);
        }

        return null;
    }

    
}
