<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Blog;
use App\Form\ArticleFormType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class ArticleController extends AbstractController {

	public function __construct(readonly ManagerRegistry $doctrine) {}
	/**
	 * @Route("/articles", methods={"GET"}, name="article_list")
	 * @return Response
	 */
	public function index($blogName) {
		$blog     = $this->doctrine->getRepository(Blog::class)->findOneBy(['urlName' => $blogName]);
		$articles = $this->doctrine->getRepository(Article::class)->findBy(['blogId' => $blog->getId()]);

		return $this->render('articles/index.html.twig', ['articles' => $articles]);
	}

	/**
	 * @Route("/article/new", methods={"GET", "POST"}, name="new_article")
	 */
	public function newArticle(Request $request, $blogName) {
		$blog = $this->doctrine->getRepository(Blog::class)->findOneBy(['urlName' => $blogName]);

		$article = new Article();
		$article->setBlogId($blog->getId());
		$form = $this->createForm(ArticleFormType::class, $article);

		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$article = $form->getData();

			$entityManager = $this->doctrine->getManager();
			$entityManager->persist($article);
			$entityManager->flush();

			return $this->redirectToRoute('article_list', ['blogName' => $blogName]);
		}

		return $this->render('articles/new.html.twig', ['form' => $form->createView()]);
	}

	/**
	 * @Route("article/edit/{id}", methods={"GET", "POST"}, name="edit_article")
	 */
	public function editArticle(Request $request, $id) {
		$article = $this->doctrine->getRepository(Article::class)->find($id);

		$form = $this->createForm(ArticleFormType::class, $article);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {

			$entityManager = $this->doctrine->getManager();
			$entityManager->flush();

			return $this->redirectToRoute('article_list', ['blogName' => $blogName]); // todo-14.09.2023-vinogradova.tv добавить
		}

		return $this->render('articles/edit.html.twig', ['form' => $form->createView()]);
	}

	/**
	 * @Route("/article/delete/{id}", methods={"DELETE"}, name="delete_article")
	 */
	public function deleteArticle(Request $request, $id) {
		$article = $this->doctrine->getRepository(Article::class)->find($id);

		$entityManager = $this->doctrine->getManager();
		$entityManager->remove($article);
		$entityManager->flush();

		$response = new Response();
		$response->send();
	}

	/**
	 * @Route("/article/{id}", name="show_article")
	 */
	public function show($id) {
		$article = $this->doctrine->getRepository(Article::class)->find($id);

		return $this->render('articles/show.html.twig', ['article' => $article]);
	}
}