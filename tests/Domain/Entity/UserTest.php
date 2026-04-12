<?php

declare(strict_types=1);

namespace BookTracker\Tests\Domain\Entity;

use BookTracker\Domain\Entity\User;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class UserTest extends TestCase
{
	public function testCreateWithValidData(): void
	{
		$user = new User('1', 'John Doe', 'john@example.com');

		$this->assertSame('1', $user->getId());
		$this->assertSame('John Doe', $user->getName());
		$this->assertSame('john@example.com', $user->getEmail());
	}

	public function testCreateWithEmptyNameThrows(): void
	{
		$this->expectException(InvalidArgumentException::class);
		new User('1', '', 'john@example.com');
	}

	public function testCreateWithEmptyEmailThrows(): void
	{
		$this->expectException(InvalidArgumentException::class);
		new User('1', 'John Doe', '');
	}
}
