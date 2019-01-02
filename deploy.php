<?php

namespace Deployer;
use function Deployer\{host, task, run, set, get, add, before, after, upload, writeln};

require_once 'recipe/symfony.php';

set('repository', 'https://github.com/henrybrink/schuelerblog');

host("production")
    ->user('ssh-w018dd9f')
    ->hostname('w018dd9f.kasserver.com')
    ->set('branch', "production")
    ->set('deploy_path', "~/deploy_test")
    ->port(22)
    ->identityFile('~/.ssh/w018dd9f')
    ->forwardAgent(true)
    ->multiplexing(true)
    ->addSshOption("UserKnownHostsFile", '/dev/null')
    ->addSshOption("StrictHostKeyChecking", "no");

