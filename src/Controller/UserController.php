<?php
declare(strict_types=1);

namespace App\Controller;

use App\DataTransformer\UserDataTransformer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

final class UserController extends AbstractController
{
    /**
     * @Route("/api/user/info", methods={"GET"}, name="api.user.info")
     * @return JsonResponse
     */
    public function userInfo(): JsonResponse
    {
        return new JsonResponse([
            'status' => 'ok',
            'data' => UserDataTransformer::userToArray($this->getUser())
        ]);
    }
}