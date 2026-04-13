<?php

declare(strict_types=1);

namespace BookTracker\Adapters\Cli;

use BookTracker\Application\Query\Book\GetBookHandler;
use BookTracker\Application\Query\Book\GetBookQuery;
use BookTracker\Domain\Exception\BookNotFoundException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'book:show', description: 'Show book details')]
final class ShowBookCliCommand extends Command
{
	public function __construct(
		private readonly GetBookHandler $handler,
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
		$id = $input->getOption('id');

		if (!is_string($id) || $id === '')
		{
			$output->writeln('<error>Option --id is required.</error>');

			return Command::FAILURE;
		}

		try
		{
			$book = $this->handler->handle(new GetBookQuery($id));

			$output->writeln(sprintf('ID:         %s', $book->id));
			$output->writeln(sprintf('Title:      %s', $book->title));
			$output->writeln(sprintf('Author:     %s', $book->author));
			$output->writeln(sprintf('Category:   %s', $book->category));
			$output->writeln(sprintf('Complexity: %d', $book->complexity));

			return Command::SUCCESS;
		}
		catch (BookNotFoundException $e)
		{
			$output->writeln(sprintf('<error>%s</error>', $e->getMessage()));

			return Command::FAILURE;
		}
	}
}
