<?php

namespace App\Command;

use App\Entity\Setting;
use App\Entity\User;
use Doctrine\DBAL\Migrations\FileQueryWriter;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class SystemInstallCommand extends ContainerAwareCommand
{
    protected static $defaultName = 'system:install';

    private $fileSystem;
    private $kernel;
    private $passwordEncoder;

    public function __construct($name = null, Filesystem $fileSystem, KernelInterface $kernel, UserPasswordEncoderInterface $passwordEncoder) {
        $this->fileSystem = $fileSystem;
        $this->kernel = $kernel;
        $this->passwordEncoder = $passwordEncoder;
        parent::__construct($name);
    }

    protected function configure() {
        $this
            ->setDescription('Dieser Befehl installiert das System, bitte führe zunächst composer install aus, sonst kannst du den Befehl nicht benutzen');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $io = new SymfonyStyle($input, $output);

        if($this->fileSystem->exists(".env")) {
            $question = $io->confirm("Möchtest du das System wirklich installieren? Ist dieses bereits installiert, werden möglicherweise Konfigurationsdateien überschrieben.", true);

            if($question) {

                // Datenbank initinieren
                $app = new Application($this->kernel);
                $app->setAutoExit(false);

                $cmd_input = new ArrayInput([
                    'command' => 'doctrine:migrations:migrate'
                ]);

                $app->run($cmd_input, new NullOutput());

                $helper = $this->getHelper('question');

                // Benutzer einrichten
                $user = new User();

                $io->writeln("Bitte erstelle nun einen neuen Benutzer!");
                $user->setUsername($helper->ask($input, $output, new Question("Benutzername?")));
                $user->setPassword($this->passwordEncoder->encodePassword($user, $helper->ask($input, $output, new Question("Passwort?"))));
                $user->setEmail($helper->ask($input, $output, new Question("E-Mailadresse?")));
                $user->setDisplayName("Administrator");
                $user->setRoles(["ROLE_ADMIN"]);

                /** @var EntityManagerInterface $em */
                $em = $this->getContainer()->get('doctrine')->getManager();
                $em->persist($user);
                $em->flush();

                $io->success("Der Benutzer wurde erfolgreich erstellt!");
                $io->writeln("Das System wird nun initialisiert, bitte habe einen Moment geduld!");

            } else {
                $io->error("Du hast die Installation abgebrochen!");
                exit;
            }

        } else {

            $io->caution("Es wurde noch keine Konfigurationsdatei angelegt, bitte erledige dies bevor du das System installieren kannst");

            /* /* MySQL Zugangsdaten - bevor hier irgendetwas passiert, müssen wir zunächst die Zugangsdaten abchecken!
            /** @var QuestionHelper $helper
            $helper = $this->getHelper('question');

            $mysql_host_question = new Question("Bitte gebe den Host deiner Datenbank an (Normalerweise localhost)");
            $ms_host = $helper->ask($input, $output, $mysql_host_question);

            $ms_db_q = new Question("Bitte gebe den Datenbanknamen ein");
            $ms_db = $helper->ask($input, $output, $ms_db_q);

            $ms_user_q = new Question("Bitte gebe den Benutzernamen des Datenbankbesitzers ein");
            $ms_user = $helper->ask($input, $output, $ms_user_q);

            $ms_pass_q = new Question("Bitte gebe das Passwort für den Datenbankbenutzer ein");
            $ms_pass = $helper->ask($input, $output, $ms_pass_q); */

        }


        $io->success('Das System wurde erfolgreich installiert - viel Spaß mit deinem Schülerblog!');
    }
}
