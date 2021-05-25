<?php
declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use App\Rules\Boolean;
use App\Rules\SupportedMimeTypes;
use Illuminate\Foundation\Http\FormRequest;

class BuildUpdateRequest extends FormRequest
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
            'file' => ['nullable', 'file', new SupportedMimeTypes()],
            'available_from' => ['nullable', 'date'],
            'forced' => ['nullable', new Boolean()],
            'changelogs.*' => ['nullable', 'string']
        ];
    }
}
