<?php

declare(strict_types=1);

namespace BookTracker\Infrastructure\Repository;

use BookTracker\Domain\Entity\Book;
use BookTracker\Domain\Exception\BookNotFoundException;
use BookTracker\Domain\Repository\BookRepositoryInterface;
use BookTracker\Domain\ValueObject\BookComplexity;
use BookTracker\Infrastructure\Storage\JsonFileStorage;
use JsonException;

final class JsonBookRepository implements BookRepositoryInterface
{
	public function __construct(private readonly JsonFileStorage $storage)
	{
	}

	/**
	 * @param array<string, mixed> $row
	 */
	private function hydrate(array $row): Book
	{
		return new Book(
			id: (string)$row['id'],
			title: (string)$row['title'],
			author: (string)$row['author'],
			category: (string)$row['category'],
			complexity: new BookComplexity((int)$row['complexity']),
		);
	}

	/**
	 * @return array<string, mixed>
	 */
	private function toRow(Book $book): array
	{
		return [
			'id' => $book->getId(),
			'title' => $book->getTitle(),
			'author' => $book->getAuthor(),
			'category' => $book->getCategory(),
			'complexity' => $book->getComplexity(),
		];
	}

	/**
	 * @throws JsonException
	 */
	public function getById(string $id): Book
	{
		foreach ($this->storage->load() as $row)
		{
			if ((string)$row['id'] === $id)
			{
				return $this->hydrate($row);
			}
		}

		throw new BookNotFoundException(sprintf('Book "%s" not found.', $id));
	}

	/**
	 * @return array<string, Book>
	 * @throws JsonException
	 */
	public function getByIds(array $ids): array
	{
		if ($ids === [])
		{
			return [];
		}

		$index = array_flip($ids);
		$result = [];

		foreach ($this->storage->load() as $row)
		{
			$id = (string)$row['id'];
			if (isset($index[$id]))
			{
				$result[$id] = $this->hydrate($row);
			}
		}

		return $result;
	}

	/**
	 * @return array<Book>
	 * @throws JsonException
	 */
	public function getAll(): array
	{
		$result = [];

		foreach ($this->storage->load() as $row)
		{
			$result[] = $this->hydrate($row);
		}

		return $result;
	}

	/**
	 * @throws JsonException
	 */
	public function save(Book $book): void
	{
		$data = $this->storage->load();

		foreach ($data as $i => $row)
		{
			if ((string)$row['id'] === $book->getId())
			{
				$data[$i] = $this->toRow($book);
				$this->storage->write($data);

				return;
			}
		}

		$data[] = $this->toRow($book);
		$this->storage->write($data);
	}

	/**
	 * @throws JsonException
	 */
	public function delete(string $id): void
	{
		$data = $this->storage->load();

		foreach ($data as $i => $row)
		{
			if ((string)$row['id'] === $id)
			{
				unset($data[$i]);
				$this->storage->write($data);

				return;
			}
		}

		throw new BookNotFoundException(sprintf('Book "%s" not found.', $id));
	}

	/**
	 * @throws JsonException
	 */
	public function existsByTitle(string $title): bool
	{
		foreach ($this->storage->load() as $row)
		{
			if ((string)$row['title'] === $title)
			{
				return true;
			}
		}

		return false;
	}

}
