<?php

namespace App\Http\Requests;

use App\Models\MeasurementCondition;
use Illuminate\Validation\Rule;

class UpdateMeasurementConditionRequest extends StoreMeasurementConditionRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('masters.update') ?? false;
    }

    public function rules(): array
    {
        $condition = $this->route('measurement_condition');
        $id = $condition instanceof MeasurementCondition ? $condition->id : $condition;

        return [
            ...parent::rules(),
            'code' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('measurement_conditions', 'code')->ignore($id)],
        ];
    }
}
