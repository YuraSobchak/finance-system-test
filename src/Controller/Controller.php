<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Service\ContainerService;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use InvalidArgumentException;
use LogicException;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Forms;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validation;
use Throwable;

abstract class Controller implements ContainerAwareInterface
{
    protected ContainerInterface $container;

    public function setContainer(ContainerInterface $container = null): void
    {
        $this->container = $container;
        ContainerService::setContainer($container);
    }

    /**
     * @param string[] $info
     * @param int $status
     * @return JsonResponse
     */
    protected function renderJson($info = ['status' => 'ok'], int $status = Response::HTTP_OK): JsonResponse
    {
        if (array_key_exists($status, Response::$statusTexts) === false) {
            throw new InvalidArgumentException(
                sprintf('An error occurred, the status "%d" is not available.', $status)
            );
        }

        return new JsonResponse($info, $status);
    }

    /**
     * Returns a NotFoundHttpException.
     *
     * This will result in a 404 response code. Usage example:
     *
     *     throw $this->createNotFoundException('Page not found!');
     * @param string $message
     * @param Throwable|null $previous
     * @return NotFoundHttpException
     */
    protected function createNotFoundException(
        string $message = 'Not Found',
        Throwable $previous = null
    ): NotFoundHttpException {
        return new NotFoundHttpException($message, $previous);
    }

    /**
     * @return User
     */
    protected function getUser()
    {
        if (
            null === $token = $this->container->get('security.token_storage')
            ->getToken()
        ) {
            return null;
        }

        if (!is_object($user = $token->getUser())) {
            // e.g. anonymous authentication
            return null;
        }

        return $user;
    }

    /**
     * Shortcut to return the Doctrine Registry service.
     *
     * @throws LogicException If DoctrineBundle is not available
     */
    protected function getDoctrine(): ManagerRegistry
    {
        if (!$this->container->has('doctrine')) {
            throw new LogicException(
                'The DoctrineBundle is not registered in your application. 
                Try running "composer require symfony/orm-pack".'
            );
        }

        return $this->container->get('doctrine');
    }

    protected function isGranted($attributes, $subject = null): bool
    {
        return $this->container->get('security.authorization_checker')->isGranted($attributes, $subject);
    }

    /**
     * @param $attributes
     * @param null $subject
     * @param string $message
     */
    protected function denyAccessUnlessGranted(
        $attributes,
        $subject = null,
        string $message = 'Access Denied.'
    ): void {
        if ($this->isGranted($attributes, $subject) === false) {
            $exception = new AccessDeniedException($message);
            $exception->setAttributes($attributes);
            $exception->setSubject($subject);

            throw $exception;
        }
    }

    /**
     * @param string $formType
     * @param object|null $data
     * @param array $options
     * @param bool $clear_missing
     * @return FormInterface
     * @throws Exception
     */
    protected function handleForm(
        string $formType,
        object $data = null,
        array $options = [],
        bool $clear_missing = true
    ): FormInterface {
        $container = $this->container;

        $validator = Validation::createValidator();

        $form = Forms::createFormFactoryBuilder()->addExtension(new ValidatorExtension($validator))
            ->getFormFactory()
            ->createBuilder($formType, $data, $options)
            ->getForm();

        /** @var Request $request */
        $request = $container->get('request_stack')->getCurrentRequest();

        $request->request->replace(
            array_merge(
                $request->request->all(),
                json_decode($request->getContent(), true)
            )
        );

        $form->handleRequest();

        if (!$form->isSubmitted()) {
            $form->submit($request->request->all(), $clear_missing);
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            throw new Exception($this->getErrorsFromForm($form));
        }

        return $form;
    }

    /**
     * @param FormInterface $form
     * @return false|string
     */
    private function getErrorsFromForm(FormInterface $form)
    {
        $errors = [];
        foreach ($form->all() as $childForm) {
            if ($childForm instanceof FormInterface) {
                foreach ($childForm->getErrors(true) as $error) {
                    $errors[$childForm->getName()] = $error->getMessage();
                }
            }
        }
        return json_encode($errors);
    }
}
