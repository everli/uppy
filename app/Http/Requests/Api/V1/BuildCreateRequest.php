<?php
declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use App\Rules\SemVer;
use App\Rules\SupportedMimeTypes;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BuildCreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'version' => ['required', new SemVer()],
            'file' => ['required', 'file', new SupportedMimeTypes()],
            'available_from' => ['nullable', 'date', 'after_or_equal:now'],
            'forced' => [Rule::in(['on', 'off', true, false])],
            'changelogs.*' => ['nullable', 'string']
        ];
    }
}
