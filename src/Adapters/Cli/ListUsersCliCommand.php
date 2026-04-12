<?php

declare(strict_types=1);

namespace BookTracker\Adapters\Cli;

use BookTracker\Application\Query\User\GetUsersListHandler;
use BookTracker\Application\Query\User\GetUsersListQuery;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class ListUsersCliCommand extends Command
{
	public function __construct(
		private readonly GetUsersListHandler $handler,
	)
	{
		parent::__construct();
	}

	protected function configure(): void
	{
		$this
			->setName('user:list')
			->setDescription('List all users')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$users = $this->handler->handle(new GetUsersListQuery());

		$table = new Table($output);
		$table->setHeaders(['ID', 'Name', 'Email']);

		foreach ($users as $user)
		{
			$table->addRow([$user->id, $user->name, $user->email]);
		}

		$table->render();

		return Command::SUCCESS;
	}
}
