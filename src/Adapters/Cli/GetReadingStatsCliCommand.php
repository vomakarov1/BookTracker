<?php

declare(strict_types=1);

namespace BookTracker\Adapters\Cli;

use BookTracker\Application\Query\Statistics\GetReadingStatsHandler;
use BookTracker\Application\Query\Statistics\GetReadingStatsQuery;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'stats', description: 'Show reading statistics for a user')]
final class GetReadingStatsCliCommand extends Command
{
	public function __construct(
		private readonly GetReadingStatsHandler $handler,
	)
	{
		parent::__construct();
	}

	protected function configure(): void
	{
		$this->addArgument('user-id', InputArgument::REQUIRED, 'User ID');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$io = new SymfonyStyle($input, $output);
		$userId = (string)$input->getArgument('user-id');

		$stats = $this->handler->handle(new GetReadingStatsQuery($userId));

		$totalBooks = array_sum($stats->countsByStatus);

		if ($totalBooks === 0)
		{
			$io->warning('No reading entries found for this user.');

			return Command::SUCCESS;
		}

		$io->section('Books by status');
		$statusRows = [];

		foreach ($stats->countsByStatus as $status => $count)
		{
			$statusRows[] = [ucfirst($status), (string)$count];
		}

		$io->table(['Status', 'Count'], $statusRows);

		if ($stats->averageRatingByAuthor !== [])
		{
			$io->section('Average rating by author');
			$authorRows = [];

			foreach ($stats->averageRatingByAuthor as $author => $avg)
			{
				$authorRows[] = [$author, sprintf('%.1f / 10', $avg)];
			}

			$io->table(['Author', 'Avg. Rating'], $authorRows);
		}

		if ($stats->finishedByMonth !== [])
		{
			$io->section('Books finished by month');
			$monthRows = [];

			foreach ($stats->finishedByMonth as $month => $count)
			{
				$monthRows[] = [$month, str_repeat('▪', $count) . ' ' . $count];
			}

			$io->table(['Month', 'Finished'], $monthRows);
		}

		return Command::SUCCESS;
	}
}
