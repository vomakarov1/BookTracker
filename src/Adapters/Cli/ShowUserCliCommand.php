<?php

declare(strict_types=1);

namespace BookTracker\Adapters\Cli;

use BookTracker\Application\Query\User\GetUserHandler;
use BookTracker\Application\Query\User\GetUserQuery;
use BookTracker\Domain\Exception\UserNotFoundException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

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
			->setName('user:show')
			->setDescription('Show user details')
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
			$user = $this->handler->handle(new GetUserQuery($id));

			$output->writeln(sprintf('ID:    %s', $user->id));
			$output->writeln(sprintf('Name:  %s', $user->name));
			$output->writeln(sprintf('Email: %s', $user->email));

			return Command::SUCCESS;
		}
		catch (UserNotFoundException $e)
		{
			$output->writeln(sprintf('<error>%s</error>', $e->getMessage()));

			return Command::FAILURE;
		}
	}
}
