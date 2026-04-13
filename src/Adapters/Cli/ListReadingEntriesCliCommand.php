<?php

declare(strict_types=1);

namespace BookTracker\Adapters\Cli;

use BookTracker\Application\Query\ReadingEntry\GetReadingEntriesHandler;
use BookTracker\Application\Query\ReadingEntry\GetReadingEntriesQuery;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'reading:list', description: 'List reading entries for a user')]
final class ListReadingEntriesCliCommand extends Command
{
	public function __construct(
		private readonly GetReadingEntriesHandler $handler,
	)
	{
		parent::__construct();
	}

	protected function configure(): void
	{
		$this
			->addOption('user-id', null, InputOption::VALUE_REQUIRED, 'User ID')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$userId = $input->getOption('user-id');

		if (!is_string($userId) || $userId === '')
		{
			$output->writeln('<error>Option --user-id is required.</error>');

			return Command::FAILURE;
		}

		$entries = $this->handler->handle(new GetReadingEntriesQuery($userId));

		$table = new Table($output);
		$table->setHeaders(['ID', 'Book ID', 'Status', 'Rating', 'Started', 'Finished']);

		foreach ($entries as $entry)
		{
			$table->addRow(
				[
					$entry->id,
					$entry->bookId,
					$entry->status,
					$entry->rating !== null ? (string)$entry->rating : '-',
					$entry->startedAt,
					$entry->finishedAt ?? '-',
				],
			);
		}

		$table->render();

		return Command::SUCCESS;
	}
}
