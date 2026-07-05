<?php

return [
    'name_column' => 'name',
    'avatar_column' => 'avatar',
    'disk' => env('PUBLIC_STORAGE_DISK', 'public'),
    'visibility' => 'public', // or replace by filesystem disk visibility with fallback value
];
