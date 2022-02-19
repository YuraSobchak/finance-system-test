<?php
declare(strict_types=1);

namespace App\Controller;

use App\DTO\UserRequest;
use App\Factory\UserFactory;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

final class SecurityController extends AbstractController
{
    private EntityManagerInterface $em;
    private UserService $userService;

    public function __construct(
        EntityManagerInterface $em,
        UserService $userService
    )
    {
        $this->em = $em;
        $this->userService = $userService;
    }

    /**
     * @Route("/api/register", methods={"POST"}, name="api.register")
     * @param UserRequest $userRequest
     * @param JWTTokenManagerInterface $JWTManager
     * @return JsonResponse
     */
    public function register(UserRequest $userRequest, JWTTokenManagerInterface $JWTManager): JsonResponse
    {
        // create User
        $user = (new UserFactory())->create(
            $userRequest->email(),
            $userRequest->username()
        );
        // hash password and add to user
        $user = $this->userService->updateUserPassword($user, $userRequest->password());
        $this->em->persist($user);
        $this->em->flush();

        return new JsonResponse(['token' => $JWTManager->create($user)]);
    }
}