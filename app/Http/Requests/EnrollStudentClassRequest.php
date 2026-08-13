<?php

namespace App\Http\Requests;

use App\Enums\SectionStatusId;
use App\Enums\StudentClassStatus;
use App\Models\Section;
use App\Models\StudentClass;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class EnrollStudentClassRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', StudentClass::class) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'class_code' => ['required', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<int, \Closure(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $section = Section::query()
                    ->where('class_code', $this->string('class_code')->toString())
                    ->first();

                if ($section === null || $section->status !== SectionStatusId::Active) {
                    $validator->errors()->add(
                        'class_code',
                        'No active section was found for this class code.',
                    );

                    return;
                }

                $existing = StudentClass::query()
                    ->where('user_id', $this->user()->id)
                    ->where('section_id', $section->id)
                    ->first();

                if ($existing !== null && $existing->status !== StudentClassStatus::Denied) {
                    $validator->errors()->add(
                        'class_code',
                        'You already have an enrollment for this class.',
                    );
                }
            },
        ];
    }
}
