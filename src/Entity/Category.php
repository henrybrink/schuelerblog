<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CategoryRepository")
 */
class Category
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\User", inversedBy="accessibleCategories")
     */
    private $access;

    /**
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $color;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\post", mappedBy="category")
     */
    private $posts;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $displayHomepage;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $public;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $badgeVisible;

    public function __construct()
    {
        $this->access = new ArrayCollection();
        $this->posts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getAccess(): Collection
    {
        return $this->access;
    }

    public function addAccess(User $access): self
    {
        if (!$this->access->contains($access)) {
            $this->access[] = $access;
        }

        return $this;
    }

    public function removeAccess(User $access): self
    {
        if ($this->access->contains($access)) {
            $this->access->removeElement($access);
        }

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

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): self
    {
        $this->color = $color;

        return $this;
    }

    /**
     * @return Collection|post[]
     */
    public function getPosts(): Collection
    {
        return $this->posts;
    }

    public function addPost(post $post): self
    {
        if (!$this->posts->contains($post)) {
            $this->posts[] = $post;
            $post->setCategory($this);
        }

        return $this;
    }

    public function removePost(post $post): self
    {
        if ($this->posts->contains($post)) {
            $this->posts->removeElement($post);
            // set the owning side to null (unless already changed)
            if ($post->getCategory() === $this) {
                $post->setCategory(null);
            }
        }

        return $this;
    }

    public function getDisplayHomepage(): ?bool
    {
        return $this->displayHomepage;
    }

    public function setDisplayHomepage(?bool $displayHomepage): self
    {
        $this->displayHomepage = $displayHomepage;

        return $this;
    }

    public function getPublic(): ?bool
    {
        return $this->public;
    }

    public function setPublic(?bool $public): self
    {
        $this->public = $public;

        return $this;
    }

    public function getBadgeVisible(): ?bool
    {
        return $this->badgeVisible;
    }

    public function setBadgeVisible(?bool $badgeVisible): self
    {
        $this->badgeVisible = $badgeVisible;

        return $this;
    }
}
