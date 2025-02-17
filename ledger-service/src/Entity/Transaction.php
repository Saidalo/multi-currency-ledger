<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\UuidV4;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\UniqueConstraint(name: "unique_transaction", columns: ["transaction_id"])]
class Transaction
{
    #[ORM\Id]
    #[ORM\Column(type: "string", length: 36, unique: true)]
    private string $transactionId;

    #[ORM\ManyToOne(targetEntity: Ledger::class)]
    #[ORM\JoinColumn(name: "ledger_id", referencedColumnName: "ledger_id", nullable: false, onDelete: "CASCADE")]
    private Ledger $ledger;

    #[ORM\Column(type: "string", length: 6)]
    #[Assert\Choice(["credit", "debit"], message: "Transaction type must be 'credit' or 'debit'.")]
    private string $transactionType;

    #[ORM\Column(type: "decimal", precision: 10, scale: 2)]
    #[Assert\Positive(message: "Amount must be greater than zero.")]
    private float $amount;

    #[ORM\Column(type: "string", length: 3)]
    #[Assert\Length(min: 3, max: 3, exactMessage: "Currency must be a 3-letter ISO code.")]
    private string $currency;

    #[ORM\Column(type: "datetime_immutable")]
    private \DateTimeImmutable $createdAt;

    public function __construct(Ledger $ledger, string $transactionType, float $amount, string $currency)
    {
        $this->transactionId = UuidV4::v4();
        $this->ledger = $ledger;
        $this->transactionType = $transactionType;
        $this->amount = $amount;
        $this->currency = strtoupper($currency);
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getTransactionId(): string
    {
        return $this->transactionId;
    }

    public function getLedger(): Ledger
    {
        return $this->ledger;
    }

    public function getTransactionType(): string
    {
        return $this->transactionType;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
