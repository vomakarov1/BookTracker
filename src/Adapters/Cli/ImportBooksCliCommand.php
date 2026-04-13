<?php

declare(strict_types=1);

namespace BookTracker\Adapters\Cli;

use BookTracker\Application\Command\Import\ImportBooksCommand;
use BookTracker\Application\Command\Import\ImportBooksHandler;
use BookTracker\Application\Exception\ImportFailedException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

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
			->addArgument('file', InputArgument::REQUIRED, 'Path to the import file')
			->addOption('format', null, InputOption::VALUE_OPTIONAL, 'File format (json|csv)', 'json')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$io = new SymfonyStyle($input, $output);

		$file = (string)$input->getArgument('file');

		$formatRaw = $input->getOption('format');
		$format = is_string($formatRaw) ? $formatRaw : 'json';

		try
		{
			$command = new ImportBooksCommand(filePath: $file, format: $format);
			$this->handler->handle($command);

			$io->success('Books imported.');

			return Command::SUCCESS;
		}
		catch (ImportFailedException $e)
		{
			$io->error($e->getMessage());

			return Command::FAILURE;
		}
	}
}
