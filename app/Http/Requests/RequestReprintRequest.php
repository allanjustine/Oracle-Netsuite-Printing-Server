<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RequestReprintRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'reason'      => ['required', 'max:5000', 'min:2'],
            'external_id' => ['required', Rule::exists('receipts', 'external_id')]
        ];
    }

    public function messages()
    {
        return [
            'external_id.exists'   => 'Receipt reference not found',
            'external_id.required' => 'Receipt reference is required',
            'external_id.max'      => 'Receipt reference must be less than 5000 characters',
            'external_id.min'      => 'Receipt reference must be at least 2 characters',
        ];
    }
}
