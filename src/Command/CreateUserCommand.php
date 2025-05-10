<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-user',
    description: 'Creation des utilisateurs',
)]
class CreateUserCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $helper = $this->getHelper("question");

        $questionEmail = new Question("Entrer votre adresse email :");
        $questionEmail->setValidator(function ($value){
            if (trim($value) === '') throw new \Exception("L'adresse ne pas être vide");

            if(!filter_var($value, FILTER_VALIDATE_EMAIL)) throw new \Exception("L'email saisi n'est pas valide");

            return $value;
        });
        $email = $helper->ask($input, $output, $questionEmail);

        $questionPassword = new Question("Entrez votre mot de passe: ");
        $questionPassword->setHidden(true);
        $questionPassword->setHiddenFallback(false);
        $questionPassword->setValidator(function ($value){
            if (trim($value) == ''){
                throw new \Exception('The mot de passe ne peut être vide');
            }
            return $value;
        });
        $password = $helper->ask($input, $output, $questionPassword);

        $questionRole = new ChoiceQuestion('Choisissez le role: ',['ROLE_USER','ROLE_REGION','ROLE_ASN','ROLE_ADMIN','ROLE_SUPER_ADMIN']);
        $questionRole->setErrorMessage('Role invalide');
        $role = $helper->ask($input, $output, $questionRole);

        $user = new User();
        $user->setEmail($email);
        $user->setPassword($this->passwordHasher->hashPassword($user, $password));
        $user->setRoles([$role]);

        $this->entityManager->persist($user);
        $this->entityManager->flush();


        $io->success("Utilisateur '{$email}' crée avec succès!");

        return Command::SUCCESS;
    }
}
