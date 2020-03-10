#!/bin/bash

composer install

php artisan key:generate
php artisan migrate

find storage bootstrap/cache public -type f -exec chmod o+w {} \;
find storage bootstrap/cache public -type d -exec chmod o+wx {} \;
chmod o-w public/index.php

php-fpm

