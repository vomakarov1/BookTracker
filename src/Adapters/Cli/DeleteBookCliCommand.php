<?php

declare(strict_types=1);

namespace BookTracker\Adapters\Cli;

use BookTracker\Application\Command\Book\DeleteBookCommand;
use BookTracker\Application\Command\Book\DeleteBookHandler;
use BookTracker\Domain\Exception\BookNotFoundException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

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
			->setName('book:delete')
			->setDescription('Delete a book by ID')
			->addOption('id', null, InputOption::VALUE_REQUIRED, 'Book ID')
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
			$this->handler->handle(new DeleteBookCommand($id));
			$output->writeln(sprintf('Book "%s" deleted.', $id));

			return Command::SUCCESS;
		}
		catch (BookNotFoundException $e)
		{
			$output->writeln(sprintf('<error>%s</error>', $e->getMessage()));

			return Command::FAILURE;
		}
	}
}
