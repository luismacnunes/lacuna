# Contributing

Lacuna is a personal project built to explore a specific idea: that a knowledge base should surface what it doesn't know. It's developed in the open, but it isn't looking for feature contributions at this stage.

That said, some things are genuinely welcome.

## What's useful

**Bug reports.** If something breaks, an issue with the steps to reproduce it is valuable.

**Questions about the approach.** The gap-detection design is the interesting part of this project and it isn't settled. If you've built something similar and disagree with a decision, open an issue, the reasoning behind each choice is in `docs/decisions/`.

**Retrieval evaluation.** If you run `php artisan lacuna:eval` against a different corpus, different chunking strategy, or a different embedding model, the numbers are interesting. Especially in languages other than Portuguese and English.

## What isn't, right now

Feature pull requests. The roadmap in the README is deliberate and the ordering matters, several items depend on decisions that haven't been made yet. If you want to build one of them, open an issue first so we don't both write the same thing differently.

## Setting up

See the README. The short version:

```bash
composer install
npm install && npm run build
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed --class=DemoKnowledgeSeeder
```

Set `EMBEDDING_DRIVER=fake` to run the pipeline without an API key.

## Before opening a pull request

Run the test suite:

```bash
./vendor/bin/pest
```

If your change touches retrieval, chunking, or the answer prompt, also run:

```bash
php artisan lacuna:eval
```

and include the before-and-after numbers in the pull request. A change that improves one question and quietly breaks three others is the failure mode this project is built to catch.

## Commit messages

First line under 72 characters, imperative mood, describing what the commit does. Body explaining why, when the why isn't obvious. Known limitations introduced by a change belong in the body.
