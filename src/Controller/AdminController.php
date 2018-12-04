<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use http\Env\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Class AdminController
 * @package App\Controller#
 *
 * @Route("/dashboard/admin")
 * @IsGranted("ROLE_ADMIN")
 */
class AdminController extends AbstractController {

    /**
     * @Route("/", name="admin")
     */
    public function index()
    {
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }

    /**
     * @Route("/users", name="listUsers")
     */
    public function listUsers() {

        $users = $this->getDoctrine()->getRepository(User::class)->findAll();

        return $this->render("admin/users.list.html.twig", array(
            "users" => $users
        ));
    }

    /**
     * @Route("/users/edit/{id}", name="editUser")
     */
    public function editUser($id, UserRepository $repository, \Symfony\Component\HttpFoundation\Request $request) {

        $user = $repository->find($id);

        if(!$user) {
            throw $this->createNotFoundException("User not found");
        }

        $form = $this->createFormBuilder($user)
            ->add("displayName", TextType::class, array('label' => "Angezeigter Name"))
            ->add("email", EmailType::class, array('label' => "E-Mailadresse"))
            ->add("roles", ChoiceType::class, array('multiple' => true, "choices" => array(
                "Administrator" => "ROLE_ADMIN",
                "Moderator" => "ROLE_MOD",
                "Redakteur" => "ROLE_EDITOR",
            ), "label" => "Rollen (mehrere Möglich)"))
            ->add("submit", SubmitType::class, array('label' => "Benutzer aktualisieren"))
            ->getForm();


        if($form->handleRequest($request) && $form->isSubmitted() && $form->isValid()) {
            /** @var User $user */
            $user = $form->getData();

            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $this->addFlash("success", "Der Benuter wurde erfolgreich erstellt");
        }

        return $this->render("admin/user.edit.html.twig", array(
            'form' => $form->createView(),
            "user" => $user,
        ));
    }

    /**
     * @Route("/create", name="createUser")
     */
    public function createUser(\Symfony\Component\HttpFoundation\Request $request, UserPasswordEncoderInterface $passwordEncoder) {

        $user = new User();

        $form = $this->createFormBuilder($user)
            ->add("username", TextType::class, ["label" => "Benutername"])
            ->add('email', EmailType::class, ['label' => "E-Mailadresse"])
            ->add('displayName', TextType::class, ['label' => "Angezeigter Name"])
            ->add('password', RepeatedType::class, [
                'first_options' => ['label' => 'Passwort'],
                'second_options' => ['label' => "Passwort wiederholen"],
                'type' => PasswordType::class,
            ])
            ->add('roles', ChoiceType::class, [
                'multiple' => true,
                'choices' => [
                    'Administrator' => "ROLE_ADMIN",
                    'Moderator' => "ROLE_MOD",
                    'Redakteur' => "ROLE_EDITOR"
                ]
            ])
            ->add('submit', SubmitType::class, ['label' => "Benutzer erstellen"])
            ->getForm();

        if($form->handleRequest($request) && $form->isSubmitted() && $form->isValid()) {

            /** @var User $user */
            $user = $form->getData();

            $user->setPassword($passwordEncoder->encodePassword($user, $user->getPassword()));

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute("listUsers");
        }

        return $this->render('admin/user.create.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
