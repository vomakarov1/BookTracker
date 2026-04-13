<?php

declare(strict_types=1);

namespace BookTracker\Adapters\Cli;

use BookTracker\Application\Command\ReadingEntry\RateReadingEntryCommand;
use BookTracker\Application\Command\ReadingEntry\RateReadingEntryHandler;
use BookTracker\Domain\Exception\InvalidStatusTransitionException;
use BookTracker\Domain\Exception\ReadingEntryNotFoundException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'reading:rate', description: 'Rate a finished reading entry')]
final class RateReadingEntryCliCommand extends Command
{
	public function __construct(
		private readonly RateReadingEntryHandler $handler,
	)
	{
		parent::__construct();
	}

	protected function configure(): void
	{
		$this
			->addOption('id', null, InputOption::VALUE_REQUIRED, 'Reading entry ID')
			->addOption('rating', null, InputOption::VALUE_REQUIRED, 'Rating (1-10)')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$id = $input->getOption('id');
		$rating = $input->getOption('rating');

		if (!is_string($id) || $id === '' || $rating === null)
		{
			$output->writeln('<error>Options --id and --rating are required.</error>');

			return Command::FAILURE;
		}

		try
		{
			$command = new RateReadingEntryCommand(readingEntryId: $id, rating: (int)$rating);
			$this->handler->handle($command);

			$output->writeln(sprintf('Rated: %d/10', (int)$rating));

			return Command::SUCCESS;
		}
		catch (ReadingEntryNotFoundException|InvalidStatusTransitionException $e)
		{
			$output->writeln(sprintf('<error>%s</error>', $e->getMessage()));

			return Command::FAILURE;
		}
	}
}
