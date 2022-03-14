#!/usr/bin/env bash

# Inspired by https://github.com/nunomaduro/larastan/blob/669b489e10558bd45fafc2429068fd4a73843802/tests/laravel-test.sh

LARAVEL_VERSION="$1"
if [[ "$LARAVEL_VERSION" = "" ]]; then
    echo "ERROR: Usage of this script is: $0 <laravel version>"
    exit 1
fi

echo ">> Install Laravel"
composer create-project --prefer-dist laravel/laravel:$LARAVEL_VERSION ./laravel || exit 1
cd ./laravel

echo ">> Install Faker"
composer require fakerphp/faker

echo ">> Copy all required files"
cp ../tests/Support/Models/* ./app/Models && echo "\xE2\x9C\x94 Models" || exit 1
cp ../tests/Support/Migrations/* ./database/migrations && echo "\xE2\x9C\x94 Migrations" || exit 1
cp ../tests/Support/Factories/* ./database/factories && echo "\xE2\x9C\x94 Factories" || exit 1
mkdir -m755 ./app/Console/Commands
cp ../tests/Support/Commands/* ./app/Console/Commands && echo "\xE2\x9C\x94 Commands" || exit 1


# todo: mozno vyskaut Commands/* ???? ked to prekopiruje tak sa spravi z toho subor a nie directory

echo ">> Migrate"
php artisan migrate

echo ">> Test command"
php artisan upsert:test

#
#echo "Add package from source"
#sed -e 's|"type": "project",|&\n"repositories": [ { "type": "path", "url": "../advanced-upsert-for-laravel" } ],|' -i composer.json || exit 1
#composer require "viktorruskai/advanced-upser-for-laravel:*" || exit 1

