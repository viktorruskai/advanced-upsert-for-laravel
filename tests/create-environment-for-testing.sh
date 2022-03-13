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

echo ">> Migrate"
php artisan migrate

#echo "Add package from source"
#sed -e 's|"type": "project",|&\n"repositories": [ { "type": "path", "url": "../advanced-upsert-for-laravel" } ],|' -i composer.json || exit 1
#composer require --dev "viktorruskai/advanced-upser-for-laravel:*" || exit 1

