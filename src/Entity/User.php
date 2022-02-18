<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 180, unique: true)]
    private $email;

    #[ORM\Column(type: 'string', length: 32, unique: true)]
    private $username;

    #[ORM\Column(type: 'json')]
    private $roles = [];

    #[ORM\Column(type: 'string')]
    private $password;

    #[ORM\Column(type: 'float', nullable: true)]
    private $amount;

    #[ORM\OneToMany(mappedBy: 'receiver', targetEntity: Transaction::class, orphanRemoval: true)]
    private $payments;

    #[ORM\OneToMany(mappedBy: 'sender', targetEntity: Transaction::class, orphanRemoval: true)]
    private $transactions;

    public function __construct()
    {
        $this->payments = new ArrayCollection();
        $this->transactions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?int
    {
        return $this->email;
    }

    public function getSecureEmail(): ?string
    {
        $mailParts = explode('@', $this->email);

        $mailNameLength = strlen($mailParts[0]);
        $stars = null;
        // string of stars with mail name length without first and last letters
        for ($i = 1; $i <= $mailNameLength - 2; $i++) {
            $stars .= '*';
        }

        // mail name with only first and last letters
        $mailName = substr_replace($mailParts[0], $stars, 1, $mailNameLength-2);

        // return secure mail
        return $mailName . '@' . $mailParts[1];
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
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
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(?float $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * @return Collection<int, Transaction>
     */
    public function getPayments(): Collection
    {
        return $this->payments;
    }

    public function addPayment(Transaction $transaction): self
    {
        if (!$this->payments->contains($transaction)) {
            $this->payments[] = $transaction;
            $transaction->setReceiver($this);
        }

        return $this;
    }

    public function removePayment(Transaction $transaction): self
    {
        if ($this->payments->removeElement($transaction)) {
            // set the owning side to null (unless already changed)
            if ($transaction->getReceiver() === $this) {
                $transaction->setReceiver(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Transaction>
     */
    public function getTransactions(): Collection
    {
        return $this->transactions;
    }

    public function addTransaction(Transaction $transaction): self
    {
        if (!$this->transactions->contains($transaction)) {
            $this->transactions[] = $transaction;
            $transaction->setSender($this);
        }

        return $this;
    }

    public function removeTransaction(Transaction $transaction): self
    {
        if ($this->transactions->removeElement($transaction)) {
            // set the owning side to null (unless already changed)
            if ($transaction->getSender() === $this) {
                $transaction->setSender(null);
            }
        }

        return $this;
    }
}
