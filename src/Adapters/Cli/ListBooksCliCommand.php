<?php

declare(strict_types=1);

namespace BookTracker\Adapters\Cli;

use BookTracker\Application\Query\Book\GetBooksListHandler;
use BookTracker\Application\Query\Book\GetBooksListQuery;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class ListBooksCliCommand extends Command
{
	public function __construct(
		private readonly GetBooksListHandler $handler,
	)
	{
		parent::__construct();
	}

	protected function configure(): void
	{
		$this
			->setName('book:list')
			->setDescription('List all books')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$books = $this->handler->handle(new GetBooksListQuery());

		$table = new Table($output);
		$table->setHeaders(['ID', 'Title', 'Author', 'Category', 'Complexity']);

		foreach ($books as $book)
		{
			$table->addRow(
				[
					$book->id,
					$book->title,
					$book->author,
					$book->category,
					(string)$book->complexity,
				]
			);
		}

		$table->render();

		return Command::SUCCESS;
	}
}
