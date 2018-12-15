<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class ResetPasswordCommand extends ContainerAwareCommand
{
    protected static $defaultName = 'user:resetPassword';
    private $passwordEncoder;

    protected function configure()
    {
        $this
            ->setDescription("Reset the password of a user, even if you don't have access to the dashboard")
            ->addArgument("username")
        ;
    }

    public function __construct($name = null, UserPasswordEncoderInterface $passwordEncoder) {
        parent::__construct($name);
        $this->passwordEncoder = $passwordEncoder;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $io = new SymfonyStyle($input, $output);

        $username = $input->getArgument("username");

        if (!$username) {
            $io->error("Du musst einen Benutzer angeben!");
        }

        /** @var UserRepository $userRepository */
        $userRepository = $this->getContainer()->get("doctrine")->getRepository(User::class);
        $user = $userRepository->findOneBy(['username' => $username]);

        if (!$user) {
            $io->error("Der Benutzer konnte nicht gefunden werden!");
        } else {

            /** @var QuestionHelper $questionHelper */
            $questionHelper = $this->getHelper("question");
            $newPassword = $questionHelper->ask($input, $output, new Question("Neues Passwort für den Benutzer?: "));

            $user->setPassword($this->passwordEncoder->encodePassword($user, $newPassword));

            /** @var EntityManagerInterface $em */
            $em = $this->getContainer()->get('doctrine')->getManager();
            $em->flush();

            $io->success('Das Passwort wurde geändert.');

        }
    }
}
