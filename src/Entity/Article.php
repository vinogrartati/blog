<?php

namespace App\Entity;

use App\Repository\ArticleRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity(repositoryClass: ArticleRepository::class)]
class Article
{
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column]
    private ?int $id = null;

	#[ORM\Column]
	private ?string $title = null;

	#[ORM\Column(type: Types::TEXT, nullable: true)]
	private ?string $body = null;

	#[ORM\Column(type: Types::INTEGER)]
	private int $blogId;

    public function getId(): ?int {
        return $this->id;
    }

	public function getBlogId(): int {
		return $this->blogId;
	}

	public function setBlogId($blogId): void {
		$this->blogId = $blogId;
	}

	public function getTitle(): ?string {
		return $this->title;
	}

	public function setTitle($title): void {
		$this->title = $title;
	}

	public function getBody(): ?string {
		return $this->body;
	}

	public function setBody($body): void {
		$this->body = $body;
	}
}
