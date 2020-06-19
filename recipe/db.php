<?php

namespace Deployer;

use Symfony\Component\Console\Input\InputOption;

option('import', null, InputOption::VALUE_NONE, 'Auto-import the database when using db:pull');

desc('Pull remote database');
task('db:pull', function () {
    $filename = 'db_dump_' . date('YmdHis') . '.gz';

    cd('{{release_path}}');

    run('export DB_HOST=$(cat .env | grep DB_DSN | cut -d ":" -f 2 | sed "s/host=\(.*\);port.*/\1/");
        export DB_PORT=$(cat .env | grep DB_DSN | cut -d ":" -f 2 | sed -e "s/.*port=\(.*\);dbname.*/\1/");
        export DB_NAME=$(cat .env | grep DB_DSN | cut -d ":" -f 2 | sed -e "s/.*dbname=\(.*\);.*/\1/");
        export DB_USER=$(cat .env | grep DB_USER | cut -d "=" -f 2 | sed -e "s/^\"//" -e "s/\"$//");
        export DB_PASSWORD=$(cat .env | grep DB_PASSWORD | cut -d "=" -f 2,3,4 | sed -e "s/^\"//" -e "s/\"$//");
        mysqldump -h $DB_HOST -P $DB_PORT -u $DB_USER --password="$DB_PASSWORD" $DB_NAME | gzip > ' . $filename);
    download('{{release_path}}/' . $filename, $filename);
    run('rm {{release_path}}/' . $filename);

    $importItLocally = false;
    if (input()->hasOption('import')) {
        $importItLocally = input()->getOption('import');
    }

    if ($importItLocally === false) {
        $importItLocally = askConfirmation('Do you want to replace the local database with a remote copy?', true);
    }

    if ($importItLocally === false) {
        writeln('You can find a copy of the remote database here: ' . $filename);

        return;
    }

    runLocally('export DB_HOST=$(cat .env | grep DB_DSN | cut -d ":" -f 2 | sed "s/host=\(.*\);port.*/\1/");
        export DB_PORT=$(cat .env | grep DB_DSN | cut -d ":" -f 2 | sed -e "s/.*port=\(.*\);dbname.*/\1/");
        export DB_NAME=$(cat .env | grep DB_DSN | cut -d ":" -f 2 | sed -e "s/.*dbname=\(.*\);.*/\1/");
        export DB_USER=$(cat .env | grep DB_USER | cut -d "=" -f 2 | sed -e "s/^\"//" -e "s/\"$//");
        export DB_PASSWORD=$(cat .env | grep DB_PASSWORD | cut -d "=" -f 2,3,4 | sed -e "s/^\"//" -e "s/\"$//");
        gunzip < ' . $filename . ' | mysql -h $DB_HOST -P $DB_PORT -u $DB_USER --password="$DB_PASSWORD" $DB_NAME');
    runLocally('rm ' . $filename);
});

desc('Push local database to host');
task('db:push', function () {
    $confirmReplace = askConfirmation('Are you sure you want to replace the remote database by a local copy?', false);

    if ($confirmReplace === false) {
        return;
    }

    $filename = 'db_dump_' . date('YmdHis') . '.gz';

    runLocally('export DB_HOST=$(cat .env | grep DB_DSN | cut -d ":" -f 2 | sed "s/host=\(.*\);port.*/\1/");
        export DB_PORT=$(cat .env | grep DB_DSN | cut -d ":" -f 2 | sed -e "s/.*port=\(.*\);dbname.*/\1/");
        export DB_NAME=$(cat .env | grep DB_DSN | cut -d ":" -f 2 | sed -e "s/.*dbname=\(.*\);.*/\1/");
        export DB_USER=$(cat .env | grep DB_USER | cut -d "=" -f 2 | sed -e "s/^\"//" -e "s/\"$//");
        export DB_PASSWORD=$(cat .env | grep DB_PASSWORD | cut -d "=" -f 2,3,4 | sed -e "s/^\"//" -e "s/\"$//");
        mysqldump --column-statistics=0 -h $DB_HOST -P $DB_PORT -u $DB_USER --password="$DB_PASSWORD" $DB_NAME | gzip > ' . $filename);
    upload($filename, '{{release_path}}/' . $filename);
    runLocally('rm ' . $filename);

    cd('{{release_path}}');
    run('export DB_HOST=$(cat .env | grep DB_DSN | cut -d ":" -f 2 | sed "s/host=\(.*\);port.*/\1/");
        export DB_PORT=$(cat .env | grep DB_DSN | cut -d ":" -f 2 | sed -e "s/.*port=\(.*\);dbname.*/\1/");
        export DB_NAME=$(cat .env | grep DB_DSN | cut -d ":" -f 2 | sed -e "s/.*dbname=\(.*\);.*/\1/");
        export DB_USER=$(cat .env | grep DB_USER | cut -d "=" -f 2 | sed -e "s/^\"//" -e "s/\"$//");
        export DB_PASSWORD=$(cat .env | grep DB_PASSWORD | cut -d "=" -f 2,3,4 | sed -e "s/^\"//" -e "s/\"$//");
        gunzip < ' . $filename . ' | mysql -h $DB_HOST -P $DB_PORT -u $DB_USER --password="$DB_PASSWORD" $DB_NAME');
    run('rm ' . $filename);
});