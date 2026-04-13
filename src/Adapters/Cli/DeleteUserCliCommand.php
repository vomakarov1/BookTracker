<?php

declare(strict_types=1);

namespace BookTracker\Adapters\Cli;

use BookTracker\Application\Command\User\DeleteUserCommand;
use BookTracker\Application\Command\User\DeleteUserHandler;
use BookTracker\Domain\Exception\UserNotFoundException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'user:delete', description: 'Delete a user by ID')]
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
			->addArgument('id', InputArgument::REQUIRED, 'User ID')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$io = new SymfonyStyle($input, $output);

		$id = (string)$input->getArgument('id');

		try
		{
			if (!$io->confirm(sprintf('Delete user "%s"? This action cannot be undone.', $id), false))
			{
				$io->note('Aborted.');

				return Command::SUCCESS;
			}

			$this->handler->handle(new DeleteUserCommand($id));

			$io->success(sprintf('User "%s" deleted.', $id));

			return Command::SUCCESS;
		}
		catch (UserNotFoundException $e)
		{
			$io->error($e->getMessage());

			return Command::FAILURE;
		}
	}
}
