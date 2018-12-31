<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\TinymceType;
use App\Repository\CommentRepository;
use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * Class ModerationController
 * @package App\Controller
 * @Route("/dashboard/moderation")
 * @IsGranted("ROLE_MOD")
 */
class ModerationController extends AbstractController {

    /**
     * @Route("/posts/", name="listPosts")
     */
    public function viewPosts() {
        $posts = $this->getDoctrine()->getRepository(Post::class)->findBy(array('status' => "inQueue"));
        $published = $this->getDoctrine()->getRepository(Post::class)->findBy(array('status' => "public"));

        return $this->render('dashboard/mod/mod.posts.html.twig', array("posts" => $posts, 'published_posts' => $published));
    }

    /**
     * @Route("/posts/check/{id}", name="checkPost")
     */
    public function postCheck($id) {
        $post = $this->getDoctrine()->getRepository(Post::class)->find($id);

        if(!$post) {
            $this->createNotFoundException("This post couldn't be found");
        }

        return $this->render("dashboard/mod/mod.posts.check.html.twig", array("post" => $post));
    }

    /**
     * @Route("/posts/approve/{id}", name="postApprove")
     */
    public function postApprove($id) {
        $post = $this->getDoctrine()->getRepository(Post::class)->find($id);

        if(!$post) {
            $this->createNotFoundException("This post couldn't be found");
        }

        $post->approve();

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->flush();

        $this->addFlash("success", "Der Beitrag wurde freigeschaltet.");

        return RedirectResponse::create("/dashboard/moderation/posts");
    }

    /**
     * @Route("/posts/deny/{id}", name="postDeny")
     */
    public function deny($id, Request $request, PostRepository $postRepository) {

        $post = $postRepository->find($id);

        if(!$post) {
            throw $this->createNotFoundException("Referenced Post couldn't be found.");
        }

        $was_published = $post->isPublished();

        $post->deny();
        $post->setOptions([
            "reason" => $request->get('reason')
        ]);

        if($was_published) {
            $post->setOptions([
                'reason' => $request->get('reason'),
                'was_published' => true
            ]);
        }

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        $this->addFlash("success", "Der Beitrag wurde abgelehnt.");

        return $this->redirectToRoute("checkPost", [
            "id" => $id
        ]);
    }

    /**
     * @Route("/posts/edit/{id}", name="modEditPost")
     */
    public function editPost($id, PostRepository $posts, Request $request) {
        $post = $posts->find($id);

        if(!$post) {
            $this->createAccessDeniedException("Post not found.");
        }

        $form = $this->createFormBuilder($post)
            ->add('title', TextType::class, ['label' => "Titel"])
            ->add('slug', TextType::class, ['label' => "URL"])
            ->add('description', TextareaType::class, ['label' => 'Beschreibung'])
            ->add('content', TinymceType::class, ['label' => 'Inhalt'])
            ->add('summary', TextareaType::class, ['label' => "Zusammenfassung deiner Änderungen", "mapped" => false])
            ->add('submit', SubmitType::class, ['label' => "Aktualisieren"])
            ->getForm();

        if($form->handleRequest($request) && $form->isSubmitted() && $form->isValid()) {
            /** @var Post $post */
            $post = $form->getData();

            $post->setOptions([
                'edited_mod' => true,
                'editor' => $this->getUser()->getId(),
                'edit_summary' => $form->get('summary')->getData()
            ]);

            $em = $this->getDoctrine()->getManager();
            $em->flush();
            $this->addFlash('success', 'Der Beitrag / die Seite wurde erfolgreich bearbeitet');
        }

        return $this->render("dashboard/mod/post.edit.html.twig", [
            'form' => $form->createView(),
            'tinymce' => true
        ]);
    }

    /**
     * @Route("/comments", name="commentsList")
     */
    public function listComments(CommentRepository $commentRepository) {
        $comments = $commentRepository->findBy(['public' => false], ['date' => "ASC"]);

        return $this->render('dashboard/mod/mod.comments.html.twig', [
            'comments' => $comments
        ]);
    }

    /**
     * @Route("/comments/approve/{id}", name="approveComment")
     */
    public function commentApprove($id, CommentRepository $commentRepository) {
        $comment = $commentRepository->find($id);

        if(!$comment) {
            throw $this->createNotFoundException("Comment not found.");
        }

        $comment->setPublic(true);
        $em = $this->getDoctrine()->getManager();
        $em->flush();

        $this->addFlash('success', "Der Kommentar wurde freigeschaltet.");

        return $this->redirect("/dashboard/moderation/comments");
    }

    /**
     * @Route("/comments/deny/{id}", name="deleteComment")
     */
    public function commentDelete($id, Request $request, CommentRepository $commentRepository) {
        $comment = $commentRepository->find($id);

        if(!$comment) {
            throw $this->createNotFoundException("Comment not found.");
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($comment);
        $em->flush();

        $this->addFlash('success', "Der Kommentar wurde gelöscht.");

        return $this->redirect($request->headers->get('referer'));
    }


}
