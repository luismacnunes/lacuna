# Contributing

Lacuna is a personal project exploring one idea: that a knowledge base should keep track of what it doesn't know, not just what it does. It's built in the open, but it isn't looking for feature contributions yet.

Some things are genuinely useful though.

## What helps

**Bug reports.** If something breaks, an issue with steps to reproduce it is worth a lot.

**Arguments about the design.** Gap detection is the interesting part of this and it isn't settled. If you've built something similar and think a decision is wrong, open an issue. The reasoning behind each one is in [`docs/decisions/`](docs/decisions/), worth a look first since the alternative you're about to suggest may already have been tried.

**Evaluation numbers.** If you run `php artisan lacuna:eval` against a different corpus, a different chunking strategy or a different embedding model, I'd like to see the results. Especially in languages other than Portuguese and English, since that's all it's been tested on.

## What doesn't, right now

Feature pull requests. The roadmap ordering is deliberate and several items depend on decisions I haven't made yet. If you want to build one, open an issue first so we don't write the same thing twice, differently.

## Getting set up

Full instructions are in the README. Short version:

```bash
composer install
npm install && npm run build
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed --class=DemoKnowledgeSeeder
```

Leave `EMBEDDING_DRIVER=fake` to run the pipeline without an API key.

## Before opening a pull request

Run the tests:

```bash
./vendor/bin/pest
```

If your change touches retrieval, chunking or the answer prompt, run the eval too:

```bash
php artisan lacuna:eval
```

and put the before and after numbers in the PR. A change that improves one question and quietly breaks three others is the exact failure this project keeps running into, so it's worth checking.

## Commits

First line under 72 characters, imperative, saying what the commit does. Body explaining why, when the why isn't obvious. If the change introduces a known limitation, that goes in the body too.