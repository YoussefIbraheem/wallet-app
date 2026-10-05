<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class PaymentRequestFormRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "reference" => ["string", "required", "uuid"],
            "amount" => ["numeric", "required"],
            "date" => ["date", "required"],
            "currency" => ["string", "required"],
            "sender_account_number" => ["string", "required"],
            "bank_code" => ["string", "required"],
            "receiver_account_number" => ["string", "required"],
            "beneficiary_name" => ["string", "required"],
            "notes" => ["array", "nullable"],
            "notes.*" => ["string"],
            "payment_type" => ["string", "required"],
            "charge_details" => ["string", "required"],
        ];
    }
}
