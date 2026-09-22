cd ..
@echo Clean composer dependencies
call composer install --no-dev --no-interaction
@echo Create readme.txt
copy /Y docs\Postie.txt+docs\Installation.txt+docs\Usage.txt+docs\FAQ.txt+docs\Changes.txt readme.txt
cd deploy
