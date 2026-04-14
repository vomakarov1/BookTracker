<?php

declare(strict_types=1);

namespace BookTracker\Adapters\Cli;

use BookTracker\Application\Command\Book\CreateBookCommand;
use BookTracker\Application\Command\Book\CreateBookHandler;
use BookTracker\Application\Exception\ValidationException;
use BookTracker\Application\Port\IdGeneratorInterface;
use BookTracker\Domain\Exception\DuplicateBookException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'book:create', description: 'Create a new book')]
final class CreateBookCliCommand extends Command
{
	public function __construct(
		private readonly CreateBookHandler $handler,
		private readonly IdGeneratorInterface $idGenerator,
	)
	{
		parent::__construct();
	}

	protected function configure(): void
	{
		$this
			->addOption('title', null, InputOption::VALUE_REQUIRED, 'Book title')
			->addOption('author', null, InputOption::VALUE_REQUIRED, 'Book author')
			->addOption('category', null, InputOption::VALUE_REQUIRED, 'Book category')
			->addOption('complexity', null, InputOption::VALUE_REQUIRED, 'Book complexity (1-10)')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$io = new SymfonyStyle($input, $output);

		$title = $input->getOption('title');
		$author = $input->getOption('author');
		$category = $input->getOption('category');
		$complexity = $input->getOption('complexity');

		$title = is_string($title) ? $title : (string)$io->ask('Book title');
		$author = is_string($author) ? $author : (string)$io->ask('Author');
		$category = is_string($category) ? $category : (string)$io->ask('Category');
		$complexity = $complexity ?? $io->ask('Complexity (1-10)');

		try
		{
			$id = $this->idGenerator->generate();

			$command = new CreateBookCommand(
				id: $id,
				title: $title,
				author: $author,
				category: $category,
				complexity: (int)$complexity,
			);

			$this->handler->handle($command);

			$io->success(sprintf('Book created with ID: %s', $id));

			return Command::SUCCESS;
		}
		catch (ValidationException|DuplicateBookException $e)
		{
			$io->error($e->getMessage());

			return Command::FAILURE;
		}
	}
}
