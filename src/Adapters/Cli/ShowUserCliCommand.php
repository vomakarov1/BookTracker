<?php

declare(strict_types=1);

namespace BookTracker\Adapters\Cli;

use BookTracker\Application\Query\User\GetUserHandler;
use BookTracker\Application\Query\User\GetUserQuery;
use BookTracker\Domain\Exception\UserNotFoundException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'user:show', description: 'Show user details')]
final class ShowUserCliCommand extends Command
{
	public function __construct(
		private readonly GetUserHandler $handler,
	)
	{
		parent::__construct();
	}

	protected function configure(): void
	{
		$this
			->addOption('id', null, InputOption::VALUE_REQUIRED, 'User ID')
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
			$user = $this->handler->handle(new GetUserQuery($id));

			$io->definitionList(
				['ID' => $user->id],
				['Name' => $user->name],
				['Email' => $user->email],
			);

			return Command::SUCCESS;
		}
		catch (UserNotFoundException $e)
		{
			$io->error($e->getMessage());

			return Command::FAILURE;
		}
	}
}
