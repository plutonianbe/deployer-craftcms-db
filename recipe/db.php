<?php

namespace Deployer;

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

    $importItLocally = ask('Do you want to import it locally?',  'n', ['y', 'n']);

    if (strtolower($importItLocally) !== 'y') {
        return;
    }

    runLocally('export DB_HOST=$(cat .env | grep DB_DSN | cut -d ":" -f 2 | sed "s/host=\(.*\);port.*/\1/");
        export DB_PORT=$(cat .env | grep DB_DSN | cut -d ":" -f 2 | sed -e "s/.*port=\(.*\);dbname.*/\1/");
        export DB_NAME=$(cat .env | grep DB_DSN | cut -d ":" -f 2 | sed -e "s/.*dbname=\(.*\);.*/\1/");
        export DB_USER=$(cat .env | grep DB_USER | cut -d "=" -f 2 | sed -e "s/^\"//" -e "s/\"$//");
        export DB_PASSWORD=$(cat .env | grep DB_PASSWORD | cut -d "=" -f 2,3,4 | sed -e "s/^\"//" -e "s/\"$//");
        gunzip < ' . $filename . ' | mysql -h $DB_HOST -P $DB_PORT -u $DB_USER --password="$DB_PASSWORD" $DB_NAME');
});

desc('Push local database on host');
task('db:push', function () {
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