<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PostRepository")
 */
class Post
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
    private $title;

    /**
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $content;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="posts")
     * @ORM\JoinColumn(nullable=false)
     */
    private $Owner;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $slug;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $options = [];


    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $proposed_changes = [];

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $date;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $image;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\AttachedImage", mappedBy="linkedPost", orphanRemoval=true)
     */
    private $attachedImages;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Comment", mappedBy="linkedPost", orphanRemoval=true)
     */
    private $comments;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Category", inversedBy="posts")
     */
    private $category;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $showThumpnail;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private $status;

    public function __construct()
    {
        $this->attachedImages = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->status = "saved";
    }

    /* Status */
    public function isPublished() {
        return ($this->status == "public");
    }

    public function isQueued() {
        return ($this->status == "inQueue");
    }

    public function isSaved() {
        return ($this->status == "saved" || $this->status == null);
    }

    public function isDenied() {
        return ($this->status == "denied");
    }

    public function queue() {
        $this->status = "inQueue";
    }

    public function approve() {
        $this->status = "public";
    }

    public function deny() {
        $this->status = "denied";
    }

    public function getStatus() {
        return $this->status;
    }

    /* End status section */

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

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

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->Owner;
    }

    public function setOwner(?User $Owner): self
    {
        $this->Owner = $Owner;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getOptions(): ?array
    {
        return $this->options;
    }

    public function setOptions(array $options): self
    {
        $this->options = $options;

        return $this;
    }

    public function getProposedChanges(): ?array
    {
        return $this->proposed_changes;
    }

    public function setProposedChanges(?array $proposed_changes): self
    {
        $this->proposed_changes = $proposed_changes;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getImage()
    {
        return $this->image;
    }

    public function setImage($image): self
    {
        $this->image = $image;

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
            $attachedImage->setLinkedPost($this);
        }

        return $this;
    }

    public function removeAttachedImage(AttachedImage $attachedImage): self
    {
        if ($this->attachedImages->contains($attachedImage)) {
            $this->attachedImages->removeElement($attachedImage);
            // set the owning side to null (unless already changed)
            if ($attachedImage->getLinkedPost() === $this) {
                $attachedImage->setLinkedPost(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Comment[]
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments[] = $comment;
            $comment->setLinkedPost($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->contains($comment)) {
            $this->comments->removeElement($comment);
            // set the owning side to null (unless already changed)
            if ($comment->getLinkedPost() === $this) {
                $comment->setLinkedPost(null);
            }
        }

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getShowThumpnail(): ?bool
    {
        return $this->showThumpnail;
    }

    public function setShowThumpnail(?bool $showThumpnail): self
    {
        $this->showThumpnail = $showThumpnail;

        return $this;
    }


}
