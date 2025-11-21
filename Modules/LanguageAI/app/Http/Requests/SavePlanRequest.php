<?php

namespace Modules\LanguageAI\Http\Requests;

use App\Enums\Permission;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class SavePlanRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $permission = $this->route('id') 
            ? Permission::LANGUAGE_AI_PLANS_UPDATE->value 
            : Permission::LANGUAGE_AI_PLANS_CREATE->value;

        return Gate::allows($permission);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $id = $this->route('id');
        
        $planCodeUniqueRule = 'required|string|max:255|unique:mongodb.master_plans,plan_code';
        if ($id) {
            $planCodeUniqueRule .= ',' . $id . ',_id';
        }

        return [
            'plan_name'           => 'required|string|max:255',
            'plan_code'           => $planCodeUniqueRule,
            'price'               => 'required|numeric|min:0',
            'currency'            => 'required|string|size:3',
            'interval'            => 'required|string|in:daily,weekly,monthly,yearly',
            'duration'            => 'required|integer|min:-1|not_in:0',
            'features'            => 'nullable|array',
            'features.*'          => 'required|string',
            'is_active'           => 'nullable|boolean',
            'is_popular'          => 'nullable|boolean',
            'is_displayed'        => 'nullable|boolean',
            'max_usage'           => 'nullable|array',
            'max_usage.monthly'   => 'nullable|integer|min:-1',
        ];
    }
}

