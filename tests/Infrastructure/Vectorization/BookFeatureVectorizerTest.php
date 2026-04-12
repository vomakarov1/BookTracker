<?php

declare(strict_types=1);

namespace BookTracker\Tests\Infrastructure\Vectorization;

use BookTracker\Domain\Entity\Book;
use BookTracker\Infrastructure\Vectorization\BookFeatureVectorizer;
use PHPUnit\Framework\TestCase;

final class BookFeatureVectorizerTest extends TestCase
{
	private BookFeatureVectorizer $vectorizer;

	protected function setUp(): void
	{
		$this->vectorizer = new BookFeatureVectorizer();
	}

	private function makeBook(string $category, int $complexity = 5, string $author = 'Author'): Book
	{
		return new Book('id', 'Title', $author, $category, $complexity);
	}

	public function testFictionCategoryFirstElementIsOne(): void
	{
		$vector = $this->vectorizer->vectorize($this->makeBook('fiction'));

		$this->assertSame(1.0, $vector[0]);

		for ($i = 1; $i <= 9; $i++)
		{
			$this->assertSame(0.0, $vector[$i], "Category element $i should be 0.0 for fiction");
		}
	}

	public function testTechCategorySeventhElementIsOne(): void
	{
		$vector = $this->vectorizer->vectorize($this->makeBook('tech'));

		$this->assertSame(1.0, $vector[6]);

		foreach ([0, 1, 2, 3, 4, 5, 7, 8, 9] as $i)
		{
			$this->assertSame(0.0, $vector[$i], "Category element $i should be 0.0 for tech");
		}
	}

	public function testUnknownCategoryAllCategoryElementsAreZero(): void
	{
		$vector = $this->vectorizer->vectorize($this->makeBook('unknown-category'));

		for ($i = 0; $i <= 9; $i++)
		{
			$this->assertSame(0.0, $vector[$i], "Category element $i should be 0.0 for unknown category");
		}
	}

	public function testComplexityFiveNormalizesToHalf(): void
	{
		$vector = $this->vectorizer->vectorize($this->makeBook('fiction', 5));

		$this->assertEqualsWithDelta(0.5, $vector[10], 1e-9);
	}

	public function testSameAuthorProducesSameAuthorElement(): void
	{
		$author = 'Fyodor Dostoevsky';
		$v1 = $this->vectorizer->vectorize($this->makeBook('fiction', 5, $author));
		$v2 = $this->vectorizer->vectorize($this->makeBook('tech', 3, $author));

		$this->assertSame($v1[11], $v2[11]);
	}

	public function testDifferentAuthorsProduceDifferentAuthorElements(): void
	{
		$v1 = $this->vectorizer->vectorize($this->makeBook('fiction', 5, 'Leo Tolstoy'));
		$v2 = $this->vectorizer->vectorize($this->makeBook('fiction', 5, 'Fyodor Dostoevsky'));

		$this->assertNotSame($v1[11], $v2[11]);
	}

	public function testVectorLengthIsAlwaysTwelve(): void
	{
		foreach (['fiction', 'tech', 'unknown', 'romance'] as $category)
		{
			$vector = $this->vectorizer->vectorize($this->makeBook($category));
			$this->assertCount(12, $vector, "Vector must have 12 elements for category '$category'");
		}
	}
}
