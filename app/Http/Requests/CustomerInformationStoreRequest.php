<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CustomerInformationStoreRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'phone_number' => 'required|string|min:10|max:20',
            "gender" => "required|in:male,female",
            'duration' => 'required',
            'start_date' => 'required',
        ];
    }

    public function messages(): array
    {
        return [
            'gender.required' => 'Please Select Your Gender.',
            'gender.in' => 'The Selected Gender is Invalid.',
        ];
    }
}