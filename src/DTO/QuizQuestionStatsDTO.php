<?php

namespace App\DTO;

final class QuizQuestionStatsDTO
{
    public function __construct(
        public readonly int $questionId,
        public readonly string $questionContent,
        public readonly int $totalResponses,
        public readonly int $correctCount,
    ) {
    }
}

