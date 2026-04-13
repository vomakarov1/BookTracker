<?php

declare(strict_types=1);

namespace BookTracker\Adapters\Cli;

use BookTracker\Application\Command\User\CreateUserCommand;
use BookTracker\Application\Command\User\CreateUserHandler;
use BookTracker\Application\Exception\ValidationException;
use BookTracker\Application\Port\IdGeneratorInterface;
use BookTracker\Domain\Exception\DuplicateUserException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class CreateUserCliCommand extends Command
{
	public function __construct(
		private readonly CreateUserHandler $handler,
		private readonly IdGeneratorInterface $idGenerator,
	)
	{
		parent::__construct();
	}

	protected function configure(): void
	{
		$this
			->setName('user:create')
			->setDescription('Create a new user')
			->addOption('name', null, InputOption::VALUE_REQUIRED, 'User name')
			->addOption('email', null, InputOption::VALUE_REQUIRED, 'User email')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$name = $input->getOption('name');
		$email = $input->getOption('email');

		if (!is_string($name) || !is_string($email))
		{
			$output->writeln('<error>Options --name and --email are required.</error>');

			return Command::FAILURE;
		}

		try
		{
			$id = $this->idGenerator->generate();

			$command = new CreateUserCommand(id: $id, name: $name, email: $email);
			$this->handler->handle($command);

			$output->writeln(sprintf('User created with ID: %s', $id));

			return Command::SUCCESS;
		}
		catch (ValidationException|DuplicateUserException $e)
		{
			$output->writeln(sprintf('<error>%s</error>', $e->getMessage()));

			return Command::FAILURE;
		}
	}
}
