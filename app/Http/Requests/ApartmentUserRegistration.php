<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ApartmentUserRegistration extends FormRequest
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
            'advancepayment' => 'nullable|integer|min:1',
            'initial_payment' => 'required|integer|min:1',
            'prepaidrentperiod' => 'required|integer|min:1',
            'deposit' => 'required|integer|min:1',
            'startDate' => 'required|date_format:m/d/Y',
            'rented_unit_id' => 'required|integer|min:1',
            'rented_unit_type' => 'required|string',
            'Newstatus' => 'required|string',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status' => 'error',
            'message' => $validator->errors(),
        ], 422));
    }
}
