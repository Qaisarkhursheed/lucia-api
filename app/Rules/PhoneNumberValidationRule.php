<?php


namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class PhoneNumberValidationRule implements Rule
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
       return  preg_match("/[+][0-9]{8,15}/" , $value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return "The :attribute may only contain + and numbers. minimum of 10 digits.";
    }
}
