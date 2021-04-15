<?php
declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use App\Http\Requests\BaseRequest;
use App\Models\Application;
use Illuminate\Foundation\Http\FormRequest;

class ApplicationCreateRequest extends FormRequest
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
            'name' => ['required'],
            'slug' => ['required', 'unique:'.Application::class.',slug'],
            'description' => ['sometimes', 'required', 'nullable'],
            'icon' => ['required', 'image'],
        ];
    }
}
