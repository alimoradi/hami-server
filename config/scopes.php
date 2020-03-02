<?php
return
[

    'service_provider' =>
    [
        'can-be-provider',
        'can-edit-profile'
    ],
    'service_user' =>
    [
        'can-be-user'
    ],
    'admin' =>
    [
        'can-get-users'
    ],
    'all'=>
    [
        'can-get-users' => 'Can get Users',
        'can-be-provider' => 'Ca be Provider',
        'can-be-user' => 'Can be User',
        'can-edit-profile' => 'Can edit own profile'
    ]

];
