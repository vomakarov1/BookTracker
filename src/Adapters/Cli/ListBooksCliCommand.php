<?php

declare(strict_types=1);

namespace BookTracker\Adapters\Cli;

use BookTracker\Application\Query\Book\GetBooksListHandler;
use BookTracker\Application\Query\Book\GetBooksListQuery;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'book:list', description: 'List all books')]
final class ListBooksCliCommand extends Command
{
	public function __construct(
		private readonly GetBooksListHandler $handler,
	)
	{
		parent::__construct();
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$io = new SymfonyStyle($input, $output);

		$books = $this->handler->handle(new GetBooksListQuery());

		$rows = [];

		foreach ($books as $book)
		{
			$rows[] = [$book->id, $book->title, $book->author, $book->category, (string)$book->complexity];
		}

		$io->table(['ID', 'Title', 'Author', 'Category', 'Complexity'], $rows);

		return Command::SUCCESS;
	}
}
