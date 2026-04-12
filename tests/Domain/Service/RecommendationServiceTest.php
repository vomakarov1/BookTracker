<?php

declare(strict_types=1);

namespace BookTracker\Tests\Domain\Service;

use BookTracker\Domain\Entity\Book;
use BookTracker\Domain\Entity\ReadingEntry;
use BookTracker\Domain\Entity\User;
use BookTracker\Domain\Enum\ReadingStatus;
use BookTracker\Domain\Service\RecommendationService;
use BookTracker\Domain\ValueObject\ReadingEntryRating;
use BookTracker\Tests\Domain\Service\Stub\StubDistanceMetric;
use BookTracker\Tests\Domain\Service\Stub\StubVectorizer;
use PHPUnit\Framework\TestCase;

final class RecommendationServiceTest extends TestCase
{
	private User $user;

	protected function setUp(): void
	{
		$this->user = new User('u1', 'Test User', 'test@example.com');
	}

	private function makeBook(string $id, string $title, string $author = 'Author'): Book
	{
		return new Book($id, $title, $author, 'Fiction', 5);
	}

	private function makeFinishedEntry(string $id, Book $book, int $rating): ReadingEntry
	{
		$entry = ReadingEntry::create($id, $this->user, $book);
		$entry->changeStatus(ReadingStatus::READING);
		$entry->changeStatus(ReadingStatus::FINISHED);
		$entry->rate(new ReadingEntryRating($rating));

		return $entry;
	}

	private function makePlannedEntry(string $id, Book $book): ReadingEntry
	{
		return ReadingEntry::create($id, $this->user, $book);
	}

	public function testAlreadyReadBooksAreNotRecommended(): void
	{
		$book = $this->makeBook('b1', 'Read Book');
		$entry = $this->makeFinishedEntry('e1', $book, 8);

		$service = new RecommendationService(
			new StubVectorizer(['b1' => [1.0, 0.0]]),
			new StubDistanceMetric(),
		);

		$results = $service->recommend([$entry], [$book], [$book]);

		$this->assertSame([], $results);
	}

	public function testAuthorWithThreeLowRatedBooksIsFiltered(): void
	{
		$badAuthor = 'Bad Author';
		$bookA = $this->makeBook('b1', 'Book A', $badAuthor);
		$bookB = $this->makeBook('b2', 'Book B', $badAuthor);
		$bookC = $this->makeBook('b3', 'Book C', $badAuthor);
		$candidate = $this->makeBook('b4', 'Candidate', $badAuthor);

		$history = [
			$this->makeFinishedEntry('e1', $bookA, 2),
			$this->makeFinishedEntry('e2', $bookB, 2),
			$this->makeFinishedEntry('e3', $bookC, 2),
		];
		$readBooks = [$bookA, $bookB, $bookC];

		$service = new RecommendationService(
			new StubVectorizer(
				[
					'b1' => [1.0, 0.0],
					'b2' => [1.0, 0.0],
					'b3' => [1.0, 0.0],
					'b4' => [1.0, 0.0],
				]
			),
			new StubDistanceMetric(),
		);

		$results = $service->recommend($history, $readBooks, [$candidate]);

		$this->assertSame([], $results);
	}

	public function testAuthorWithTwoLowRatedBooksIsNotFiltered(): void
	{
		$author = 'Borderline Author';
		$bookA = $this->makeBook('b1', 'Book A', $author);
		$bookB = $this->makeBook('b2', 'Book B', $author);
		$referenceBook = $this->makeBook('b3', 'High Rated', 'Other Author');
		$candidate = $this->makeBook('b4', 'Candidate', $author);

		$history = [
			$this->makeFinishedEntry('e1', $bookA, 2),
			$this->makeFinishedEntry('e2', $bookB, 2),
			$this->makeFinishedEntry('e3', $referenceBook, 9),
		];
		$readBooks = [$bookA, $bookB, $referenceBook];

		$service = new RecommendationService(
			new StubVectorizer(
				[
					'b3' => [1.0, 0.0],
					'b4' => [1.1, 0.0],
				]
			),
			new StubDistanceMetric(),
		);

		$results = $service->recommend($history, $readBooks, [$candidate]);

		$this->assertCount(1, $results);
		$this->assertSame('b4', $results[0]->book->getId());
	}

