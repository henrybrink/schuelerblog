<?php

namespace App\Controller;

use App\Entity\Post;
use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
        $posts = $this->getDoctrine()->getRepository(Post::class)->findBy(array('published' => false, 'denied' => false));
        $published = $this->getDoctrine()->getRepository(Post::class)->findBy(array('published' => true));


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

        $post->setPublished(true);

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

        $was_published = $post->getPublished();

        $post->setPublished(false);
        $post->setDenied(true);
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

}
