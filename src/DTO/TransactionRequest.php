<?php
declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class TransactionRequest
 * @package App\DTO
 */
final class TransactionRequest implements RequestDTOInterface
{
    #[Assert\NotBlank]
    private string $receiver;

    #[Assert\NotBlank]
    private float $amount;

    public function __construct(Request $request)
    {
        $data = json_decode($request->getContent(), true);

        $this->receiver = $data['receiver'] ?? '';
        $this->amount = $data['amount'] ?? '';
    }

    public function receiver(): string
    {
        return $this->receiver;
    }

    public function amount(): float
    {
        return $this->amount;
    }
}