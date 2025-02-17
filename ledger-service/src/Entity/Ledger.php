<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\UuidV4;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
class Ledger
{
    #[ORM\Id]
    #[ORM\Column(type: "string", length: 36, unique: true)]
    private string $ledgerId;

    #[ORM\Column(type: "string", length: 3)]
    private string $currency;

    #[ORM\Column(type: "datetime_immutable")]
    private \DateTimeImmutable $createdAt;
    /**
     * @var Collection<int, Transaction>
     */
    #[ORM\OneToMany(targetEntity: Transaction::class, mappedBy: 'ledger', orphanRemoval: true)]
    private Collection $transactions;

    public function __construct(string $currency)
    {
        $this->ledgerId = UuidV4::v4();
        $this->currency = strtoupper($currency);
        $this->createdAt = new \DateTimeImmutable();
        $this->transactions = new ArrayCollection();
    }

    public function getLedgerId(): string
    {
        return $this->ledgerId;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * @return Collection<int, Transaction>
     */
    public function getTransactions(): Collection
    {
        return $this->transactions;
    }

    public function addTransaction(Transaction $transaction): static
    {
        if (!$this->transactions->contains($transaction)) {
            $this->transactions->add($transaction);
            $transaction->setLedger($this);
        }

        return $this;
    }

    public function removeTransaction(Transaction $transaction): static
    {
        if ($this->transactions->removeElement($transaction)) {
            // set the owning side to null (unless already changed)
            if ($transaction->getLedger() === $this) {
                $transaction->setLedger(null);
            }
        }

        return $this;
    }
}
