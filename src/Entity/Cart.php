<?php

namespace App\Entity;

use App\Repository\CartRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity(repositoryClass=CartRepository::class)
 */
class Cart
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="carts")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateBuy;

    /**
     * @ORM\Column(type="boolean")
     * @Assert\NotBlank
     */
    private $status;

    /**
     * @ORM\OneToMany(targetEntity=CartContent::class, mappedBy="cart", orphanRemoval=true)
     */
    private $cartContents;

    public function __construct()
    {
        $this->cartContents = new ArrayCollection();
        $this->status = false;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getDateBuy(): ?\DateTimeInterface
    {
        return $this->dateBuy;
    }

    public function setDateBuy(?\DateTimeInterface $dateBuy): self
    {
        $this->dateBuy = $dateBuy;

        return $this;
    }

    public function getStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(bool $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return Collection|CartContent[]
     */
    public function getCartContents(): Collection
    {
        return $this->cartContents;
    }

    public function addCartContent(CartContent $cartContent): self
    {
        if (!$this->cartContents->contains($cartContent)) {
            $this->cartContents[] = $cartContent;
            $cartContent->setCart($this);
        }

        return $this;
    }

    public function removeCartContent(CartContent $cartContent): self
    {
        if ($this->cartContents->removeElement($cartContent)) {
            // set the owning side to null (unless already changed)
            if ($cartContent->getCart() === $this) {
                $cartContent->setCart(null);
            }
        }

        return $this;
    }
}
