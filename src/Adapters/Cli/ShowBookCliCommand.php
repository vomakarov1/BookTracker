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
use Symfony\Component\Console\Style\SymfonyStyle;

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
		$io = new SymfonyStyle($input, $output);

		$id = $input->getOption('id');

		if (!is_string($id) || $id === '')
		{
			$io->error('Option --id is required.');

			return Command::FAILURE;
		}

		try
		{
			$book = $this->handler->handle(new GetBookQuery($id));

			$io->definitionList(
				['ID' => $book->id],
				['Title' => $book->title],
				['Author' => $book->author],
				['Category' => $book->category],
				['Complexity' => (string)$book->complexity],
			);

			return Command::SUCCESS;
		}
		catch (BookNotFoundException $e)
		{
			$io->error($e->getMessage());

			return Command::FAILURE;
		}
	}
}
