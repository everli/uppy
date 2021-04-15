<?php
declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use App\Rules\SemVer;
use Illuminate\Foundation\Http\FormRequest;

class UpdateGetRequest extends FormRequest
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
            'version' => [new SemVer()],
        ];
    }

    public function all($keys = null)
    {
        return array_merge(parent::all(), $this->route()->parameters());
    }
}
