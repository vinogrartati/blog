<?php

namespace App\Controller;

use App\Entity\Article;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class ArticleController extends AbstractController {

	public function __construct(readonly ManagerRegistry $doctrine) {}
	/**
	 * @Route("/articles", methods={"GET"}, name="article_list") // todo-06.09.2023-vinogradova.tv почему тут блог???
	 * @return Response
	 */
	public function index($blogName) {
		$blog = $this->doctrine->getRepository(Article::class)->findOneBy(['urlName' => $blogName]);
		$articles = $this->doctrine->getRepository(Article::class)->findBy(['blogId' => $blog->getId()]);

		return $this->render('articles/index.html.twig', ['articles' => $articles]);
	}

	/**
	 * @Route("/article/new", methods={"GET", "POST"}, name="new_article")
	 */
	public function newArticle(Request $request, $blogName) {
		$blog = $this->doctrine->getRepository(Article::class)->findOneBy(['url_name' => $blogName]);

		$article = new Article();

		$form = $this->createFormBuilder($article)
			->add('blogId', HiddenType::class, ['attr' => ['class' => 'form-control', 'value' => $blog->getId()]])
			->add('title', TextType::class,    ['attr' => ['class' => 'form-control']])
			->add('body', TextareaType::class, ['required' => false, 'attr' => ['class' => 'form-control']])
			->add('save', SubmitType::class,   ['label' => 'Create', 'attr' => ['class' => 'btn btn-primary mt-3']])
			->getForm()
		;

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
	public function editArticle(Request $request, $id) { // todo-08.09.2023-vinogradova.tv добавить блог
		$article = $this->doctrine->getRepository(Article::class)->find($id);

		$form = $this->createFormBuilder($article)
			 ->add('title', TextType::class, ['attr' => ['class' => 'form-control']])
			 ->add('body', TextareaType::class, ['required' => false, 'attr' => ['class' => 'form-control']])
			 ->add('save', SubmitType::class, ['label' => 'Update', 'attr' => ['class' => 'btn btn-primary mt-3']])
			 ->getForm()
		;

		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {

			$entityManager = $this->doctrine->getManager();
			$entityManager->flush();

			return $this->redirectToRoute('article_list');
		}

		return $this->render('articles/edit.html.twig', ['form' => $form->createView()]);
	}

	/**
	 * @Route("/article/delete/{id}", methods={"DELETE"})
	 */
	public function deleteArticle(Request $request, $id) { // todo-08.09.2023-vinogradova.tv добавить блог
		$article = $this->doctrine->getRepository(Article::class)->find($id);

		$entityManager = $this->doctrine->getManager();
		$entityManager->remove($article);
		$entityManager->flush();

		$response = new Response();
		$response->send();
	}
	/**
	 * @Route("/article/save", methods={"POST"}) // todo-08.09.2023-vinogradova.tv это неправильный сэйв!
	 */
	public function save($blogName) {
		$blog = $this->doctrine->getRepository(Article::class)->findOneBy(['urlName' => $blogName]);

		$entityManager = $this->doctrine->getManager();

		$article = new Article();
		$article->setTitle('Article Two');
		$article->setBody('This is the body for article Two');
		$article->setBlogId($blog->getId());

		$entityManager->persist($article);

		$entityManager->flush();

		return new Response('Saved an article with the id of ' . $article->getId());
	}

	/**
	 * @Route("/article/{id}", name="article_show")
	 */
	public function show($id) {
		$article = $this->doctrine->getRepository(Article::class)->find($id);

		return $this->render('articles/show.html.twig', ['article' => $article]);
	}
}