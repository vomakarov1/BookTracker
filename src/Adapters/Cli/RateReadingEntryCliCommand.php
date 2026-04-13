<?php

declare(strict_types=1);

namespace BookTracker\Adapters\Cli;

use BookTracker\Application\Command\ReadingEntry\RateReadingEntryCommand;
use BookTracker\Application\Command\ReadingEntry\RateReadingEntryHandler;
use BookTracker\Domain\Exception\InvalidStatusTransitionException;
use BookTracker\Domain\Exception\ReadingEntryNotFoundException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

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
			->addArgument('id', InputArgument::REQUIRED, 'Reading entry ID')
			->addArgument('rating', InputArgument::REQUIRED, 'Rating (1-10)')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$io = new SymfonyStyle($input, $output);

		$id = (string)$input->getArgument('id');
		$rating = $input->getArgument('rating');

		try
		{
			$command = new RateReadingEntryCommand(readingEntryId: $id, rating: (int)$rating);
			$this->handler->handle($command);

			$io->success(sprintf('Rated: %d/10', (int)$rating));

			return Command::SUCCESS;
		}
		catch (ReadingEntryNotFoundException|InvalidStatusTransitionException $e)
		{
			$io->error($e->getMessage());

			return Command::FAILURE;
		}
	}
}
