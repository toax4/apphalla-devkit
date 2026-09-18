<?php

namespace Apphalla\TwigInjecterBundle;

use Apphalla\TwigInjecterBundle\Contract\TwigInjecterInterface;
use Apphalla\TwigInjecterBundle\EventListener\TwigInjecterListener;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

final class TwigInjecterBundle extends AbstractBundle
{
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        // 1. Tag automatique pour toute classe implémentant l'interface
        $builder->registerForAutoconfiguration(TwigInjecterInterface::class)
            ->addTag('app.twig_injecter');

        // 2. Enregistrement du listener, caché dans le bundle
        $container->services()
            ->set(TwigInjecterListener::class)
            ->args([
                service('twig'),
                tagged_iterator('app.twig_injecter'),
            ])
            ->tag('kernel.event_listener', [
                'event' => 'kernel.controller',
                'method' => 'onKernelController',
            ]);
    }
}