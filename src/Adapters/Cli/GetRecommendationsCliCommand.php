<?php

declare(strict_types=1);

namespace BookTracker\Adapters\Cli;

use BookTracker\Application\Query\Recommendation\GetRecommendationsHandler;
use BookTracker\Application\Query\Recommendation\GetRecommendationsQuery;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

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
			->setName('recommend')
			->setDescription('Get book recommendations for a user')
			->addOption('user-id', null, InputOption::VALUE_REQUIRED, 'User ID')
			->addOption('limit', null, InputOption::VALUE_OPTIONAL, 'Number of recommendations', '5')
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

		$limitRaw = $input->getOption('limit');
		$limit = is_numeric($limitRaw) ? (int)$limitRaw : 5;

		$recommendations = $this->handler->handle(
			new GetRecommendationsQuery(
				userId: $userId,
				limit: $limit,
			)
		);

		if ($recommendations === [])
		{
			$output->writeln('No recommendations found. Read more books first!');

			return Command::SUCCESS;
		}

		$table = new Table($output);
		$table->setHeaders(['#', 'Title', 'Author', 'Score', 'Reason']);

		foreach ($recommendations as $i => $dto)
		{
			$table->addRow(
				[
					(string)($i + 1),
					$dto->book->title,
					$dto->book->author,
					sprintf('%.4f', $dto->score),
					$dto->reason,
				]
			);
		}

		$table->render();

		return Command::SUCCESS;
	}
}
