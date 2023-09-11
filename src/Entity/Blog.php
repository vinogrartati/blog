<?php

namespace App\Entity;

use App\Repository\BlogRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BlogRepository::class)]
class Blog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    public function getId(): ?int
    {
        return $this->id;
    }

	#[ORM\Column]
	private string $title;

	#[ORM\Column]
	private string $urlName;

	#[ORM\Column(type: Types::INTEGER)]
	private int $ownerId;

	#[ORM\Column(type: Types::TEXT, nullable: true)]
	private ?string $about = null;

	public function getTitle(): ?string {
		return $this->title;
	}

	public function setTitle($title): void {
		$this->title = $title;
	}

	public function getUrlName(): ?string {
		return $this->urlName;
	}

	public function setUrlName($urlName): void {
		$this->urlName = $urlName;
	}

	public function getAbout(): ?string {
		return $this->about;
	}

	public function setAbout($about): void {
		$this->about = $about;
	}

	public function getOwnerId(): int {
		return $this->ownerId;
	}

	public function setOwnerId($id): void {
		$this->ownerId = $id;
	}
}
