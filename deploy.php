<?php
namespace Deployer;

require 'recipe/symfony.php';

// Config

set('repository', 'https://github.com/delrodie/backendScoutReady.git');

add('shared_files', []);
add('shared_dirs', []);
add('writable_dirs', []);

// Hosts

host('backstage.scoutready.net')
    ->set('remote_user', 'amdi3587')
    ->set('deploy_path', '~/backstage');

// Hooks
task('build', function (){
    run('composer install --no-dev');
    run('php/bin console cache:clear');
});
task('deploy', function () {
    upload(__DIR__ . '/', '{{deploy_path}}');
});

after('deploy:failed', 'deploy:unlock');
