<?php

declare(strict_types=1);

namespace BookTracker\Adapters\Cli;

use BookTracker\Application\Query\Recommendation\GetRecommendationsHandler;
use BookTracker\Application\Query\Recommendation\GetRecommendationsQuery;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'recommend', description: 'Get book recommendations for a user')]
final class GetRecommendationsCliCommand extends Command
{
	public function __construct(
		private readonly GetRecommendationsHandler $handler,
	)
	{
		parent::__construct();
	}

	protected function configure(): void
	{
		$this
			->addOption('user-id', null, InputOption::VALUE_REQUIRED, 'User ID')
			->addOption('limit', null, InputOption::VALUE_OPTIONAL, 'Number of recommendations', '5')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$io = new SymfonyStyle($input, $output);

		$userId = $input->getOption('user-id');

		if (!is_string($userId) || $userId === '')
		{
			$io->error('Option --user-id is required.');

			return Command::FAILURE;
		}

		$limitRaw = $input->getOption('limit');
		$limit = is_numeric($limitRaw) ? (int)$limitRaw : 5;

		$recommendations = $this->handler->handle(
			new GetRecommendationsQuery(
				userId: $userId,
				limit: $limit,
			),
		);

		if ($recommendations === [])
		{
			$io->warning('No recommendations found. Read more books first!');

			return Command::SUCCESS;
		}

		$rows = [];

		foreach ($recommendations as $i => $dto)
		{
			$rows[] = [
				(string)($i + 1),
				$dto->book->title,
				$dto->book->author,
				sprintf('%.4f', $dto->score),
				$dto->reason,
			];
		}

		$io->table(['#', 'Title', 'Author', 'Score', 'Reason'], $rows);

		return Command::SUCCESS;
	}
}
