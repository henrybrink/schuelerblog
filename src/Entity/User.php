<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 */
class User implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $username;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Post", mappedBy="Owner", orphanRemoval=true)
     */
    private $posts;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $email;

    /**
     * @ORM\Column(type="text")
     */
    private $displayName;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\AttachedImage", mappedBy="owner", orphanRemoval=true)
     */
    private $attachedImages;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Page", mappedBy="owner", orphanRemoval=true)
     */
    private $pages;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Category", mappedBy="access")
     */
    private $accessibleCategories;

    public function __construct()
    {
        $this->posts = new ArrayCollection();
        $this->attachedImages = new ArrayCollection();
        $this->pages = new ArrayCollection();
        $this->accessibleCategories = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * @return Collection|Post[]
     */
    public function getPosts(): Collection
    {
        return $this->posts;
    }

    public function addPost(Post $post): self
    {
        if (!$this->posts->contains($post)) {
            $this->posts[] = $post;
            $post->setOwner($this);
        }

        return $this;
    }

    public function removePost(Post $post): self
    {
        if ($this->posts->contains($post)) {
            $this->posts->removeElement($post);
            // set the owning side to null (unless already changed)
            if ($post->getOwner() === $this) {
                $post->setOwner(null);
            }
        }

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getDisplayName(): ?string
    {
        return $this->displayName;
    }

    public function setDisplayName(string $displayName): self
    {
        $this->displayName = $displayName;

        return $this;
    }

    /**
     * @return Collection|AttachedImage[]
     */
    public function getAttachedImages(): Collection
    {
        return $this->attachedImages;
    }

    public function addAttachedImage(AttachedImage $attachedImage): self
    {
        if (!$this->attachedImages->contains($attachedImage)) {
            $this->attachedImages[] = $attachedImage;
            $attachedImage->setOwner($this);
        }

        return $this;
    }

    public function removeAttachedImage(AttachedImage $attachedImage): self
    {
        if ($this->attachedImages->contains($attachedImage)) {
            $this->attachedImages->removeElement($attachedImage);
            // set the owning side to null (unless already changed)
            if ($attachedImage->getOwner() === $this) {
                $attachedImage->setOwner(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Page[]
     */
    public function getPages(): Collection
    {
        return $this->pages;
    }

    public function addPage(Page $page): self
    {
        if (!$this->pages->contains($page)) {
            $this->pages[] = $page;
            $page->setOwner($this);
        }

        return $this;
    }

    public function removePage(Page $page): self
    {
        if ($this->pages->contains($page)) {
            $this->pages->removeElement($page);
            // set the owning side to null (unless already changed)
            if ($page->getOwner() === $this) {
                $page->setOwner(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Category[]
     */
    public function getAccessibleCategories(): Collection
    {
        return $this->accessibleCategories;
    }

    public function addAccessibleCategory(Category $accessibleCategory): self
    {
        if (!$this->accessibleCategories->contains($accessibleCategory)) {
            $this->accessibleCategories[] = $accessibleCategory;
            $accessibleCategory->addAccess($this);
        }

        return $this;
    }

    public function removeAccessibleCategory(Category $accessibleCategory): self
    {
        if ($this->accessibleCategories->contains($accessibleCategory)) {
            $this->accessibleCategories->removeElement($accessibleCategory);
            $accessibleCategory->removeAccess($this);
        }

        return $this;
    }
}
