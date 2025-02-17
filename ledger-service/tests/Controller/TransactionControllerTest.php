<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpClient\HttpClient;

class TransactionControllerTest extends KernelTestCase
{
    private static $client;
    private static string $ledgerId;

    public static function setUpBeforeClass(): void
    {
        // ✅ Ensure base URI is correct and points to your API
        self::$client = HttpClient::create([
            'base_uri' => 'http://nginx', // ✅ Use "nginx" instead of "localhost"
            'headers' => ['Content-Type' => 'application/json']
        ]);

        // ✅ Correct: Create a Ledger with POST (NO GET request)
        $response = self::$client->request('POST', '/ledgers', [
            'json' => ['currency' => 'USD']
        ]);

        if ($response->getStatusCode() !== 201) {
            throw new \RuntimeException("Failed to create ledger: " . $response->getContent(false));
        }

        $ledgerData = $response->toArray();
        self::$ledgerId = $ledgerData['ledgerId'];
    }

    public function testCreateTransaction()
    {
        $response = self::$client->request('POST', '/transactions', [
            'json' => [
                'ledgerId' => self::$ledgerId,
                'transactionType' => 'credit',
                'amount' => 100.00,
                'currency' => 'USD'
            ]
        ]);

        $this->assertSame(201, $response->getStatusCode());
    }

    public function testGetBalances()
    {
        $response = self::$client->request('GET', "ledgers/balances/" . self::$ledgerId);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertJson($response->getContent());
    }
}
