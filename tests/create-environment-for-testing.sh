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

echo ">> Copy all required files"
cp ../tests/Support/Models/* ./app/Models && echo "\xE2\x9C\x94 Models" || exit 1
cp ../tests/Support/Migrations/* ./database/migrations && echo "\xE2\x9C\x94 Migrations" || exit 1
cp ../tests/Support/Commands/* ./app/Console/Commands && echo "\xE2\x9C\x94 Commands" || exit 1

echo ">> Migrate"
php artisan migrate

echo ">> Test command"
php artisan
cd ./app/Console/Commands
ls -la

#
#echo "Add package from source"
#sed -e 's|"type": "project",|&\n"repositories": [ { "type": "path", "url": "../advanced-upsert-for-laravel" } ],|' -i composer.json || exit 1
#composer require "viktorruskai/advanced-upser-for-laravel:*" || exit 1

