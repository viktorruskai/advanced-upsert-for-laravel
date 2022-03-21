#!/usr/bin/env bash

# Inspired by https://github.com/nunomaduro/larastan/blob/669b489e10558bd45fafc2429068fd4a73843802/tests/laravel-test.sh

LARAVEL_VERSION="$1"
if [[ "$LARAVEL_VERSION" = "" ]]; then
    echo "ERROR: Usage of this script is: $0 <laravel version>"
    exit 1
fi

echo ">> Install Laravel"
composer create-project --prefer-dist laravel/laravel:$LARAVEL_VERSION ../laravel || exit 1
cd ../laravel

echo ">> Install Faker"
composer require fakerphp/faker

echo "Add package from Github Workflow source"
sed -e 's|"type": "project",|&\n"repositories": [ { "type": "path", "url": "../advanced-upsert-for-laravel" } ],|' -i composer.json || exit 1
composer require --dev "viktorruskai/advanced-upsert-for-laravel:*" || exit 1

echo ">> Copy all required files"
cp ../advanced-upsert-for-laravel/tests/Support/Models/* ./app/Models && echo -e "\xE2\x9C\x94 Models" || exit 1
cp ../advanced-upsert-for-laravel/tests/Support/Migrations/* ./database/migrations && echo -e "\xE2\x9C\x94 Migrations" || exit 1
cp ../advanced-upsert-for-laravel/tests/Support/Factories/* ./database/factories && echo -e "\xE2\x9C\x94 Factories" || exit 1
cp ../advanced-upsert-for-laravel/tests/Support/Tests/Unit/* ./tests/Unit && echo -e "\xE2\x9C\x94 Tests (Unit)" || exit 1
cp ../advanced-upsert-for-laravel/tests/Support/Tests/Feature/* ./tests/Feature && echo -e "\xE2\x9C\x94 Tests (Feature)" || exit 1

echo ">> Migrate"
php artisan migrate

echo ">> Start testing"
php artisan test

echo ">> Coverage"
./vendor/bin/phpunit --coverage-text