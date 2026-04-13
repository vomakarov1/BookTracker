<?php

declare(strict_types=1);

namespace BookTracker\Adapters\Cli;

use BookTracker\Application\Command\ReadingEntry\CreateReadingEntryCommand;
use BookTracker\Application\Command\ReadingEntry\CreateReadingEntryHandler;
use BookTracker\Application\Port\IdGeneratorInterface;
use BookTracker\Domain\Exception\BookNotFoundException;
use BookTracker\Domain\Exception\DuplicateReadingEntryException;
use BookTracker\Domain\Exception\UserNotFoundException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'reading:create', description: 'Create a reading entry for a user and book')]
final class CreateReadingEntryCliCommand extends Command
{
	public function __construct(
		private readonly CreateReadingEntryHandler $handler,
		private readonly IdGeneratorInterface $idGenerator,
	)
	{
		parent::__construct();
	}

	protected function configure(): void
	{
		$this
			->addArgument('user-id', InputArgument::REQUIRED, 'User ID')
			->addArgument('book-id', InputArgument::REQUIRED, 'Book ID')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$io = new SymfonyStyle($input, $output);

		$userId = (string)$input->getArgument('user-id');
		$bookId = (string)$input->getArgument('book-id');

		try
		{
			$id = $this->idGenerator->generate();

			$command = new CreateReadingEntryCommand(id: $id, userId: $userId, bookId: $bookId);
			$this->handler->handle($command);

			$io->success(sprintf('Reading entry created with ID: %s', $id));

			return Command::SUCCESS;
		}
		catch (UserNotFoundException|BookNotFoundException|DuplicateReadingEntryException $e)
		{
			$io->error($e->getMessage());

			return Command::FAILURE;
		}
	}
}
