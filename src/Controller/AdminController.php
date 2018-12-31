<?php

namespace App\Controller;

use App\Entity\AttachedImage;
use App\Entity\Category;
use App\Entity\Page;
use App\Entity\Post;
use App\Entity\Setting;
use App\Entity\User;
use App\Form\TinymceType;
use App\Repository\CategoryRepository;
use App\Repository\PageRepository;
use App\Repository\PostRepository;
use App\Repository\SettingRepository;
use App\Repository\UserRepository;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use http\Env\Request;
use phpDocumentor\Reflection\Types\Boolean;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RadioType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\JsonResponse;
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
     * @Route("/users/create", name="createUser")
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

    /**
     * @Route("/users/resetPassword/{id}", name="resetPassword")
     * @throws \Exception
     */
    public function passwordReset($id, UserRepository $repository, UserPasswordEncoderInterface $passwordEncoder) {
        $user = $repository->find($id);

        if(!$user || $user == null) {
            throw $this->createNotFoundException("User not found");
        }

        $randomPassword = substr(md5(random_bytes(20)), 0, 10);

        $user->setPassword($passwordEncoder->encodePassword($user, $randomPassword));

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        $this->addFlash('success', "Das Passwort wurde erfolgreich geändert. Neues Passwort: " . $randomPassword);
        return $this->redirectToRoute('editUser', ['id' => $user->getId()]);
    }

    /**
     * @Route("/users/delete/{id}", name="userDelete")
     */
    public function deleteUser($id, UserRepository $userRepository) {
        $user = $userRepository->find($id);

        if(!$user) {
            $this->addFlash('error', "Benutzer nicht gefunden");
            return $this->redirectToRoute('listUsers');
        }

        if($user == $this->getUser()) {
            $this->addFlash('error', "Du kannst dich nicht selbst löschen");
            return $this->redirectToRoute('listUsers');
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($user);
        $em->flush();

        $this->addFlash('success', "Der Benutzer wurde erfolgreich gelöscht");

        return $this->redirectToRoute("listUsers");
    }

    /**
     * @Route("/pages/add", name="pageAdd")
     */
    public function addPage(\Symfony\Component\HttpFoundation\Request $request) {
        $page = new Page();

        $form = $this->createFormBuilder($page)
            ->add('title', TextType::class, ['label' => "Titel der Seite"])
            ->add('slug', TextType::class, ['label' => "URL der Seite"])
            ->add('content', TinymceType::class, ['label' => "Inhalt der Seite"])
            ->add('submit', SubmitType::class, ['label' => "Speichern"])
            ->getForm();

        if($form->handleRequest($request) && $form->isSubmitted() && $form->isValid()) {
            $page = $form->getData();
            $page->setOwner($this->getUser());

            $em = $this->getDoctrine()->getManager();
            $em->persist($page);
            $em->flush();

            $this->addFlash('success', "Die Seite wurde erfolgreich erstellt.");
            return $this->redirectToRoute('pagesList');
        }

        return $this->render('admin/pages/pages.add.html.twig', [
            'form' => $form->createView(),
            'imageType' => 'page',
            'tinymce' => true
        ]);
    }

    /**
     * @Route("/media/upload", name="pageMediaUpload")
     */
    public function uploadMedia2Page(\Symfony\Component\HttpFoundation\Request $request) {
        /** @var UploadedFile $uploadedFile */
        $uploadedFile = $request->files->get('file');
        $linkedPost = $request->get('linkedPost');

        if($uploadedFile == null && $linkedPost == null) {
            throw $this->createNotFoundException("Can't find a route for your request.");
        }

        /** @var Post $post */
        $post = $this->getDoctrine()->getRepository(Post::class)->find($linkedPost);

        if(!$post || ($post->getOwner()->getId() != $this->getUser()->getId() && !$this->isGranted("ROLE_ADMIN"))) {
            $this->createAccessDeniedException("You can't upload media to post which don't exists or you aren't the owner of");
        }

        $fileName = md5(uniqid("20") . random_bytes(20)) . $uploadedFile->getClientOriginalExtension();
        $uploadedFile->move("uploads/attachments", $fileName);

        $attachment = new AttachedImage();
        $attachment->setOwner($this->getUser());
        $attachment->setPath($fileName);
        $attachment->setDescription("(disabled)");
        $attachment->setLinkedPost($post);

        $entiyManager = $this->getDoctrine()->getManager();
        $entiyManager->persist($attachment);
        $entiyManager->flush();

        return new JsonResponse([
            "success" => true,
            "location" => "/images/attachment/" . $attachment->getId(),
        ]);
    }

    /**
     * @Route("/pages/", name="pagesList")
     */
    public function listPages(PageRepository $pageRepository) {
        $pages = $pageRepository->findAll();

        return $this->render('admin/pages/pages.list.html.twig', [
            'pages' => $pages
        ]);
    }

    /**
     * @Route("/pages/edit/{id}", name="pageEdit")
     */
    public function editPage($id, PageRepository $pageRepository, \Symfony\Component\HttpFoundation\Request $request) {
        $page = $pageRepository->find($id);

        if(!$page) {
            throw $this->createNotFoundException("Page wasn't found");
        }

        $form = $this->createFormBuilder($page)
            ->add('title', TextType::class, ['label' => "Seitentitel"])
            ->add('slug', TextType::class, ['label' => "URL"])
            ->add('content', TinymceType::class, ['label' => "Inhalt der Seite"])
            ->add('submit', SubmitType::class, ['label' => "Speichern"])
            ->getForm();

        if($form->handleRequest($request) && $form->isSubmitted() && $form->isValid()) {
            /** @var Page $page */
            $page = $form->getData();

            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $this->addFlash('success', "Die Seite wurde erfolgreich bearbeitet");
            return $this->redirectToRoute('pagesList');
        }

        return $this->render('admin/pages/pages.edit.html.twig', [
            'form' => $form->createView(),
            'tinymce' => true
        ]);
    }

    /**
     * @Route("/pages/delete/{id}", name="pageDelete")
     */
    public function deletePage($id, PageRepository $pageRepository) {

        $page = $pageRepository->find($id);

        if(!$page) {
            throw $this->createNotFoundException("Page not found");
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($page);
        $em->flush();

        return $this->redirectToRoute('pagesList');
    }

    /**
     * @Route("/config", name="configSystem")
     */
    public function systemConfig(SettingRepository $settings) {
        $currentSetting = $settings->findOneBy(['name' => 'settings.version']);
        $currentVersion = ($currentSetting !== null) ? $currentSetting->getValue()['currentVersion'] : 0;
        $needsUpdate = ($currentVersion >= Setting::defaultSettings()['version']->getValue()['currentVersion']);
        $needsUpdate = false;

        return $this->render('admin/config/settings.list.html.twig', [
            'needsUpdate' => $needsUpdate,
            'settings' => $settings->findAll()
        ]);
    }

    /**
     * @Route("/config/reset", name="configReset")
     */
    public function resetConfig(Connection $connection, EntityManagerInterface $em) {
        $stmt = $connection->prepare("DELETE from `setting`");
        $stmt->execute();

        $settings = Setting::defaultSettings();
        foreach ($settings as $setting) {
            $em->persist($setting);
        }
        $em->flush();

        return $this->redirectToRoute('configSystem');
    }

    /**
     * @Route("/config/edit/{id}", name="configEdit")
     */
    public function editConfig($id, \Symfony\Component\HttpFoundation\Request $request, SettingRepository $settings) {
        $setting = $settings->find($id);

        if($settings == null) {
            throw $this->createNotFoundException("Setting not found.");
        }

        $form = $this->createFormBuilder($setting);

        if($setting->getType() == "choice") {
            $form->add('value', ChoiceType::class, ['multiple' => true, 'choices' => $setting->getDefaultValue()['choices']]);
        } else if($setting->getType() == "boolean") {
            $form->add('value', RadioType::class, ['choices' => ['Ja.' => true, 'Nö.' => false], 'expanded' => true, 'multiple' => false]);
        } else {
            $form->add('value', TextType::class);
        }

        $form->add('submit', SubmitType::class, ['label' => "Speichern"]);
        $form = $form->getForm();

        if($form->handleRequest($request) && $form->isSubmitted() && $form->isValid()) {
            $setting = $form->getData();

            if($setting->getType() == "choice") {
                $form->add('value', ChoiceType::class, ['multiple' => true, 'choices' => $setting->getDefaultValue()['choices']]);
            } else if($setting->getType() == "boolean") {
                $form->add('value', RadioType::class, ['choices' => ['Ja.' => true, 'Nö.' => false], 'expanded' => true, 'multiple' => false]);
            } else {
                $form->add('value', TextType::class);
            }

            $em = $this->getDoctrine()->getManager();
            $em->flush();
        }

        return $this->render('admin/config/edit.html.twig', [
            'form' => $form->createView(),
            'setting' => $setting
        ]);
    }

    /**
     * @Route("/category/add", name="createCategory")
     */
    public function categoryAdd(\Symfony\Component\HttpFoundation\Request $request, EntityManagerInterface $entityManager) {
        $category = new Category();

        $form = $this->createFormBuilder($category)
            ->add('name', TextType::class, ['label' => "Name der Kategorie"])
            ->add('description', TextareaType::class, ['label' => "Beschreibung der Kategorie"])
            ->add('access', EntityType::class, ['label' => "Benutzer mit Zugriff", "multiple" => true, 'class' => "App:User", 'choice_label' => function(User $user) {
                return $user->getDisplayName();
            }])
            ->add('displayHomepage', CheckboxType::class, ['label' => "Beiträge auf der Homepage anzeigen", 'required' => false])
            ->add('public', CheckboxType::class, ['label' => "Für alle Benutzer nutzbar", 'required' => false])
            ->add('badgeVisible', CheckboxType::class, ['label' => "Kategorie-Badge öffentlich anzeigen.", 'required' => false])
            ->add('submit', SubmitType::class, ['label' => "Erstellen"])
            ->getForm();

        if($form->handleRequest($request) && $form->isSubmitted() && $form->isValid()) {
            /** @var Category $category */
            $category = $form->getData();

            $category->setColor("#a02828");

            $entityManager->persist($category);
            $entityManager->flush();

            $this->addFlash('success', "Die Kategorie wurd erfolgreich angelegt.");
            return $this->redirectToRoute('listCategories');
        }

        return $this->render('admin/categories/create.category.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/categories", name="listCategories")
     */
    public function categoriesList(CategoryRepository $categories) {
        return $this->render('admin/categories/list.categories.html.twig', ['categories' => $categories->findAll()]);
    }

    /**
     * @Route("/category/edit/{id}", name="editCategory")
     */
    public function categoryEdit($id, CategoryRepository $repository, \Symfony\Component\HttpFoundation\Request $request) {
        $category = $repository->find($id);

        if(!$category || $category == null) {
            throw $this->createNotFoundException("Diese Kategorie wurde nicht gefunden.");
        }

        $form = $this->createFormBuilder($category)
            ->add('name', TextType::class, ['label' => "Name der Kategorie"])
            ->add('description', TextareaType::class, ['label' => "Beschreibung der Kategorie"])
            ->add('access', EntityType::class, ['label' => "Benutzer mit Zugriff", 'multiple' => true, 'class' => "App:User", 'choice_label' => function(User $user) {
                return $user->getDisplayName();
            }])
            ->add('displayHomepage', CheckboxType::class, ['label' => "Beiträge auf der Homepage anzeigen", 'required' => false])
            ->add('public', CheckboxType::class, ['label' => "Für alle Benutzer nutzbar", 'required' => false])
            ->add('badgeVisible', CheckboxType::class, ['label' => "Kategorie-Badge öffentlich anzeigen.", 'required' => false])
            ->add('submit', SubmitType::class, ['label' => "Speichern"])
            ->getForm();

        if($form->handleRequest($request) && $form->isSubmitted() && $form->isValid()) {
            /** @var Category $category */
            $category = $form->getData();

            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $this->addFlash('success', "Die Kategorie wurde erfolgreich gespeichert.");
            return $this->redirectToRoute('listCategories');
        }

        return $this->render('admin/categories/edit.category.html.twig', ['form' => $form->createView()]);
    }

}
