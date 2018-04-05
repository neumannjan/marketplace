<?php

return [
    'base' => [
        'greetings' => [
            'error' => 'Jejda!',
            'default' => 'Dobrý den!',
        ],
        'regards' => 'S pozdravem,<br>aplikace :site',
        'help' => "Pokud máte potíže stiknout tlačítko \":text\", zkopírujte a vložte následující odkaz do Vašeho webového prohlížeče: [:url](:url)"
    ],
    'register-activate' => [
        'greeting' => 'Vítejte v aplikaci :site!',
        'line1' => 'Děkujeme, že jste se registrovali do aplikace :site! Prosím, klikněte na následující odkaz, abyste aktivovali Váš nový účet:',
        'action' => 'Aktivovat účet',
        'line2' => 'Následně se můžete přihlásit pomocí Vašeho e-mailu (:email) nebo Vašeho nového uživatelského jména (:username).',
        'line3' => 'Pokud jste se do aplikace :site neregistrovali, prosím ignorujte tento e-mail úplně.',
    ],
    'password-reset' => [
        'subject' => 'Požadavek o obnovení hesla na :site',
        'line1' => 'Obdrželi jste tento e-mail, protože jsme dostali požadavek o obnovení hesla pro Váš účet.',
        'action' => 'Obnovit heslo',
        'line2' => 'Pokud jste o obnovení hesla nepožádali, můžete tento e-mail ignorovat.'
    ],
    'message' => [
        'greeting' => 'Obdrželi jste zprávu od uživatele :from v aplikaci :site!',
        'line1' => 'Kliknutím na tlačítko níže přejděte do aplikace :site a zobrazte zprávu.',
        'action' => 'Přejít do aplikace :site'
    ],
    'message-offer' => [
        'greeting' => 'Uživatel/ka :from chce koupit Vaši nabídku ":offer" v aplikaci :site!',
        'line1' => 'Více informací najdete v chatu. Kliknutím na tlačítko níže přejdete do aplikace :site.',
        'action' => 'Přejít do aplikace :site'
    ],
];