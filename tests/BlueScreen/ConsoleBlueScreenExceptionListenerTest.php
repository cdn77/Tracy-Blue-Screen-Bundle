<?php

declare(strict_types=1);

namespace Cdn77\TracyBlueScreenBundle\Tests\BlueScreen;

use Cdn77\TracyBlueScreenBundle\BlueScreen\ConsoleBlueScreenErrorListener;
use Exception;
use InvalidArgumentException;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Event\ConsoleErrorEvent;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tracy\BlueScreen;
use Tracy\Logger as TracyLogger;

final class ConsoleBlueScreenExceptionListenerTest extends TestCase
{
    public function testLogTracy(): void
    {
        vfsStream::setup('tracy');
        $directory = vfsStream::url('tracy');
        $file = $directory . '/exception.html';

        $command = $this->createMock(Command::class);
        $input = $this->createMock(InputInterface::class);
        $output = $this->createMock(OutputInterface::class);
        $output
            ->expects(self::once())
            ->method('writeln')
            ->with(self::stringContains('saved in file'));
        $exception = new Exception('Foobar!');

        $event = new ConsoleErrorEvent($input, $output, $exception, $command);

        $logger = $this->createMock(TracyLogger::class);
        $logger
            ->expects(self::once())
            ->method('getExceptionFile')
            ->with($exception)
            ->willReturn($file);

        $blueScreen = $this->createMock(BlueScreen::class);
        $blueScreen
            ->expects(self::once())
            ->method('renderToFile')
            ->with($exception, $file);

        $listener = new ConsoleBlueScreenErrorListener(
            $logger,
            $blueScreen,
            $directory,
            null
        );
        $listener->onConsoleError($event);
    }

    public function testUsesErrorOutputIfPossible(): void
    {
        vfsStream::setup('tracy');
        $directory = vfsStream::url('tracy');
        $file = $directory . '/exception.html';

        $command = $this->createMock(Command::class);
        $input = $this->createMock(InputInterface::class);
        $errorOutput = $this->createMock(OutputInterface::class);
        $errorOutput
            ->expects(self::once())
            ->method('writeln')
            ->with(self::stringContains('saved in file'));
        $output = $this->createMock(ConsoleOutputInterface::class);
        $output
            ->expects(self::once())
            ->method('getErrorOutput')
            ->willReturn($errorOutput);

        $exception = new Exception('Foobar!');

        $event = new ConsoleErrorEvent($input, $output, $exception, $command);

        $logger = $this->createMock(TracyLogger::class);
        $logger
            ->expects(self::once())
            ->method('getExceptionFile')
            ->with($exception)
            ->willReturn($file);

        $blueScreen = $this->createMock(BlueScreen::class);
        $blueScreen
            ->expects(self::once())
            ->method('renderToFile')
            ->with($exception, $file);

        $listener = new ConsoleBlueScreenErrorListener(
            $logger,
            $blueScreen,
            $directory,
            null
        );
        $listener->onConsoleError($event);
    }

    public function testMissingLogDir(): void
    {
        $command = $this->createMock(Command::class);
        $input = $this->createMock(InputInterface::class);
        $output = $this->createMock(OutputInterface::class);
        $exception = new Exception('Foobar!');

        $event = new ConsoleErrorEvent($input, $output, $exception, $command);

        $logger = $this->createMock(TracyLogger::class);
        $blueScreen = $this->createMock(BlueScreen::class);

        $listener = new ConsoleBlueScreenErrorListener(
            $logger,
            $blueScreen,
            null,
            null
        );

        $this->expectException(InvalidArgumentException::class);

        $listener->onConsoleError($event);
    }
}
