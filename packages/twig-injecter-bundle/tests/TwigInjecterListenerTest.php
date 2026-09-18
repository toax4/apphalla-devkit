<?php

namespace Apphalla\TwigInjecterBundle\Tests;

use Apphalla\TwigInjecterBundle\Contract\TwigInjecterInterface;
use Apphalla\TwigInjecterBundle\EventListener\TwigInjecterListener;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Twig\Environment;

final class TwigInjecterListenerTest extends TestCase
{
    public function testInjectsGlobalWhenSupported(): void
    {
        $twig = $this->createMock(Environment::class);
        $twig->expects($this->once())
            ->method('addGlobal')
            ->with('supported_var', 'supported_value');

        $injecter = $this->createInjecter(supports: true, name: 'supported_var', value: 'supported_value');

        $listener = new TwigInjecterListener($twig, [$injecter]);
        $listener->onKernelController($this->createControllerEvent());
    }

    public function testSkipsInjecterWhenNotSupported(): void
    {
        $twig = $this->createMock(Environment::class);
        $twig->expects($this->never())->method('addGlobal');

        $injecter = $this->createInjecter(supports: false, name: 'irrelevant', value: 'irrelevant');

        $listener = new TwigInjecterListener($twig, [$injecter]);
        $listener->onKernelController($this->createControllerEvent());
    }

    public function testSkipsSubRequests(): void
    {
        $twig = $this->createMock(Environment::class);
        $twig->expects($this->never())->method('addGlobal');

        $injecter = $this->createInjecter(supports: true, name: 'irrelevant', value: 'irrelevant');

        $listener = new TwigInjecterListener($twig, [$injecter]);
        $listener->onKernelController($this->createControllerEvent(mainRequest: false));
    }

    private function createInjecter(bool $supports, string $name, mixed $value): TwigInjecterInterface
    {
        $injecter = $this->createMock(TwigInjecterInterface::class);
        $injecter->method('supports')->willReturn($supports);
        $injecter->method('getName')->willReturn($name);
        $injecter->method('getValue')->willReturn($value);

        return $injecter;
    }

    private function createControllerEvent(bool $mainRequest = true): ControllerEvent
    {
        $kernel = $this->createMock(KernelInterface::class);

        return new ControllerEvent(
            $kernel,
            static fn () => new \stdClass(),
            new Request(),
            $mainRequest ? HttpKernelInterface::MAIN_REQUEST : HttpKernelInterface::SUB_REQUEST,
        );
    }
}
