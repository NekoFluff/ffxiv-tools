<?php

namespace App\Http\Requests;

use App\Models\Retainer;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Validator;

class StoreItemRetainerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        /** @var int $retainerID */
        $retainerID = $this->route('retainerID');

        return Auth::user()->retainers->contains(intval($retainerID));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'item_id' => ['required', 'integer', 'exists:items,id'],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'item_id.required' => 'You must select an item',
        ];
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

                /** @var Retainer $retainer */
                $retainer = Auth::user()->retainers->find($this->route('retainerID'));
                if ($retainer->items->contains($this->input('item_id'))) {
                    /** @var \App\Models\Item $item */
                    $item = $retainer->items->get($this->input('item_id'));
                    $validator->errors()->add('item_id', $item->name.' is already linked to the retainer');
                }

                if ($retainer->items->count() >= 20) {
                    $validator->errors()->add('item_id', 'The retainer can only have 20 items');
                }
            },
        ];
    }
}
