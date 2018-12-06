<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AttachedImageRepository")
 */
class AttachedImage
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     */
    private $path;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="attachedImages")
     * @ORM\JoinColumn(nullable=false)
     */
    private $owner;

    /**
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Post", inversedBy="attachedImages")
     * @ORM\JoinColumn(nullable=true)
     */
    private $linkedPost;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Page", inversedBy="attachedImages")
     * @ORM\JoinColumn(nullable=true)
     */
    private $linkedPage;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isIndependent;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(string $path): self
    {
        $this->path = $path;

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): self
    {
        $this->owner = $owner;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getLinkedPost(): ?Post
    {
        return $this->linkedPost;
    }

    public function setLinkedPost(?Post $linkedPost): self
    {
        $this->linkedPost = $linkedPost;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getLinkedPage() {
        return $this->linkedPage;
    }

    /**
     * @param mixed $linkedPage
     */
    public function setLinkedPage($linkedPage): void {
        $this->linkedPage = $linkedPage;
    }

    public function hasPage() {
        return ($this->linkedPage == null);
    }

    public function isIndependent(): ?bool
    {
        return $this->isIndependent;
    }

    public function setIsIndependent(?bool $isIndependent): self
    {
        $this->isIndependent = $isIndependent;

        return $this;
    }
}
