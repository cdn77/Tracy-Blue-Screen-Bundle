<?php

declare(strict_types=1);

namespace Cdn77\TracyBlueScreenBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

use function assert;
use function is_bool;
use function is_string;

//phpcs:disable SlevomatCodingStandard.Files.LineLength.LineTooLong
final class TracyBlueScreenExtension extends ConfigurableExtension
{
    public const ContainerParameterBlueScreenCollapsePaths = 'cdn77.tracy_blue_screen.blue_screen.collapse_paths';
    public const ContainerParameterConsoleBrowser = 'cdn77.tracy_blue_screen.console.browser';
    public const ContainerParameterConsoleListenerPriority = 'cdn77.tracy_blue_screen.console.listener_priority';
    public const ContainerParameterConsoleLogDirectory = 'cdn77.tracy_blue_screen.console.log_directory';
    public const ContainerParameterControllerListenerPriority = 'cdn77.tracy_blue_screen.controller.listener_priority';

    /** @param mixed[] $mergedConfig */
    public function loadInternal(array $mergedConfig, ContainerBuilder $container): void
    {
        $container->setParameter(
            self::ContainerParameterBlueScreenCollapsePaths,
            $mergedConfig[Configuration::SectionBlueScreen][Configuration::ParameterCollapsePaths],
        );
        $container->setParameter(
            self::ContainerParameterConsoleBrowser,
            $mergedConfig[Configuration::SectionConsole][Configuration::ParameterConsoleBrowser],
        );
        $container->setParameter(
            self::ContainerParameterConsoleListenerPriority,
            $mergedConfig[Configuration::SectionConsole][Configuration::ParameterConsoleListenerPriority],
        );
        $container->setParameter(
            self::ContainerParameterConsoleLogDirectory,
            $mergedConfig[Configuration::SectionConsole][Configuration::ParameterConsoleLogDirectory],
        );
        $container->setParameter(
            self::ContainerParameterControllerListenerPriority,
            $mergedConfig[Configuration::SectionController][Configuration::ParameterControllerListenerPriority],
        );

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/config'));
        $loader->load('services.yml');

        $environment = $container->getParameter('kernel.environment');
        assert(is_string($environment));
        $debug = $container->getParameter('kernel.debug');
        assert(is_bool($debug));

        if (
            $this->isEnabled(
                $mergedConfig[Configuration::SectionConsole][Configuration::ParameterConsoleEnabled],
                $environment,
                $debug,
            )
        ) {
            $loader->load('console_listener.yml');
        }

        if (
            ! $this->isEnabled(
                $mergedConfig[Configuration::SectionController][Configuration::ParameterControllerEnabled],
                $environment,
                $debug,
            )
        ) {
            return;
        }

        $loader->load('controller_listener.yml');
    }

    /** @param mixed[] $config */
    public function getConfiguration(array $config, ContainerBuilder $container): Configuration
    {
        $kernelProjectDir = $container->getParameter('kernel.project_dir');
        $kernelLogsDir = $container->getParameter('kernel.logs_dir');
        $kernelCacheDir = $container->getParameter('kernel.cache_dir');
        assert(is_string($kernelProjectDir));
        assert(is_string($kernelLogsDir));
        assert(is_string($kernelCacheDir));

        return new Configuration(
            $this->getAlias(),
            $kernelProjectDir,
            $kernelLogsDir,
            $kernelCacheDir,
        );
    }

    private function isEnabled(bool|null $configOption, string $environment, bool $debug): bool
    {
        if ($configOption === null) {
            return $environment === 'dev' && $debug === true;
        }

        return $configOption;
    }
}
