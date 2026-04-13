<?php

declare(strict_types=1);

namespace BookTracker\Adapters\Cli;

use BookTracker\Application\Command\Import\ImportBooksCommand;
use BookTracker\Application\Command\Import\ImportBooksHandler;
use BookTracker\Application\Exception\ImportFailedException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'import:books', description: 'Import books from a file')]
final class ImportBooksCliCommand extends Command
{
	public function __construct(
		private readonly ImportBooksHandler $handler,
	)
	{
		parent::__construct();
	}

	protected function configure(): void
	{
		$this
			->addOption('file', null, InputOption::VALUE_REQUIRED, 'Path to the import file')
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
			$command = new ImportBooksCommand(filePath: $file, format: $format);
			$this->handler->handle($command);

			$output->writeln('Books imported.');

			return Command::SUCCESS;
		}
		catch (ImportFailedException $e)
		{
			$output->writeln(sprintf('<error>%s</error>', $e->getMessage()));

			return Command::FAILURE;
		}
	}
}
