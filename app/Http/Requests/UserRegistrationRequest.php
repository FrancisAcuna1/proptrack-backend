<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UserRegistrationRequest extends FormRequest
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
            'firstname' => 'required|string|max:25',
            'middlename' => 'nullable|string|max:16',
            'lastname' => 'required|string|max:16',
            'contact' => ['required', 'regex:/^(09|\+639)\d{9}$/'],
            // 'email' => 'required|email',
            'email' => 'required|email|unique:users,email',
            'username' => 'required|alpha_num|unique:users,username|min:8',
            'password' => 'required|alpha_num|min:8',
            'user_type' => 'required|string',
            'status' => 'required|string',
            'street' => 'required|string',
            'barangay' => 'required|string',
            'municipality' => 'required|string',
            'rentalfee' => 'required|integer|min:1',
            'initial_payment' => 'required|integer|min:1',
            'advancepayment' => 'nullable|integer|min:1',
            'prepaidrentperiod' => 'required|integer|min:1',
            'deposit' => 'required|integer|min:1',
            'startDate' => 'required|date_format:m/d/Y',
            // 'endDate' => 'required|date_format:m/d/Y|after_or_equal:startDate',
            'rented_unit_id' => 'required|integer|min:1',
            'rented_unit_type' => 'required|string',
            'Newstatus' => 'required|string',
            'roomid' => 'required|integer',
            'bedId' => 'required|array', // Ensure bedId is an array or a single integer
            'bedId.*' => 'integer|nullable', // Make sure individual bed ids are integers or null
            'bedId' => 'required|integer' // If only one bed can be selected, you can use this rule.
        ];
    }
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status' => 'error',
            'message' => $validator->errors(),
        ], 422));
    }

}
