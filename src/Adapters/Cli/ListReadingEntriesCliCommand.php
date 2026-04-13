<?php

declare(strict_types=1);

namespace BookTracker\Adapters\Cli;

use BookTracker\Application\Query\ReadingEntry\GetReadingEntriesHandler;
use BookTracker\Application\Query\ReadingEntry\GetReadingEntriesQuery;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

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
			->addArgument('user-id', InputArgument::REQUIRED, 'User ID')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$io = new SymfonyStyle($input, $output);

		$userId = (string)$input->getArgument('user-id');

		$entries = $this->handler->handle(new GetReadingEntriesQuery($userId));

		$rows = [];

		foreach ($entries as $entry)
		{
			$rows[] = [
				$entry->id,
				$entry->bookId,
				$entry->status,
				$entry->rating !== null ? (string)$entry->rating : '-',
				$entry->startedAt,
				$entry->finishedAt ?? '-',
			];
		}

		$io->table(['ID', 'Book ID', 'Status', 'Rating', 'Started', 'Finished'], $rows);

		return Command::SUCCESS;
	}
}
