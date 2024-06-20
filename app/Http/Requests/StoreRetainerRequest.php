<?php

namespace App\Http\Requests;

use App\Models\Enums\Server;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreRetainerRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255', 'min:2'],
            'server' => ['required', Rule::enum(Server::class)],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<int, Closure>
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                $user = Auth::user();
                if ($user->retainers->count() >= 10) {
                    $validator->errors()->add('name', 'You can only have 10 retainers');
                }

                if ($user->retainers->contains('name', $this->input('name'))) {
                    $validator->errors()->add('name', 'You already have a retainer with that name');
                }
            },
        ];
    }
}
