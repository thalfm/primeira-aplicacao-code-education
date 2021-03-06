<?php
/**
 * Created by PhpStorm.
 * User: thalesmartins
 * Date: 06/11/2017
 * Time: 12:58
 */

namespace App\Infrastructure\Service\Doctrine;

use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;

class DoctrineFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->has('config') ? $container->get('config') : [];
        $paths =  $config['doctrine']['driver']['App_driver']['paths'];
        $dbParams = $config['doctrine']['connection']['orm_default'];
        $isDevMode = true;
        $config = \Doctrine\ORM\Tools\Setup::createYAMLMetadataConfiguration($paths, $isDevMode);

        // EntityManager
        return EntityManager::create($dbParams, $config);
    }
}