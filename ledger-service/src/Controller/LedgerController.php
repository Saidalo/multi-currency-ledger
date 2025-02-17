<?php

namespace App\Controller;

use App\Entity\Ledger;
use App\Entity\Transaction;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\AsController;
use OpenApi\Annotations as OA;

#[AsController]
/**
 * @OA\Tag(name="Ledgers")
 */
#[Route('/ledgers')]
class LedgerController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private ValidatorInterface $validator;

    public function __construct(EntityManagerInterface $entityManager, ValidatorInterface $validator)
    {
        $this->entityManager = $entityManager;
        $this->validator = $validator;
    }

    /**
     * @OA\Post(
     *     path="/ledgers",
     *     summary="Create a new ledger",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="currency", type="string", example="USD")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Ledger created"),
     *     @OA\Response(response=400, description="Invalid request")
     * )
     */
    #[Route('', methods: ['POST'])]
    public function createLedger(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!isset($data['currency'])) {
            return new JsonResponse(["error" => "Currency is required"], 400);
        }

        $ledger = new Ledger($data['currency']);

        $errors = $this->validator->validate($ledger);
        if (count($errors) > 0) {
            return new JsonResponse(['error' => (string) $errors], 400);
        }

        $this->entityManager->persist($ledger);
        $this->entityManager->flush();

        return new JsonResponse([
            'ledgerId' => $ledger->getLedgerId(),
            'currency' => $ledger->getCurrency(),
            'createdAt' => $ledger->getCreatedAt()->format('Y-m-d H:i:s')
        ], 201);
    }

    #[Route('/balances/{ledgerId}', methods: ['GET'])]
    public function getBalance(string $ledgerId): JsonResponse {
        $ledger = $this->entityManager->getRepository(Ledger::class)->findOneBy(['ledgerId' => $ledgerId]);

        if (!$ledger) {
            return new JsonResponse(['error' => 'Ledger not found'], 404);
        }

        $transactions = $this->entityManager->getRepository(Transaction::class)->findBy(['ledger' => $ledger]);

        $balances = [];
        foreach ($transactions as $transaction) {
            $currency = $transaction->getCurrency();
            $amount = $transaction->getAmount();
            $type = $transaction->getTransactionType();

            if (!isset($balances[$currency])) {
                $balances[$currency] = 0;
            }

            $balances[$currency] += ($type === 'credit' ? $amount : -$amount);
        }

        return new JsonResponse($balances);
    }
}
