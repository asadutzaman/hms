<?php

namespace App\Validators;

class DailyReviewValidator extends BaseValidator
{
    protected $request;

    public function __construct()
    {
        $this->request = request();
    }

    public function rules()
    {
        $common = [
            'ipd_admission_id' => ['nullable', 'integer'],
            'author_user_id'   => ['nullable', 'integer', 'exists:users,id'],
            'review_date'      => ['nullable', 'date'],
            'progress_note'    => ['nullable', 'string'],
            'assessment'       => ['nullable', 'string'],
            'plan'             => ['nullable', 'string'],
            'obs_snapshot'     => ['nullable', 'array'],
            'status'           => ['nullable', 'integer'],
        ];

        switch ($this->request->method()) {
            case 'POST':
                $common['ipd_admission_id'] = ['required', 'integer'];
                return $common;
            case 'PUT':
            case 'PATCH':
                return $common;
            default:
                return [];
        }
    }

    public function messages()
    {
        return array_merge(parent::messages(), [
            'ipd_admission_id.required' => 'Admission is required.',
        ]);
    }

    public function attributes()
    {
        return parent::attributes();
    }
}
