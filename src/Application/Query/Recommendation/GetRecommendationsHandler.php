<?php

declare(strict_types=1);

namespace BookTracker\Application\Query\Recommendation;

use BookTracker\Application\DTO\BookDTO;
use BookTracker\Application\DTO\BookDTOAssembler;
use BookTracker\Application\DTO\RecommendationDTO;
use BookTracker\Domain\Repository\BookRepositoryInterface;
use BookTracker\Domain\Repository\ReadingEntryRepositoryInterface;
use BookTracker\Domain\Service\RecommendationResult;
use BookTracker\Domain\Service\RecommendationService;

final class GetRecommendationsHandler
{
	public function __construct(
		private readonly ReadingEntryRepositoryInterface $readingEntryRepository,
		private readonly BookRepositoryInterface $bookRepository,
		private readonly RecommendationService $recommendationService,
	)
	{
	}

	/** @return array<RecommendationDTO> */
	public function handle(GetRecommendationsQuery $query): array
	{
		$readingHistory = $this->readingEntryRepository->getByUserId($query->userId);

		$bookIds = array_map(static fn($entry) => $entry->getBookId(), $readingHistory);
		$readBooks = array_values($this->bookRepository->getByIds($bookIds));

		$candidates = $this->bookRepository->getAll();

		$results = $this->recommendationService->recommend(
			readingHistory: $readingHistory,
			readBooks: $readBooks,
			candidateBooks: $candidates,
			limit: $query->limit,
		);

		return array_map(
			static fn(RecommendationResult $r) => new RecommendationDTO(
				book: BookDTOAssembler::fromEntity($r->book),
				score: $r->score,
				reason: $r->reason,
			),
			$results,
		);
	}
}
