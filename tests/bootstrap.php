<?php

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

if (file_exists(dirname(__DIR__).'/config/bootstrap.php')) {
    require dirname(__DIR__).'/config/bootstrap.php';
} elseif (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__).'/.env');
}

passthru('rm -fr /tmp/app-test-storage-member-documents');
passthru(sprintf('php "%s/../bin/console" doctrine:database:drop --if-exists --force --env test', __DIR__));
passthru(sprintf('php "%s/../bin/console" doctrine:database:create --if-not-exists --env test', __DIR__));
passthru(sprintf('php "%s/../bin/console" doctrine:schema:create --env test', __DIR__));
passthru(sprintf('php "%s/../bin/console" doctrine:fixtures:load --no-interaction --env test', __DIR__));
