<?php

namespace App\Controller;

use App\Entity\AttachedImage;
use App\Entity\Category;
use App\Entity\Page;
use App\Entity\Post;
use App\Entity\User;
use App\Form\TinymceType;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class DashboardController extends AbstractController
{
    /**
     * @Route("/dashboard", name="dashboard")
     */
    public function index(PostRepository $posts) {

        // Check a few values for admins:
        $debugMode = (getenv("APP_ENV") == "dev") ? true : false;

        $denied_posts = $posts->findBy(['status' => 'denied', 'Owner' => $this->getUser()], ['date' => 'DESC']);

        if($this->isGranted("ROLE_MOD")) {
            return $this->render('dashboard/index.html.twig', [
                'controller_name' => 'DashboardController',
                'debug_mode' => $debugMode,
                'denied_posts' => $denied_posts,
                'denied_size' => count($denied_posts),
                'mod_posts' => $posts->findBy(['status' => 'inQueue'])
            ]);
        }

        return $this->render('dashboard/index.html.twig', [
            'controller_name' => 'DashboardController',
            'debug_mode' => $debugMode,
            'denied_posts' => $denied_posts,
            'denied_size' => count($denied_posts),
        ]);
    }

    /**
     * @Route("/dashboard/posts/add", name="addPost")
     * @IsGranted("ROLE_EDITOR")
     */
    public function addPost(Request $request) {

        $post = new Post();
        $post->setOwner($this->getUser());

        $form = $this->createFormBuilder($post)
            ->add('title', TextType::class, array('label' => "Titel deines Beitrages"))
            ->add('description', TextareaType::class, array('label' => "Kurze Beschreibung deines Artikels (feed)"))
            ->add('submit', SubmitType::class, array('label' => 'Weiter'))
            ->getForm();

        if($form->handleRequest($request) && $form->isSubmitted() && $form->isValid()) {

            /** @var Post $post */
            $post = $form->getData();

            $post->setContent(strip_tags($post->getContent(), "<h1><h2><h3><h4><h5><h6><p><br><span><strike><b><bold><i><b><div><table><em><li><img>"));
            $post->setDate(new \DateTime());
            $post->setSlug(strtolower(str_replace([" ", "ä", "ü", "ö", "ß", "ẞ", "\\", "\"", "_", "/"], ["-", "ae", "ue", "oe", "ss", "s", "", "", "-", "-"], $post->getTitle())));

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($post);
            $entityManager->flush();

            return RedirectResponse::create("/dashboard/posts/addContent/{$post->getId()}");
        }


        return $this->render('dashboard/posts/posts.create.html.twig', array(
            'form' => $form->createView(),
            'tinymce' => false,
            'post' => $post,
            'current' => "home"
        ));
    }

    /**
     * @Route("/dashboard/posts/preview/{slug}", name="viewPost")
     */
    public function previewPost($slug) {

        $post = $this->getDoctrine()->getRepository(Post::class)->findOneBy(array('slug' => $slug));

        if(!$post) {
            throw $this->createNotFoundException("Eh. Post not found");
        }

        return $this->render('dashboard/posts/post.render.html.twig', array('post' => $post));
    }

    /**
     * @Route("/dashboard/posts/list/my", name="loadPosts")
     */
    public function myPosts() {

        $posts = $this->getDoctrine()->getRepository(Post::class)->findBy(array('Owner' => $this->getUser()));

        return $this->render("dashboard/posts/post.list.html.twig", array('posts' => $posts));
    }

    /**
     * @Route("/dashboard/posts/delete/{id}", name="postDelete")
     */
    public function deletePost($id) {

        $post = $this->getDoctrine()->getRepository(Post::class)->find($id);

        if(!$post) {
            throw $this->createNotFoundException("Post not found");
        }

        if(!($post->getOwner() == $this->getUser()) && !$this->isGranted("ROLE_ADMIN")) {
            throw $this->createAccessDeniedException("You can't delete that post.");
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($post);
        $entityManager->flush();

        return RedirectResponse::create("/dashboard/posts/list/my");
    }

    /**
     * @Route("/dashboard/posts/addContent/{post}", name="postAddContent")
     */
    public function addContent($post, Request $request) {

        /** @var Post $post */
        $post = $this->getDoctrine()->getRepository(Post::class)->find($post);

        if(!$post || $post->getOwner() != $this->getUser() || $post->isPublished() == true) {
            throw $this->createNotFoundException("Post not found.");
        }

        $form = $this->createFormBuilder($post)
            ->add('content', TinymceType::class, array("label" => "Der Text deines Artikels (*)"))
            ->add("category", EntityType::class, ['label' => "Kategorie des Beitrages", "class" => "App:Category", 'choice_label' => 'name', 'query_builder' => function(EntityRepository $repository) {
                return $repository->createQueryBuilder('c')
                    ->where(':user MEMBER of c.access')
                    ->orWhere('c.public = true')
                    ->orderBy('c.name', 'ASC')
                    ->setParameter('user', $this->getUser());
            }])
            ->add('queue', CheckboxType::class, ['mapped' => false, 'label' => "Beitrag einreichen (erst dann wird dieser Beitrag Moderatoren angezeigt), danach kannst du den Beitrag nicht mehr bearbeiten", 'required' => false]);

        if($post->getImage() == null) {
            $form->add("image", FileType::class, array('label' => "Beitragsbild (*)", 'data_class' => null) );
        }

        $hasImage = $post->getImage() != null;

        $form->add('submit', SubmitType::class, ['label' => "Erstellen"]);
        $form = $form->getForm();

        if($form->handleRequest($request) && $form->isSubmitted() && $form->isValid()) {
            $post = $form->getData();

            if(!$hasImage) {
                $file = $post->getImage();
                $fileName = md5(uniqid() . md5(random_bytes(20))).'.'.$file->guessExtension();

                $file->move(
                    'uploads/images',
                    $fileName
                );
                $post->setImage($fileName);
            }

            if($form['queue']->getData() == "1") {
                $post->queue();
            }


            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->flush();

            return RedirectResponse::create("/dashboard/posts/list/my");
        }

        return $this->render("dashboard/posts/posts.create.html.twig", array(
           "form" => $form->createView(),
            'tinymce' => true,
            'post' => $post
        ));
    }

    /**
     * @Route("/dashboard/media/upload/", name="uploadMediaPost")
     */
    public function uploadMedia(Request $request) {

        /** @var UploadedFile $uploadedFile */
        $uploadedFile = $request->files->get('file');
        $linkedPost = $request->get('linkedPost');

        if($uploadedFile == null && $linkedPost == null) {
            throw $this->createNotFoundException("Can't find a route for your request.");
        }

        /** @var Page $post */
        $post = $this->getDoctrine()->getRepository(Page::class)->find($linkedPost);

        $fileName = md5(uniqid("20") . random_bytes(20)) . $uploadedFile->getClientOriginalExtension();
        $uploadedFile->move("uploads/attachments", $fileName);

        $attachment = new AttachedImage();
        $attachment->setOwner($this->getUser());
        $attachment->setPath($fileName);
        $attachment->setDescription("(disabled)");
        $attachment->setLinkedPage($post);

        $entiyManager = $this->getDoctrine()->getManager();
        $entiyManager->persist($attachment);
        $entiyManager->flush();

        return new JsonResponse([
            "success" => true,
            "location" => "/images/attachment/" . $attachment->getId(),
        ]);
    }

    /**
     * @Route("/dashboard/posts/updateContent/{id}", name="updateContent")
     */
    public function updatePostContent($id, PostRepository $posts, Request $request) {

        $post = $posts->find($id);

        if(!$post || $post->getOwner() != $this->getUser()) {
            throw $this->createNotFoundException("Post not found.");
        }

        $form = $this->createFormBuilder($post)
            ->add('content', TinymceType::class, array("label" => "Der Text deines Artikels (*)"))
            ->add('updatedMessage', TextareaType::class, [
                'mapped' => false,
                'label' => "Beschreibe deinen Änderungen"
            ])
            ->add('submit', SubmitType::class, array('label' => "Beitrag einreichen"))
            ->getForm();

        if($form->handleRequest($request) && $form->isSubmitted() && $form->isValid()) {

            /** @var Post $post */
            $post = $form->getData();
            $post->queue();

            $post->setOptions([
                "resubmitted" => true,
                "message" => $form->get("updatedMessage")->getData()
            ]);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->flush();

            return $this->redirectToRoute("loadPosts");
        }

        return $this->render("dashboard/posts/posts.create.html.twig", array(
            "form" => $form->createView(),
            'tinymce' => true,
            'post' => $post,
            'update' => true
        ));

    }

    /**
     * @Route("/dashboard/user/settings", name="settingsUser")
     */
    public function userSettings(Request $request, UserPasswordEncoderInterface $passwordEncoder) {
        $form = $this->createFormBuilder($this->getUser())
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => ['label' => "Passwort"],
                'second_options' => ['label' => "Passwort wiederholen"]
            ])
            ->add('submit', SubmitType::class, ['label' => "Passwort ändern"])
            ->getForm();

        if($form->handleRequest($request) && $form->isSubmitted() && $form->isValid()) {
            /** @var User $user */
            $user = $form->getData();

            $user->setPassword($passwordEncoder->encodePassword($user, $user->getPassword()));

            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $this->addFlash('success', "Dein Passwort wurde erfolgreich geändert.");
        }

        return $this->render('dashboard/user.settings.html.twig', [
            'form' => $form->createView()
        ]);
    }

}
