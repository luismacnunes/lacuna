# 003 - The curated answer boost needs a floor

**Date:** August 2026
**Status:** Accepted

## The idea

Curated answers are written by a person in response to a real gap. When both a curated answer and a document chunk are relevant, the human-written one should win.

First version: query both sources separately, add a flat `+0.15` to every curated answer before merging and sorting.

## What it cost

`php artisan lacuna:eval`, with two curated answers in the database:

| | recall@1 | recall@3 |
|---|---:|---:|
| Before any curated answers existed | 80% | 90% |
| Flat boost | 80% | **85%** |
| Boost with a floor | 80% | 90% |

Five points of recall@3, with two curated answers in the store.

The question that dropped was about editing a migration that had already run in production. The right chunk sat at 0.45; a curated answer at roughly 0.35 got boosted to 0.50 and pushed it out of the top three. The answer was still in the corpus, it just stopped being found.

## Why the first version was wrong

What I meant was *"when both are relevant, prefer the human-written one."* What I implemented was *"prefer the curated answer even when it isn't relevant."* An off-topic curated answer displacing an on-topic chunk makes the system worse in precisely the situation where retrieval matters.

The 0.15 was a guess applied without measuring. It showed up on the first eval run after the change, which is the only reason it didn't stay.

## What I did instead

The boost only applies above a relevance floor:

```php
private const CURATED_BOOST = 0.15;
private const BOOST_FLOOR = 0.45;

rank: $similarity + ($similarity >= self::BOOST_FLOOR ? self::CURATED_BOOST : 0.0)
```

Below 0.45 a curated answer competes on equal terms. Above it, it gets the advantage.

## Still open

Both numbers are hypotheses. 0.45 sits below the median similarity of correctly-retrieved chunks in the eval set and above the range where results are noise. 0.15 is enough to flip a close call without overriding a real gap. Neither has been swept properly.

They need re-measuring once there are more than a handful of curated answers, because the interference this guards against gets worse as their number grows.

## Worth noting

This is the kind of change that feels obviously correct while you're writing it. I wouldn't have caught it by hand, because the retrieval it broke was for a question nobody had asked yet, it only surfaced because the eval set had one.