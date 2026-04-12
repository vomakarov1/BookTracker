<?php

declare(strict_types=1);

namespace BookTracker\Adapters\Cli;

use BookTracker\Application\Command\Export\ExportBooksCommand;
use BookTracker\Application\Command\Export\ExportBooksHandler;
use BookTracker\Application\Exception\ExportFailedException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class ExportBooksCliCommand extends Command
{
	public function __construct(
		private readonly ExportBooksHandler $handler,
	)
	{
		parent::__construct();
	}

	protected function configure(): void
	{
		$this
			->setName('export:books')
			->setDescription('Export books to a file')
			->addOption('file', null, InputOption::VALUE_REQUIRED, 'Path to the output file')
			->addOption('format', null, InputOption::VALUE_OPTIONAL, 'File format (json|csv)', 'json')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$file = $input->getOption('file');

		if (!is_string($file) || $file === '')
		{
			$output->writeln('<error>Option --file is required.</error>');

			return Command::FAILURE;
		}

		$formatRaw = $input->getOption('format');
		$format = is_string($formatRaw) ? $formatRaw : 'json';

		try
		{
			$command = new ExportBooksCommand(filePath: $file, format: $format);
			$this->handler->handle($command);

			$output->writeln(sprintf('Exported to %s', $file));

			return Command::SUCCESS;
		}
		catch (ExportFailedException $e)
		{
			$output->writeln(sprintf('<error>%s</error>', $e->getMessage()));

			return Command::FAILURE;
		}
	}
}
