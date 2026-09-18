<?php

namespace Apphalla\TwigInjecterBundle\EventListener;

use Apphalla\TwigInjecterBundle\Contract\TwigInjecterInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Twig\Environment;

final class TwigInjecterListener
{
    /**
     * @param iterable<TwigInjecterInterface> $injecters
     */
    public function __construct(
        private readonly Environment $twig,
        private readonly iterable $injecters,
    ) {
    }

    public function onKernelController(ControllerEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();

        foreach ($this->injecters as $injecter) {
            if ($injecter->supports($request)) {
                $this->twig->addGlobal($injecter->getName(), $injecter->getValue());
            }
        }
    }
}
