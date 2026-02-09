<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class PlayerStatsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'server_id' => ['required', 'integer'],
            'player_uuid' => ['required', 'string', 'max:255'],
            'player_name' => ['nullable', 'string', 'max:255'],
            'kills' => ['required', 'integer', 'min:0'],
            'deaths' => ['required', 'integer', 'min:0'],
            'playtime' => ['required', 'integer', 'min:0'],
            'xp' => ['nullable', 'integer', 'min:0'],
            'distance_traveled' => ['nullable', 'numeric', 'min:0'],
            'score' => ['nullable', 'integer', 'min:0'],
            'sessions' => ['nullable', 'integer', 'min:0'],
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
