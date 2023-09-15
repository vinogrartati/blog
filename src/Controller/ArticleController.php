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
	 * Создание статьи.
	 *
	 * @param Request $request  Запрос с формой
	 * @param string  $blogName Url блога
	 *
	 * @return Response
	 */
	#[Route(path: '/article/new', name: 'new_article', methods: ['GET', 'POST'])]
	public function newArticle(Request $request, string $blogName): Response {
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

			return $this->redirectToRoute('blog_show', ['name' => $blogName]);
		}

		return $this->render('articles/new.html.twig', ['form' => $form->createView()]);
	}

	/**
	 * Редактирование статьи.
	 *
	 * @param Request $request  Запрос с формой
	 * @param int     $id       Id статьи
	 * @param string  $blogName Url блога
	 *
	 * @return Response
	 */
	#[Route(path: '/article/edit/{id}', name: 'edit_article', methods: ['GET', 'POST'])]
	public function editArticle(Request $request, int $id, string $blogName): Response {
		$article = $this->doctrine->getRepository(Article::class)->find($id);

		$form = $this->createForm(ArticleFormType::class, $article);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {

			$entityManager = $this->doctrine->getManager();
			$entityManager->flush();

			return $this->redirectToRoute('blog_show', ['name' => $blogName]);
		}

		return $this->render('articles/edit.html.twig', ['form' => $form->createView()]);
	}

	/**
	 * Удаление статьи.
	 *
	 * @param int    $id       Id статьи
	 * @param string $blogName Url блога
	 *
	 * @return Response
	 */
	#[Route(path: '/article/delete/{id}', name: 'delete_article', methods: ['POST'])]
	public function deleteArticle(int $id, string $blogName): Response { // todo-15.09.2023-vinogradova.tv добавить подтверждение удаления и модалку успешного удаления
		$article = $this->doctrine->getRepository(Article::class)->find($id);

		$entityManager = $this->doctrine->getManager();
		$entityManager->remove($article);
		$entityManager->flush();

		return $this->redirectToRoute('blog_show', ['name' => $blogName]);
	}

	/**
	 * Показать статью
	 *
	 * @param int $id Id статьи
	 *
	 * @return Response
	 */
	#[Route(path: '/article/{id}', name: 'show_article')]
	public function show(int $id): Response {
		$article = $this->doctrine->getRepository(Article::class)->find($id);

		return $this->render('articles/show.html.twig', ['article' => $article]);
	}
}