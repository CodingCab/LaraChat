#!/bin/sh
#echo "Running pre-commit hooks"

PROJECT=`php -r "echo dirname(dirname(dirname(realpath('$0'))));"`
STAGED_FILES_CMD=`git diff --cached --name-only --diff-filter=ACMR HEAD | grep \\\\.php`

# Determine if a file list is passed
if [ "$#" -eq 1 ]
then
    oIFS=$IFS
    IFS='
    '
    SFILES="$1"
    IFS=$oIFS
fi
SFILES=${SFILES:-$STAGED_FILES_CMD}

echo "Running Code Sniffer. Code standard PSR2."
php vendor/bin/phpcbf

if [ "$FILES" != "" ]
then
    # Run PHP Code Sniffer
    ./vendor/bin/phpcs --encoding=utf-8 -n -p $FILES >&1
    if [ $? -ne 0 ]
    then
        echo "Fix the error before commit."
        exit 1
    fi
fi

echo "------"
echo "Running Coverage tests..."
php artisan test --stop-on-failure --filter 'Coverage' >null
if [ $? -ne 0 ]
then
    echo "Coverage tests failed. Please fix the issue before commit."
    exit 1
fi

php artisan migrate --force -n
php artisan migrate --force -n --database=phpunit

# Run the tests on the changed files
echo "Running PHPUnit, Dusk & Lint tests for each modified file..."
for FILE in $SFILES
do
    FILE_NAME=$(basename "$FILE" .php)

    echo "Processing file: $FILE_NAME"

    # Run PHP Lint
    php -l -d display_errors=0 $FILE >&1
    if [ $? -ne 0 ]
    then
        echo "Fix the error before commit."
        exit 1
    fi

    # Run PHPUnit tests
    php artisan test --stop-on-failure --filter "$FILE_NAME" >&1
    if [ $? -ne 0 ]
    then
        echo "PHPUnit tests failed. Please fix the issue before commit."
        exit 1
    fi

    # Run Dusk tests
    php artisan dusk --stop-on-failure --stop-on-error --filter "$FILE_NAME" >&1
    if [ $? -ne 0 ]
    then
        echo "Dusk tests failed. Please fix the issue before commit."
        exit 1
    fi

    FILES="$FILES $PROJECT/$FILE"
done

exit $?
