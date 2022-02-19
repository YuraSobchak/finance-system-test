<?php
declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\HttpFoundation\Request;

/**
 * Interface RequestDTOInterface
 * @package App\DTO
 */
interface RequestDTOInterface
{
    public function __construct(Request $request);
}