<?php

// Format environment variables into Codeception params
return [
    'DB_HOST' => getenv('DB_HOST') ?: 'localhost',
    'DB_USERNAME' => getenv('DB_USERNAME') ?: 'username',
    'DB_PASSWORD' => getenv('DB_PASSWORD') ?: 'password',
    'DB_NAME' => getenv('DB_NAME') ?: 'database_name',
    'ABSOLUTE_PATH' => rtrim(getenv('ABSOLUTE_PATH') ?: 'localhost', '/'),
    'ABSOLUTE_URL' => rtrim(getenv('ABSOLUTE_URL') ?: 'https://127.0.0.1:8888', '/'),
];
