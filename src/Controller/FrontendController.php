<?php

namespace App\Controller;

use App\Entity\AttachedImage;
use App\Entity\Post;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;

class FrontendController extends AbstractController
{
    /**
     * @Route("/", name="frontend")
     */
    public function index() {
        $posts = $this->getDoctrine()->getRepository(Post::class)->findBy(array('published' => true), ['date' => 'DESC']);
        return $this->render('frontend/index.template.html.twig', ['posts' => $posts]);
    }

    /**
     * @Route("/posts/{slug}", name="showPost")
     */
    public function viewPost($slug) {
        $post = $this->getDoctrine()->getRepository(Post::class)->findOneBy(array('slug' => $slug));

        if(!$post || !$post->getPublished()) {
            throw $this->createNotFoundException("This post can't be found");
        }

        return $this->render("frontend/post.view.html.twig", array("post" => $post));
    }

    /**
     * @Route("/images/post_thumpnail/{post}", name="loadPostImage")
     */
    public function viewImage($post) {

        $post = $this->getDoctrine()->getRepository(Post::class)->find($post);

        if(!$post || (!$post->getPublished() && !$this->isGranted("ROLE_MOD")) || !$post->getImage()) {
            throw $this->createNotFoundException("Post not found.");
        }

        $response = new Response(file_get_contents('uploads/images/'.$post->getImage()));

        $dispositon = $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_INLINE, $post->getImage());

        $response->headers->set("Content-Disposition", $dispositon);

        return $response;
    }

    /**
     * @Route("/images/attachment/{id}", name="loadAttachedImage")
     */
    public function loadAttachedImage($id) {

        /** @var AttachedImage $post */
        $post = $this->getDoctrine()->getRepository(AttachedImage::class)->find($id);

        if(!$post || (!$post->getLinkedPost()->getPublished() && !$this->isGranted("IS_AUTHENTICATED_FULLY"))) {
            throw $this->createNotFoundException("Attachment not found.");
        }

        $response = new Response(file_get_contents('uploads/attachments/'.$post->getPath()));

        $dispositon = $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_INLINE, $post->getPath());

        $response->headers->set("Content-Disposition", $dispositon);

        return $response;
    }

    /**
     * @Route("/{page}", name="showPage")
     */
    public function pageShow($page) {
    }
}
