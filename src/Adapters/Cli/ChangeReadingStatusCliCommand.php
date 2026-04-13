<?php

declare(strict_types=1);

namespace BookTracker\Adapters\Cli;

use BookTracker\Application\Command\ReadingEntry\ChangeReadingStatusCommand;
use BookTracker\Application\Command\ReadingEntry\ChangeReadingStatusHandler;
use BookTracker\Application\Exception\ValidationException;
use BookTracker\Domain\Exception\InvalidStatusTransitionException;
use BookTracker\Domain\Exception\ReadingEntryNotFoundException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'reading:status', description: 'Change the status of a reading entry')]
final class ChangeReadingStatusCliCommand extends Command
{
	public function __construct(
		private readonly ChangeReadingStatusHandler $handler,
	)
	{
		parent::__construct();
	}

	protected function configure(): void
	{
		$this
			->addOption('id', null, InputOption::VALUE_REQUIRED, 'Reading entry ID')
			->addOption('status', null, InputOption::VALUE_REQUIRED, 'New status (planned|reading|finished|dropped)')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$io = new SymfonyStyle($input, $output);

		$id = $input->getOption('id');
		$status = $input->getOption('status');

		if (!is_string($id) || $id === '' || !is_string($status) || $status === '')
		{
			$io->error('Options --id and --status are required.');

			return Command::FAILURE;
		}

		try
		{
			$command = new ChangeReadingStatusCommand(readingEntryId: $id, newStatus: $status);
			$this->handler->handle($command);

			$io->success(sprintf('Status changed to %s', $status));

			return Command::SUCCESS;
		}
		catch (ReadingEntryNotFoundException|ValidationException|InvalidStatusTransitionException $e)
		{
			$io->error($e->getMessage());

			return Command::FAILURE;
		}
	}
}
