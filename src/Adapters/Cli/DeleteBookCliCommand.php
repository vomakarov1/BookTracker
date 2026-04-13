<?php

declare(strict_types=1);

namespace BookTracker\Adapters\Cli;

use BookTracker\Application\Command\Book\DeleteBookCommand;
use BookTracker\Application\Command\Book\DeleteBookHandler;
use BookTracker\Domain\Exception\BookNotFoundException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'book:delete', description: 'Delete a book by ID')]
final class DeleteBookCliCommand extends Command
{
	public function __construct(
		private readonly DeleteBookHandler $handler,
	)
	{
		parent::__construct();
	}

	protected function configure(): void
	{
		$this
			->addOption('id', null, InputOption::VALUE_REQUIRED, 'Book ID')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$io = new SymfonyStyle($input, $output);

		$id = $input->getOption('id');

		if (!is_string($id) || $id === '')
		{
			$io->error('Option --id is required.');

			return Command::FAILURE;
		}

		try
		{
			if (!$io->confirm(sprintf('Delete book "%s"? This action cannot be undone.', $id), false))
			{
				$io->note('Aborted.');

				return Command::SUCCESS;
			}

			$this->handler->handle(new DeleteBookCommand($id));

			$io->success(sprintf('Book "%s" deleted.', $id));

			return Command::SUCCESS;
		}
		catch (BookNotFoundException $e)
		{
			$io->error($e->getMessage());

			return Command::FAILURE;
		}
	}
}
