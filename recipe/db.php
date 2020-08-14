<?php

namespace Deployer;

use Symfony\Component\Console\Input\InputOption;

option('import', null, InputOption::VALUE_NONE, 'Auto-import the database when using db:pull');

desc('Pull remote database');
task('db:pull', function () {
    $filePath = 'storage/backups/deployer_db_dump_' . date('YmdHis') . '.sql';

    cd('{{release_path}}');

    run('{{release_path}}/craft backup/db');
    download('`ls -tdr {{release_path}}/storage/backups/* | tail -1`', $filePath);
    run('rm `ls -tdr {{release_path}}/storage/backups/* | tail -1`');

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

    runLocally("./craft restore/db $filePath");
    runLocally("rm $filePath");
});

desc('Push local database to host');
task('db:push', function () {
    $confirmReplace = askConfirmation('Are you sure you want to replace the remote database by a local copy?', false);

    if ($confirmReplace === false) {
        return;
    }

    $filePath = 'storage/backups/deployer_db_dump_' . date('YmdHis') . '.sql';

    runLocally("./craft backup/db $filePath");

    if (!test('[ -d {{release_path}}/storage/backups ]')) {
        if (!commandExist('mkdir')) {
            fail('{{release_path}}/storage/backups does not exist and it cannot be created with the mkdir command');
        }
        run('mkdir {{release_path}}/storage/backups');
    }

    upload($filePath, "{{release_path}}/$filePath");
    runLocally("rm $filePath");

    cd('{{release_path}}');
    run("./craft restore/db $filePath");
    run("rm $filePath");
});