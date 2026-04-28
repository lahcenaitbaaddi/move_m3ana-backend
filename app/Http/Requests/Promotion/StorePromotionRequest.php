<?php

namespace App\Http\Requests\Promotion;

use App\Models\Promotion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePromotionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'promotable_type' => [
                'required',
                'string',
                Rule::in(array_keys(Promotion::PROMOTABLE_TYPES)),
            ],
            'promotable_id' => 'required|integer',
            'start_date'    => 'required|date|after_or_equal:now',
            'end_date'      => 'required|date|after:start_date',
            'is_boosted'    => 'sometimes|boolean',
        ];
    }

    /**
     * Validation supplémentaire : vérifier que l'entité promotable existe.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $type = $this->input('promotable_type');
            $id   = $this->input('promotable_id');

            if ($type && $id) {
                $types = Promotion::PROMOTABLE_TYPES;

                if (isset($types[$type])) {
                    $modelClass = $types[$type];
                    if (!$modelClass::find($id)) {
                        $validator->errors()->add(
                            'promotable_id',
                            "L'entité {$type} avec l'ID {$id} n'existe pas."
                        );
                    }
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'promotable_type.required' => 'Le type de contenu à promouvoir est obligatoire.',
            'promotable_type.in'       => 'Le type doit être : activity, club ou event.',
            'promotable_id.required'   => 'L\'ID du contenu à promouvoir est obligatoire.',
            'start_date.required'      => 'La date de début est obligatoire.',
            'start_date.after_or_equal'=> 'La date de début doit être aujourd\'hui ou dans le futur.',
            'end_date.required'        => 'La date de fin est obligatoire.',
            'end_date.after'           => 'La date de fin doit être après la date de début.',
        ];
    }
}
