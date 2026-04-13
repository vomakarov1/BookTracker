<?php

declare(strict_types=1);

namespace BookTracker\Adapters\Cli;

use BookTracker\Application\Query\User\GetUsersListHandler;
use BookTracker\Application\Query\User\GetUsersListQuery;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'user:list', description: 'List all users')]
final class ListUsersCliCommand extends Command
{
	public function __construct(
		private readonly GetUsersListHandler $handler,
	)
	{
		parent::__construct();
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$io = new SymfonyStyle($input, $output);

		$users = $this->handler->handle(new GetUsersListQuery());

		$rows = [];

		foreach ($users as $user)
		{
			$rows[] = [$user->id, $user->name, $user->email];
		}

		$io->table(['ID', 'Name', 'Email'], $rows);

		return Command::SUCCESS;
	}
}
