<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted' => ':attribute must be accepted.',
    'active_url' => ':attribute is not a valid URL.',
    'after' => ':attribute must be a date after :date.',
    'after_or_equal' => ':attribute must be a date after or equal to :date.',
    'alpha' => ':attribute may only contain letters.',
    'alpha_dash' => ':attribute may only contain letters, numbers, and dashes.',
    'alpha_num' => ':attribute may only contain letters and numbers.',
    'array' => ':attribute must be an array.',
    'before' => ':attribute must be a date before :date.',
    'before_or_equal' => ':attribute must be a date before or equal to :date.',
    'between' => [
        'numeric' => ':attribute must be between :min and :max.',
        'file' => ':attribute must be between :min and :max kilobytes.',
        'string' => ':attribute must be between :min and :max characters.',
        'array' => ':attribute must have between :min and :max items.',
    ],
    'boolean' => ':attribute field must be true or false.',
    'confirmed' => ':attribute confirmation does not match.',
    'contains' => [
        'numeric' => ':attribute must contain at least one number.',
        'non_numeric' => ':attribute must contain at least one character that is not a number.',
    ],
    'date' => ':attribute is not a valid date.',
    'date_format' => ':attribute does not match format :format.',
    'different' => ':attribute and :other must be different.',
    'digits' => ':attribute must be :digits digits.',
    'digits_between' => ':attribute must be between :min and :max digits.',
    'dimensions' => ':attribute has invalid image dimensions.',
    'distinct' => ':attribute field has a duplicate value.',
    'email' => ':attribute must be a valid email address.',
    'exists' => 'selected :attribute is invalid.',
    'file' => ':attribute must be a file.',
    'filled' => ':attribute field must have a value.',
    'image' => ':attribute must be an image.',
    'in' => 'selected :attribute is invalid.',
    'in_array' => ':attribute field does not exist in :other.',
    'integer' => ':attribute must be an integer.',
    'ip' => ':attribute must be a valid IP address.',
    'ipv4' => ':attribute must be a valid IPv4 address.',
    'ipv6' => ':attribute must be a valid IPv6 address.',
    'json' => ':attribute must be a valid JSON string.',
    'max' => [
        'numeric' => ':attribute may not be greater than :max.',
        'file' => ':attribute may not be greater than :max kilobytes.',
        'string' => ':attribute may not be greater than :max characters.',
        'array' => ':attribute may not have more than :max items.',
    ],
    'mimes' => ':attribute must be a file of type: :values.',
    'mimetypes' => ':attribute must be a file of type: :values.',
    'min' => [
        'numeric' => ':attribute must be at least :min.',
        'file' => ':attribute must be at least :min kilobytes.',
        'string' => ':attribute must be at least :min characters.',
        'array' => ':attribute must have at least :min items.',
    ],
    'not_in' => 'selected :attribute is invalid.',
    'numeric' => ':attribute must be a number.',
    'present' => ':attribute field must be present.',
    'regex' => ':attribute format is invalid.',
    'required' => ':attribute field is required.',
    'required_if' => ':attribute field is required when :other is :value.',
    'required_unless' => ':attribute field is required unless :other is in :values.',
    'required_with' => ':attribute field is required when :values is present.',
    'required_with_all' => ':attribute field is required when :values is present.',
    'required_without' => ':attribute field is required when :values is not present.',
    'required_without_all' => ':attribute field is required when none of :values are present.',
    'same' => ':attribute and :other must match.',
    'size' => [
        'numeric' => ':attribute must be :size.',
        'file' => ':attribute must be :size kilobytes.',
        'string' => ':attribute must be :size characters.',
        'array' => ':attribute must contain :size items.',
    ],
    'slug' => ':attribute can only contain letters, numbers, dashes and underscores.',
    'string' => ':attribute must be a string.',
    'timezone' => ':attribute must be a valid zone.',
    'unique' => ':attribute has already been taken.',
    'uploaded' => ':attribute failed to upload.',
    'url' => ':attribute format is invalid.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap attribute place-holders
    | with something more reader friendly such as E-Mail Address instead
    | of "email". This simply helps us make messages a little cleaner.
    |
    */

    'attributes' => [
        'login' => 'Login',
        'username' => 'Username',
        'email' => 'E-Mail Address',
        'password' => 'Password',
        'display_name' => 'Display Name',
        'password_confirmation' => 'Confirm Password'
    ],

];
