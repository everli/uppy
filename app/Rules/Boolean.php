<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class Boolean implements Rule
{

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return in_array($value, [
            true,
            false,
            'true',
            'false',
            'on',
            'off'
        ], true);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The field :attribute must be a boolean.';
    }
}
