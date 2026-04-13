<?php

declare(strict_types=1);

namespace BookTracker\Infrastructure\Repository;

use BookTracker\Domain\Entity\Book;
use BookTracker\Domain\Exception\BookNotFoundException;
use BookTracker\Domain\Repository\BookRepositoryInterface;
use BookTracker\Domain\ValueObject\BookComplexity;
use JsonException;
use RuntimeException;

final class JsonBookRepository implements BookRepositoryInterface
{
	private string $filePath;

	public function __construct(string $storagePath)
	{
		$this->filePath = rtrim($storagePath, '/') . '/books.json';

		if (!is_dir($storagePath) && !mkdir($storagePath, 0755, true) && !is_dir($storagePath))
		{
			throw new RuntimeException(sprintf('Directory "%s" was not created', $storagePath));
		}

		if (!file_exists($this->filePath))
		{
			file_put_contents($this->filePath, '[]');
		}
	}

	/**
	 * @return array<int, array<string, mixed>>
	 */
	private function loadData(): array
	{
		$content = file_get_contents($this->filePath);

		if ($content === false)
		{
			return [];
		}

		$decoded = json_decode($content, true);

		if (!is_array($decoded))
		{
			return [];
		}

		/** @var array<int, array<string, mixed>> $decoded */
		return $decoded;
	}

	/**
	 * @param array<int, array<string, mixed>> $data
	 * @throws JsonException
	 */
	private function writeData(array $data): void
	{
		file_put_contents(
			$this->filePath,
			json_encode(array_values($data), JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
		);
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

	public function getById(string $id): Book
	{
		foreach ($this->loadData() as $row)
		{
			if ((string)$row['id'] === $id)
			{
				return $this->hydrate($row);
			}
		}

		throw new BookNotFoundException(sprintf('Book "%s" not found.', $id));
	}

	/** @return array<string, Book> */
	public function getByIds(array $ids): array
	{
		if ($ids === [])
		{
			return [];
		}

		$index = array_flip($ids);
		$result = [];

		foreach ($this->loadData() as $row)
		{
			$id = (string)$row['id'];
			if (isset($index[$id]))
			{
				$result[$id] = $this->hydrate($row);
			}
		}

		return $result;
	}

	/** @return array<Book> */
	public function getAll(): array
	{
		$result = [];

		foreach ($this->loadData() as $row)
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
		$data = $this->loadData();

		foreach ($data as $i => $row)
		{
			if ((string)$row['id'] === $book->getId())
			{
				$data[$i] = $this->toRow($book);
				$this->writeData($data);

				return;
			}
		}

		$data[] = $this->toRow($book);
		$this->writeData($data);
	}

	/**
	 * @throws JsonException
	 */
	public function delete(string $id): void
	{
		$data = $this->loadData();

		foreach ($data as $i => $row)
		{
			if ((string)$row['id'] === $id)
			{
				unset($data[$i]);
				$this->writeData($data);

				return;
			}
		}

		throw new BookNotFoundException(sprintf('Book "%s" not found.', $id));
	}

	public function existsByTitle(string $title): bool
	{
		foreach ($this->loadData() as $row)
		{
			if ((string)$row['title'] === $title)
			{
				return true;
			}
		}

		return false;
	}

}
