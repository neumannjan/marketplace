<?php

return [
    'base' => [
        'greetings' => [
            'error' => 'Whoops!',
            'default' => 'Hello!',
        ],
        'regards' => 'Regards,<br>:site',
        'help' => "If you’re having trouble clicking the \":text\" button, copy and paste the URL below into your web browser: [:url](:url)"
    ],
    'register-activate' => [
        'greeting' => 'Welcome to :site!',
        'line1' => 'Thank you for registering at :site! Please click the button below to activate your new account.',
        'action' => 'Activate Account',
        'line2' => 'You can then login with your e-mail (:email) or your new username (:username).',
        'line3' => 'If you did not register at :site, please ignore this e-mail altogether.',
    ],
    'password-reset' => [
        'subject' => 'Password reset request on :site',
        'line1' => 'You are receiving this e-mail because we received a password reset request for your account.',
        'action' => 'Reset Password',
        'line2' => 'If you did not request a password reset, no further action is required.'
    ],
    'message' => [
        'greeting' => 'You received a message from :from on :site!',
        'line1' => 'Click the button below to go to :site and view the message.',
        'action' => 'Go to :site'
    ],
    'message-offer' => [
        'greeting' => 'User :from wants to buy your offer ":offer" on :site!',
        'line1' => 'You will find additional information in chat. Click the button below to go to :site.',
        'action' => 'Go to :site'
    ],
];