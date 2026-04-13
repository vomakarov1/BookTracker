<?php

declare(strict_types=1);

namespace BookTracker\Adapters\Cli;

use BookTracker\Application\Command\Book\CreateBookCommand;
use BookTracker\Application\Command\Book\CreateBookHandler;
use BookTracker\Application\Exception\ValidationException;
use BookTracker\Application\Port\IdGeneratorInterface;
use BookTracker\Domain\Exception\DuplicateBookException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

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
			->setName('book:create')
			->setDescription('Create a new book')
			->addOption('title', null, InputOption::VALUE_REQUIRED, 'Book title')
			->addOption('author', null, InputOption::VALUE_REQUIRED, 'Book author')
			->addOption('category', null, InputOption::VALUE_REQUIRED, 'Book category')
			->addOption('complexity', null, InputOption::VALUE_REQUIRED, 'Book complexity (1-10)')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$title = $input->getOption('title');
		$author = $input->getOption('author');
		$category = $input->getOption('category');
		$complexity = $input->getOption('complexity');

		if (!is_string($title) || !is_string($author) || !is_string($category) || $complexity === null)
		{
			$output->writeln('<error>Options --title, --author, --category, --complexity are required.</error>');

			return Command::FAILURE;
		}

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

			$output->writeln(sprintf('Book created with ID: %s', $id));

			return Command::SUCCESS;
		}
		catch (ValidationException|DuplicateBookException $e)
		{
			$output->writeln(sprintf('<error>%s</error>', $e->getMessage()));

			return Command::FAILURE;
		}
	}
}
