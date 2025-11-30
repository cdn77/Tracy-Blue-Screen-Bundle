<?php

declare(strict_types=1);

namespace Cdn77\TracyBlueScreenBundle\Tests\DependencyInjection;

use Cdn77\TracyBlueScreenBundle\BlueScreen\ControllerBlueScreenExceptionListener;
use Cdn77\TracyBlueScreenBundle\DependencyInjection\TracyBlueScreenExtension;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;

final class TracyBlueScreenExtensionControllerTest extends AbstractExtensionTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->setParameter('kernel.environment', 'dev');
        $this->setParameter('kernel.debug', true);
    }

    public function testEnabledByDefault(): void
    {
        $this->loadExtensions();

        $this->assertContainerBuilderHasService(
            'cdn77.tracy_blue_screen.blue_screen.controller_blue_screen_exception_listener',
            ControllerBlueScreenExceptionListener::class,
        );
        $this->assertContainerBuilderHasServiceDefinitionWithTag(
            'cdn77.tracy_blue_screen.blue_screen.controller_blue_screen_exception_listener',
            'kernel.event_listener',
            [
                'event' => 'kernel.exception',
                'priority' => '%' . TracyBlueScreenExtension::ContainerParameterControllerListenerPriority . '%',
            ],
        );
    }

    public function testDisabled(): void
    {
        $this->loadExtensions(
            [
                'tracy_blue_screen' => [
                    'controller' => ['enabled' => false],
                ],
            ],
        );

        $this->assertContainerBuilderNotHasService(
            'cdn77.tracy_blue_screen.blue_screen.controller_blue_screen_exception_listener',
        );
    }

    public function testEnabled(): void
    {
        $this->loadExtensions(
            [
                'tracy_blue_screen' => [
                    'controller' => ['enabled' => true],
                ],
            ],
        );

        $this->assertContainerBuilderHasService(
            'cdn77.tracy_blue_screen.blue_screen.controller_blue_screen_exception_listener',
            ControllerBlueScreenExceptionListener::class,
        );
        $this->assertContainerBuilderHasServiceDefinitionWithTag(
            'cdn77.tracy_blue_screen.blue_screen.controller_blue_screen_exception_listener',
            'kernel.event_listener',
            [
                'event' => 'kernel.exception',
                'priority' => '%' . TracyBlueScreenExtension::ContainerParameterControllerListenerPriority . '%',
            ],
        );
    }

    public function testConfigureListenerPriority(): void
    {
        $this->loadExtensions(
            [
                'tracy_blue_screen' => [
                    'controller' => ['listener_priority' => 123],
                ],
            ],
        );

        $this->assertContainerBuilderHasParameter(
            TracyBlueScreenExtension::ContainerParameterControllerListenerPriority,
            123,
        );
    }

    /** @return ExtensionInterface[] */
    protected function getContainerExtensions(): array
    {
        return [new TracyBlueScreenExtension()];
    }

    /** @param mixed[] $configuration format: extensionAlias(string) => configuration(mixed[]) */
    private function loadExtensions(array $configuration = []): void
    {
        TracyBlueScreenExtensionTest::loadExtensionsToContainer(
            $this->container,
            $configuration,
            $this->getMinimalConfiguration(),
        );
    }
}
