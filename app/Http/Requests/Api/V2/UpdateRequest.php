<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V2;


use App\Rules\SemVer;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'version' => ['required', new SemVer()],
            'device_id' => ['nullable']
        ];
    }
}
