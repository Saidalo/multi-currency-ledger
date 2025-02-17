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
use Psr\Log\LoggerInterface;
use OpenApi\Annotations as OA;

#[AsController]
/**
 * @OA\Tag(name="Transactions")
 */
#[Route('/transactions')]
class TransactionController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private ValidatorInterface $validator;
    private LoggerInterface $logger;

    public function __construct(EntityManagerInterface $entityManager, ValidatorInterface $validator, LoggerInterface $logger)
    {
        $this->entityManager = $entityManager;
        $this->validator = $validator;
        $this->logger = $logger;
    }

    /**
     * @OA\Post(
     *     path="/transactions",
     *     summary="Create a new transaction",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="ledgerId", type="string", example="12345"),
     *             @OA\Property(property="amount", type="number", example=100),
     *             @OA\Property(property="currency", type="string", example="USD"),
     *             @OA\Property(property="transactionType", type="string", example="credit")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Transaction created"),
     *     @OA\Response(response=400, description="Invalid request")
     * )
     */
    #[Route('', methods: ['POST'])]
    public function createTransaction(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['ledgerId'], $data['transactionType'], $data['amount'], $data['currency'])) {
            return new JsonResponse(["error" => "Missing required fields."], 400);
        }

        $this->entityManager->beginTransaction();

        try {
            $ledgerQuery = $this->entityManager->getRepository(Ledger::class)
                ->createQueryBuilder('l')
                ->where('l.ledgerId = :ledgerId')
                ->setParameter('ledgerId', $data['ledgerId'])
                ->getQuery();
            $ledgerQuery->setLockMode(\Doctrine\DBAL\LockMode::PESSIMISTIC_WRITE);
            $ledger = $ledgerQuery->getOneOrNullResult();

            if (!$ledger) {
                return new JsonResponse(["error" => "Ledger not found."], 404);
            }

            if (!in_array($data['transactionType'], ['credit', 'debit'])) {
                return new JsonResponse(["error" => "Transaction type must be 'credit' or 'debit'."], 400);
            }

            $transaction = new Transaction(
                $ledger,
                $data['transactionType'],
                $data['amount'],
                $data['currency']
            );

            $errors = $this->validator->validate($transaction);
            if (count($errors) > 0) {
                return new JsonResponse(["error" => (string) $errors], 400);
            }

            $this->entityManager->persist($transaction);
            $this->entityManager->flush();
            $this->entityManager->commit(); // ✅ Commit transaction

            return new JsonResponse([
                "transactionId" => $transaction->getTransactionId(),
                "ledgerId" => $transaction->getLedger()->getLedgerId(),
                "amount" => $transaction->getAmount(),
                "transactionType" => $transaction->getTransactionType(),
                "currency" => $transaction->getCurrency(),
                "createdAt" => $transaction->getCreatedAt()->format('Y-m-d H:i:s')
            ], 201);
        } catch (\Exception $e) {
            $this->entityManager->rollback(); // 🔥 Rollback on failure
            $this->logger->error("Transaction failed: " . $e->getMessage()); // ✅ Log the error
            return new JsonResponse(["error" => "Transaction failed."], 500);
        }
    }
}
