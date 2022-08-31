<?php
return [
    'adminEmail' => 'no-reply@toprank-accounting.com',
    'supportEmail' => 'no-reply@toprank-accounting.com',
    'user.passwordResetTokenExpire' => 3600,
    'maskMoneyOptions' => [
        'prefix' => 'P ',
        'affixesStay' => true,
        'thousands' => ',',
        'decimal' => '.',
        'precision' => 2, 
        'allowZero' => false,
        'allowNegative' => false,
    ]
];
