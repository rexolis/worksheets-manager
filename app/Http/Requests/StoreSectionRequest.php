<?php

namespace App\Http\Requests;

use App\Models\Section;
use App\Models\WorksheetClass;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreSectionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', Section::class) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'section_type' => ['required', 'string'],
            'worksheet_class_id' => ['required', 'integer', 'exists:worksheet_classes,id'],
            'class_code' => [
                'required',
                'string',
                'max:255',
                'unique:sections,class_code',
                'regex:/^\d{6}-[A-Z0-9]+(?:-[A-Z0-9]+)*-[A-Z]$/',
            ],
            'date_start' => ['required', 'date'],
            'date_end' => ['required', 'date', 'after_or_equal:date_start'],
            'teacher_ids' => ['nullable', 'array'],
            'teacher_ids.*' => [
                'integer',
                'distinct',
                Rule::exists('users', 'id')->where(function ($query): void {
                    $query->whereIn('id', function ($subquery): void {
                        $subquery->select('user_roles.user_id')
                            ->from('user_roles')
                            ->join('roles', 'roles.id', '=', 'user_roles.role_id')
                            ->where('roles.slug', 'teacher');
                    });
                }),
            ],
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

                $worksheetClass = WorksheetClass::query()->find(
                    $this->integer('worksheet_class_id'),
                );

                if ($worksheetClass === null) {
                    return;
                }

                $expectedPrefix = sprintf(
                    '%s-%s-',
                    $this->date('date_start')->format('Ym'),
                    strtoupper($worksheetClass->slug),
                );

                if (! str_starts_with((string) $this->input('class_code'), $expectedPrefix)) {
                    $validator->errors()->add(
                        'class_code',
                        "The class code must use the format {$expectedPrefix}A.",
                    );
                }
            },
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'class_code.regex' => 'The class code must match the format YYYYMM-CLASS-A.',
        ];
    }
}
