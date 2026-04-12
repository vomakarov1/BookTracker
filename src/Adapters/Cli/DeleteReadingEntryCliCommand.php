<?php

declare(strict_types=1);

namespace BookTracker\Adapters\Cli;

use BookTracker\Application\Command\ReadingEntry\DeleteReadingEntryCommand;
use BookTracker\Application\Command\ReadingEntry\DeleteReadingEntryHandler;
use BookTracker\Domain\Exception\ReadingEntryNotFoundException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

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
			->setName('reading:delete')
			->setDescription('Delete a reading entry by ID')
			->addOption('id', null, InputOption::VALUE_REQUIRED, 'Reading entry ID')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$id = $input->getOption('id');

		if (!is_string($id) || $id === '')
		{
			$output->writeln('<error>Option --id is required.</error>');

			return Command::FAILURE;
		}

		try
		{
			$this->handler->handle(new DeleteReadingEntryCommand($id));
			$output->writeln(sprintf('Reading entry "%s" deleted.', $id));

			return Command::SUCCESS;
		}
		catch (ReadingEntryNotFoundException $e)
		{
			$output->writeln(sprintf('<error>%s</error>', $e->getMessage()));

			return Command::FAILURE;
		}
	}
}
