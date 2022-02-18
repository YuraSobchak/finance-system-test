<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

final class SecurityController extends Controller
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
     * @param JWTTokenManagerInterface $JWTManager
     * @return JsonResponse
     * @throws Exception
     */
    public function register(JWTTokenManagerInterface $JWTManager): JsonResponse
    {
        $user = new User();
        $this->handleForm(UserType::class, $user);
        $user = $this->userService->updateUserPassword($user, $user->getPassword());
        $this->em->persist($user);
        $this->em->flush();

        return new JsonResponse(['token' => $JWTManager->create($user)]);
    }
}