<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Blog;
use App\Entity\User;
use App\Form\BlogFormType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class BlogController extends AbstractController {
	public function __construct(readonly ManagerRegistry $doctrine, readonly Security $security) {}
	/**
	 * @Route("/", methods={"GET"}, name="blogs_list")
	 * @return Response
	 */
	public function index() {
		$blogs = $this->doctrine->getRepository(Blog::class)->findAll();

		return $this->render('blog/index.html.twig', ['blogs' => $blogs]);
	}

	/**
	 * @Route("blog/new", methods={"GET", "POST"}, name="new_blog")
	 */
	public function newBlog(Request $request) {
		$blog = new Blog();

		$user = $this->security->getUser(); /** @var User $user */ // todo-06.09.2023-vinogradova.tv насколько это правильно ?
		if (null === $user) {
			return $this->redirectToRoute('app_login');
		}

		$blog->setOwnerId($user->getId());
		$form = $this->createForm(BlogFormType::class, $blog);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$blog = $form->getData();
			$entityManager = $this->doctrine->getManager();
			$entityManager->persist($blog);
			$entityManager->flush();

			return $this->redirectToRoute('blog_show', ['name' => $blog->getUrlName()]);
		}

		return $this->render('blog/new.html.twig', ['form' => $form->createView()]);
	}

	/**
	 * @Route("blog/{name}/edit/", methods={"GET", "POST"}, name="edit_blog")
	 */
	public function editBlog(Request $request, $name) {
		$blog = $this->doctrine->getRepository(Blog::class)->findOneBy(['urlName' => $name]);

		$form = $this->createForm(BlogFormType::class, $blog);

		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {

			$entityManager = $this->doctrine->getManager();
			$entityManager->flush();

			return $this->redirectToRoute('blog_show', ['name' => $blog->getUrlName()]);
		}

		return $this->render('blog/edit.html.twig', ['form' => $form->createView()]);
	}

	/**
	 * @Route("/blog/delete/{id}", methods={"DELETE"})
	 */
	public function deleteBlog(Request $request, $id) {
		$blog = $this->doctrine->getRepository(Blog::class)->find($id);

		$entityManager = $this->doctrine->getManager();
		$entityManager->remove($blog);
		$entityManager->flush();

		$response = new Response();
		$response->send();
	}
//	/**
//	 * @Route("/blog/save")
//	 */
//	public function save() {
//		$entityManager = $this->doctrine->getManager();
//
//		$article = new Article();
//		$article->setTitle('Article Two');
//		$article->setBody('This is the body for article Two');
//
//		$entityManager->persist($article);
//
//		$entityManager->flush();
//
//		return new Response('Saved an article with the id of ' . $article->getId());
//	}

	/**
	 * @Route("/blog/{name}", name="blog_show")
	 */
	public function show($name) {
		$blog = $this->doctrine->getRepository(Blog::class)->findOneBy(['urlName' => $name]);
		if (null === $blog) {
			throw new NotFoundHttpException('Блог не найден');
		}
		$articles = $this->doctrine->getRepository(Article::class)->findBy(['blogId' => $blog->getId()]);

		return $this->render('blog/show.html.twig', ['blog' => $blog, 'articles' => $articles,]);
	}
}