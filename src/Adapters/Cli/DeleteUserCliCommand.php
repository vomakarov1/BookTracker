<?php

declare(strict_types=1);

namespace BookTracker\Adapters\Cli;

use BookTracker\Application\Command\User\DeleteUserCommand;
use BookTracker\Application\Command\User\DeleteUserHandler;
use BookTracker\Domain\Exception\UserNotFoundException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class DeleteUserCliCommand extends Command
{
	public function __construct(
		private readonly DeleteUserHandler $handler,
	)
	{
		parent::__construct();
	}

	protected function configure(): void
	{
		$this
			->setName('user:delete')
			->setDescription('Delete a user by ID')
			->addOption('id', null, InputOption::VALUE_REQUIRED, 'User ID')
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
			$this->handler->handle(new DeleteUserCommand($id));
			$output->writeln(sprintf('User "%s" deleted.', $id));

			return Command::SUCCESS;
		}
		catch (UserNotFoundException $e)
		{
			$output->writeln(sprintf('<error>%s</error>', $e->getMessage()));

			return Command::FAILURE;
		}
	}
}
