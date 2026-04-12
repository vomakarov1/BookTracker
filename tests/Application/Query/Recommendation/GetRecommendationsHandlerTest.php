<?php

declare(strict_types=1);

namespace BookTracker\Tests\Application\Query\Recommendation;

use BookTracker\Application\Query\Recommendation\GetRecommendationsHandler;
use BookTracker\Application\Query\Recommendation\GetRecommendationsQuery;
use BookTracker\Domain\Entity\Book;
use BookTracker\Domain\Entity\ReadingEntry;
use BookTracker\Domain\Entity\User;
use BookTracker\Domain\Enum\ReadingStatus;
use BookTracker\Domain\Service\RecommendationService;
use BookTracker\Domain\ValueObject\ReadingEntryRating;
use BookTracker\Tests\Domain\Service\Stub\StubDistanceMetric;
use BookTracker\Tests\Domain\Service\Stub\StubVectorizer;
use BookTracker\Tests\Stub\InMemoryBookRepository;
use BookTracker\Tests\Stub\InMemoryReadingEntryRepository;
use PHPUnit\Framework\TestCase;

final class GetRecommendationsHandlerTest extends TestCase
{
	private InMemoryBookRepository $bookRepository;
	private InMemoryReadingEntryRepository $entryRepository;
	private User $user;

	protected function setUp(): void
	{
		$this->bookRepository = new InMemoryBookRepository();
		$this->entryRepository = new InMemoryReadingEntryRepository();
		$this->user = new User('u1', 'Alice', 'alice@example.com');
	}

	private function makeHandler(array $vectors): GetRecommendationsHandler
	{
		$service = new RecommendationService(
			new StubVectorizer($vectors),
			new StubDistanceMetric(),
		);

		return new GetRecommendationsHandler(
			$this->entryRepository,
			$this->bookRepository,
			$service,
		);
	}

	private function makeFinishedEntry(string $id, Book $book, int $rating): ReadingEntry
	{
		$entry = ReadingEntry::create($id, $this->user, $book);
		$entry->changeStatus(ReadingStatus::READING);
		$entry->changeStatus(ReadingStatus::FINISHED);
		$entry->rate(new ReadingEntryRating($rating));

		return $entry;
	}

	public function testReturnsRecommendationsForUserWhoReadTwoBooks(): void
	{
		$read1 = new Book('b1', 'Read Book One', 'Author A', 'Tech', 7);
		$read2 = new Book('b2', 'Read Book Two', 'Author B', 'Tech', 6);
		$candidate1 = new Book('b3', 'Candidate One', 'Author C', 'Tech', 5);
		$candidate2 = new Book('b4', 'Candidate Two', 'Author D', 'Tech', 4);
		$candidate3 = new Book('b5', 'Candidate Three', 'Author E', 'Tech', 8);

		$this->bookRepository->save($read1);
		$this->bookRepository->save($read2);
		$this->bookRepository->save($candidate1);
		$this->bookRepository->save($candidate2);
		$this->bookRepository->save($candidate3);

		$entry1 = $this->makeFinishedEntry('e1', $read1, 8);
		$entry2 = $this->makeFinishedEntry('e2', $read2, 7);
		$this->entryRepository->save($entry1);
		$this->entryRepository->save($entry2);

		$vectors = [
			'b1' => [1.0, 0.0],
			'b2' => [0.9, 0.0],
			'b3' => [1.1, 0.0],
			'b4' => [1.2, 0.0],
			'b5' => [5.0, 5.0],
		];

		$handler = $this->makeHandler($vectors);
		$result = $handler->handle(new GetRecommendationsQuery('u1', 5));

		self::assertNotEmpty($result);
		self::assertCount(3, $result);

		foreach ($result as $dto)
		{
			self::assertIsFloat($dto->score);
			self::assertNotEmpty($dto->reason);
			self::assertNotNull($dto->book);
		}
	}

	public function testReturnsEmptyArrayForUserWithNoReadingHistory(): void
	{
		$candidate = new Book('b1', 'Some Book', 'Author', 'Fiction', 5);
		$this->bookRepository->save($candidate);

		$handler = $this->makeHandler(['b1' => [1.0, 0.0]]);
		$result = $handler->handle(new GetRecommendationsQuery('u1', 5));

		self::assertSame([], $result);
	}
}
