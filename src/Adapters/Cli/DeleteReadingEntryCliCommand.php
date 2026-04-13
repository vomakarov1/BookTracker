<?php

declare(strict_types=1);

namespace BookTracker\Adapters\Cli;

use BookTracker\Application\Command\ReadingEntry\DeleteReadingEntryCommand;
use BookTracker\Application\Command\ReadingEntry\DeleteReadingEntryHandler;
use BookTracker\Domain\Exception\ReadingEntryNotFoundException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'reading:delete', description: 'Delete a reading entry by ID')]
final class DeleteReadingEntryCliCommand extends Command
{
	public function __construct(
		private readonly DeleteReadingEntryHandler $handler,
	)
	{
		parent::__construct();
	}

	protected function configure(): void
	{
		$this
			->addArgument('id', InputArgument::REQUIRED, 'Reading entry ID')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$io = new SymfonyStyle($input, $output);

		$id = (string)$input->getArgument('id');

		try
		{
			if (!$io->confirm(sprintf('Delete reading entry "%s"? This action cannot be undone.', $id), false))
			{
				$io->note('Aborted.');

				return Command::SUCCESS;
			}

			$this->handler->handle(new DeleteReadingEntryCommand($id));

			$io->success(sprintf('Reading entry "%s" deleted.', $id));

			return Command::SUCCESS;
		}
		catch (ReadingEntryNotFoundException $e)
		{
			$io->error($e->getMessage());

			return Command::FAILURE;
		}
	}
}
