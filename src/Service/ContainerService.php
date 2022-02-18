<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ContainerService
 * @package App\Service
 */
final class ContainerService
{
    private static ContainerInterface $container;

    /**  @param ContainerInterface $container */
    public static function setContainer(ContainerInterface $container)
    {
        self::$container = $container;
    }

    /**
     * @return ContainerInterface
     */
    public static function getContainerInstance(): ContainerInterface
    {
        return self::$container;
    }
}
