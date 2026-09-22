#!/bin/sh
cd ..
echo Clean composer dependencies
composer install --no-dev --no-interaction
echo Create readme.txt
cat docs/Postie.txt docs/Installation.txt docs/Usage.txt docs/FAQ.txt docs/Changes.txt > readme.txt
cd deploy
