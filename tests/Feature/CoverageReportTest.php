<?php

use App\Enums\QuestionStatus;
use App\Models\Question;
use App\Services\CoverageReport;
use Carbon\CarbonImmutable;

it('calculates the answered rate per week', function () {
    CarbonImmutable::setTestNow('2026-08-26 10:00:00');

    Question::create(['text' => 'a', 'status' => QuestionStatus::Answered]);
    Question::create(['text' => 'b', 'status' => QuestionStatus::Answered]);
    Question::create(['text' => 'c', 'status' => QuestionStatus::Unanswered]);
    Question::create(['text' => 'd', 'status' => QuestionStatus::Unanswered]);

    $weeks = app(CoverageReport::class)->weekly();

    expect($weeks)->toHaveCount(1)
        ->and($weeks->first()['total'])->toBe(4)
        ->and($weeks->first()['answered'])->toBe(2)
        ->and($weeks->first()['rate'])->toBe(50);

    CarbonImmutable::setTestNow();
});

it('separates questions into different weeks', function () {
    CarbonImmutable::setTestNow('2026-08-26 10:00:00');
    Question::create(['text' => 'a', 'status' => QuestionStatus::Answered]);

    CarbonImmutable::setTestNow('2026-09-02 10:00:00');
    Question::create(['text' => 'b', 'status' => QuestionStatus::Unanswered]);

    expect(app(CoverageReport::class)->weekly())->toHaveCount(2);

    CarbonImmutable::setTestNow();
});

it('returns nothing when there are no questions', function () {
    expect(app(CoverageReport::class)->weekly())->toBeEmpty();
});