	public function testBooksCloserToHighRatedAppearHigherInResults(): void
	{
		$highRated = $this->makeBook('b1', 'High Rated Book');
		$entry = $this->makeFinishedEntry('e1', $highRated, 9);

		$nearCandidate = $this->makeBook('b2', 'Near Candidate');
		$farCandidate = $this->makeBook('b3', 'Far Candidate');

		$service = new RecommendationService(
			new StubVectorizer(
				[
					'b1' => [1.0, 0.0],
					'b2' => [1.1, 0.0],  // distance ≈ 0.1 from b1
					'b3' => [5.0, 5.0],  // distance ≈ 7.07 from b1
				]
			),
			new StubDistanceMetric(),
		);

		$results = $service->recommend([$entry], [$highRated], [$nearCandidate, $farCandidate]);

		$this->assertCount(2, $results);
		$this->assertSame('b2', $results[0]->book->getId());
		$this->assertSame('b3', $results[1]->book->getId());
		$this->assertGreaterThan($results[1]->score, $results[0]->score);
	}

	public function testLimitRestrictsNumberOfResults(): void
	{
		$highRated = $this->makeBook('b1', 'Reference Book');
		$entry = $this->makeFinishedEntry('e1', $highRated, 8);

		$candidates = [
			$this->makeBook('b2', 'Candidate 1'),
			$this->makeBook('b3', 'Candidate 2'),
			$this->makeBook('b4', 'Candidate 3'),
		];

		$vectors = [
			'b1' => [0.0, 0.0],
			'b2' => [1.0, 0.0],
			'b3' => [2.0, 0.0],
			'b4' => [3.0, 0.0],
		];

		$service = new RecommendationService(
			new StubVectorizer($vectors),
			new StubDistanceMetric(),
		);

		$results = $service->recommend([$entry], [$highRated], $candidates, 2);

		$this->assertCount(2, $results);
	}

	public function testEmptyReadingHistoryReturnsEmptyArray(): void
	{
		$candidate = $this->makeBook('b1', 'Some Book');

		$service = new RecommendationService(
			new StubVectorizer(['b1' => [1.0, 0.0]]),
			new StubDistanceMetric(),
		);

		$results = $service->recommend([], [], [$candidate]);

		$this->assertSame([], $results);
	}

	public function testAllCandidatesAlreadyReadReturnsEmptyArray(): void
	{
		$bookA = $this->makeBook('b1', 'Book A');
		$bookB = $this->makeBook('b2', 'Book B');

		$history = [
			$this->makeFinishedEntry('e1', $bookA, 8),
			$this->makeFinishedEntry('e2', $bookB, 7),
		];

		$service = new RecommendationService(
			new StubVectorizer(
				[
					'b1' => [1.0, 0.0],
					'b2' => [2.0, 0.0],
				]
			),
			new StubDistanceMetric(),
		);

		$results = $service->recommend($history, [$bookA, $bookB], [$bookA, $bookB]);

		$this->assertSame([], $results);
	}

	public function testReasonContainsTitleOfClosestBook(): void
	{
		$highRated = $this->makeBook('b1', 'Clean Code');
		$entry = $this->makeFinishedEntry('e1', $highRated, 9);
		$candidate = $this->makeBook('b2', 'Refactoring');

		$service = new RecommendationService(
			new StubVectorizer(
				[
					'b1' => [1.0, 0.0],
					'b2' => [1.1, 0.0],
				]
			),
			new StubDistanceMetric(),
		);

		$results = $service->recommend([$entry], [$highRated], [$candidate]);

		$this->assertCount(1, $results);
		$this->assertStringContainsString('Clean Code', $results[0]->reason);
	}

	public function testFallbackToAllReadBooksWhenNoHighRatedEntries(): void
	{
		$readBook = $this->makeBook('b1', 'Low Rated Book');
		$entry = $this->makeFinishedEntry('e1', $readBook, 5);
		$candidate = $this->makeBook('b2', 'Candidate');

		$service = new RecommendationService(
			new StubVectorizer(
				[
					'b1' => [1.0, 0.0],
					'b2' => [1.1, 0.0],
				]
			),
			new StubDistanceMetric(),
		);

		$results = $service->recommend([$entry], [$readBook], [$candidate]);

		$this->assertCount(1, $results);
		$this->assertSame('b2', $results[0]->book->getId());
	}
}
