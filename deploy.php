<?php
namespace Deployer;

require 'recipe/symfony.php';

// Config
set('repository', 'https://github.com/delrodie/backendScoutReady.git');

add('shared_files', []);
add('shared_dirs', []);
add('writable_dirs', []);

// Hosts
host('strategie.o2switch.net')
    ->set('remote_user', 'amdi3587')
    ->set('deploy_path', '~/backstage/v1')
    ->set('ssh_multiplexing', false)
    ->set('identity_file', '~/.ssh/id_rsa');

// Custom task
task('build', function () {
    run('composer install --no-dev');
    run('{{bin/php}} {{bin/console}} cache:clear');
});

// Hook: ajoute ta tâche `build` après que le code est téléchargé
after('deploy:vendors', 'build');

// En cas d'échec
after('deploy:failed', 'deploy:unlock');