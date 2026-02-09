<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ServerStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'server_id' => ['required', 'integer'],
            'server_name' => ['required', 'string', 'max:255'],
            'map' => ['required', 'string', 'max:255'],
            'players' => ['required', 'integer', 'min:0'],
            'max_players' => ['required', 'integer', 'min:0'],
            'ping' => ['required', 'integer', 'min:0'],
            'timestamp' => ['required', 'integer'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $validator->errors(),
        ], 422));
    }
}
