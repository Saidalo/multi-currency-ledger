<?php

namespace App\Tests\Entity;

use App\Entity\Ledger;
use App\Entity\Transaction;
use PHPUnit\Framework\TestCase;

class TransactionTest extends TestCase
{
    public function testTransactionCreation()
    {
        $ledger = new Ledger('USD');
        $transaction = new Transaction($ledger, 'credit', 100.00, 'USD');

        $this->assertEquals(100.00, $transaction->getAmount());
        $this->assertEquals('USD', $transaction->getCurrency());
        $this->assertEquals('credit', $transaction->getTransactionType());
        $this->assertEquals($ledger, $transaction->getLedger());
    }
}
