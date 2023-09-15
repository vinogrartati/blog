<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Blog;
use App\Entity\User;
use App\Form\BlogFormType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class BlogController extends AbstractController {
	public function __construct(readonly ManagerRegistry $doctrine, readonly Security $security) {}

	/**
	 * Получение списка блогов.
	 *
	 * @return Response
	 */
	#[Route(path: '/', name: 'blogs_list', methods: ['GET'])]
	public function index(): Response {
		$blogs = $this->doctrine->getRepository(Blog::class)->findAll();

		return $this->render('blog/index.html.twig', ['blogs' => $blogs]);
	}

	/**
	 * Создание блога.
	 *
	 * @param Request $request Запрос с формой
	 *
	 * @return Response
	 */
	#[Route(path: 'blog/new', name: 'new_blog', methods: ['GET', 'POST'])]
	public function newBlog(Request $request): Response {
		$blog = new Blog();

		$user = $this->security->getUser(); /** @var User $user */
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
	 * Редактирование блога.
	 *
	 * @param Request $request  Запрос с формой
	 * @param string  $name     Url-имя блога
	 *
	 * @return Response
	 */
	#[Route(path: 'blog/{name}/edit/', name: 'edit_blog', methods: ['GET', 'POST'])]
	public function editBlog(Request $request, string $name): Response {
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
	 * Удалить блог
	 *
	 * @param string $id Id блога
	 *
	 * @return void
	 */
	#[Route(path: 'blog/delete/{id}/', name: 'delete_blog', methods: ['DELETE'])]
	public function deleteBlog(string $id): void { // todo-15.09.2023-vinogradova.tv надо доработать
		$blog = $this->doctrine->getRepository(Blog::class)->find($id); /** @var Blog $blog */
		if ($blog->getOwnerId() !== ($this->security->getUser())->getId()) {
			return;
		}

		$entityManager = $this->doctrine->getManager();
		$entityManager->remove($blog);
		$entityManager->flush();

		$response = new Response();
		$response->send();
	}

	/**
	 * Главная страница блога
	 *
	 * @param string $name Url-имя блога
	 *
	 * @return Response
	 */
	#[Route(path: 'blog/{name}', name: 'blog_show')]
	public function show(string $name): Response {
		$blog = $this->doctrine->getRepository(Blog::class)->findOneBy(['urlName' => $name]);

		if (null === $blog) {
			throw new NotFoundHttpException('Блог не найден');
		}
		$articles = $this->doctrine->getRepository(Article::class)->findBy(['blogId' => $blog->getId()]);

		return $this->render('blog/show.html.twig', ['blog' => $blog, 'articles' => $articles,]);
	}
}