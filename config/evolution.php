<?php

return [
    'url'      => env('EVOLUTION_API_URL', 'http://localhost:8080'),
    'key'      => env('EVOLUTION_API_KEY', ''),
    'instance' => env('EVOLUTION_DEFAULT_INSTANCE', ''),
    'timeout'  => env('EVOLUTION_API_TIMEOUT', 30),
];
