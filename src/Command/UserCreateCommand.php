<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\Tools\Console\Command\SchemaTool\AbstractCommand;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserCreateCommand extends ContainerAwareCommand
{
    protected static $defaultName = 'user:create';

    private $passwordEncoder;
    private $entityManager;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder) {
        $this->passwordEncoder = $passwordEncoder;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Erstellt einen Benutzer per CLI wenn kein Admin-Account verfügbar ist.')
            ->addArgument('username', InputArgument::REQUIRED)
            ->addArgument("password", InputArgument::REQUIRED)
            ->addArgument("roles", InputArgument::REQUIRED)
            ->addArgument("email", InputArgument::REQUIRED)
            ->addArgument("displayName", InputArgument::REQUIRED)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $username = $input->getArgument("username");
        $password = $input->getArgument("password");
        $roles = $input->getArgument("roles");


        $user = new User();
        $user->setRoles(array($roles));
        $user->setPassword($this->passwordEncoder->encodePassword($user, $password));
        $user->setUsername($username);
        $user->setEmail($input->getArgument("email"));
        $user->setDisplayName($input->getArgument("displayName"));

        $entityManager = $this->getContainer()->get('doctrine')->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

        $io->success('Der Benutzer wurde erfolgreich erstellt.');
    }

}
