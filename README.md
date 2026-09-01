<div align="center">

<picture>
  <source media="(prefers-color-scheme: dark)" srcset="docs/brand/lacuna-logo-tagline-white.svg">
  <img src="docs/brand/lacuna-logo-tagline.svg" alt="Lacuna - knows what it doesn't know" width="320">
</picture>

<br>

**A self-hosted knowledge base for small teams that keeps track of what's missing from it.**

Built with Laravel and pgvector.

[![License](https://img.shields.io/badge/license-MIT-green.svg?style=flat-square)](LICENSE)
[![PHP](https://img.shields.io/badge/PHP-8.5-777BB4.svg?style=flat-square&logo=php&logoColor=white)](https://php.net)
[![Laravel](https://img.shields.io/badge/Laravel-13-FF2D20.svg?style=flat-square&logo=laravel&logoColor=white)](https://laravel.com)
[![PostgreSQL](https://img.shields.io/badge/PostgreSQL-pgvector-4169E1.svg?style=flat-square&logo=postgresql&logoColor=white)](https://github.com/pgvector/pgvector)
[![Status](https://img.shields.io/badge/status-in%20development-orange.svg?style=flat-square)](#roadmap)
[![tests](https://github.com/luismacnunes/lacuna/actions/workflows/tests.yml/badge.svg)](https://github.com/luismacnunes/lacuna/actions/workflows/tests.yml)

</div>

---

Internal documentation has a habit of going stale quietly. Someone writes it once, things change around it, and nobody finds out until a question goes unanswered.

Lacuna answers questions like any knowledge base. The difference is what it does when it can't: instead of a dead end, you get a task for someone to fill the gap.

<!-- GIF of the ask -> gap -> queue cycle -->

## What it does

🔍 **Answers with sources.** Ask in plain language, get an answer with the passages it came from, so you can check it.

🕳️ **Tries hard not to bluff.** When the material doesn't cover something, it's meant to say so rather than improvise. It gets this right most of the time, and the cases where it doesn't are the ones I care most about.

📋 **Turns gaps into a queue.** A question it couldn't answer becomes a task, ranked above the rest, because someone actually needed that answer.

💬 **Asks you questions when you upload.** Drop in a document and it comes back with three or four things the document doesn't explain. Answer them whenever you have time.

✍️ **Human answers rank first.** Write an answer to a queued question and it goes into the index, above the raw documents.

🔔 **Flags answers for review.** Edit a source document and the curated answers in that topic get marked for a second look, with a one-click "still fine".

🔌 **Swappable models.** OpenAI works out of the box. Embeddings and generation sit behind interfaces, so pointing it at a local model is a config change plus one class.

## How it works

Two loops, feeding each other.

**Someone asks something.** If the material covers it, you get an answer with sources. If it doesn't, the system says so, and the question lands in the queue as work for someone.

**Someone adds material.** It gets chunked, embedded, and the model reads it looking for holes: why was this decided, what are the exceptions, what happens when it breaks. Those questions land in the same queue too.

Answer anything in the queue and it gets indexed. Next person to ask gets served.

The base grows around what people ask, not around what someone guessed would matter.

## Why not just RAG over a folder

Because RAG only finds what someone already wrote down, and in a small team a lot of what matters was never written. It's in the head of whoever built the thing.

That's what the questions on upload are for. You drop in a file with a one-line note and the system does the work of figuring out what's missing.

Tettra and Guru already do the other half of this well, turning unanswered questions into articles. Interviewing the author while the context is still fresh is the part I haven't come across elsewhere, and it's the part this project is really about.

## The hard bit

Detecting that you *don't* know something is harder than answering.

The obvious approach is a distance threshold: if nothing close enough comes back, you don't have the answer. It doesn't hold up. In this corpus, a question with no answer anywhere scored **0.516**, while a question the docs answer in full scored **0.441**. No line separates them, because embedding distance tells you whether something is on-topic, not whether the answer is in there.

What worked better was asking the model to *classify* rather than *judge*: `direct`, `negative_rule`, or `not_in_material`. Three labels, and the app decides from the label. Two rounds of explaining the same distinction in prose kept fixing one error and creating another.

There's more of it, including a ranking tweak that felt obviously right and quietly cost five points of recall until the eval harness caught it.

📐 **[Read the decision records →](docs/decisions/)** - five of them, with the numbers behind each one and the alternatives that didn't work.

## Try it

```bash
git clone git@github.com:luismacnunes/lacuna.git
cd lacuna
composer install && npm install && npm run build
cp .env.example .env && php artisan key:generate
```

Create a PostgreSQL database with the `vector` extension available, point `.env` at it, then:

```bash
php artisan migrate
php artisan db:seed --class=DemoKnowledgeSeeder
php artisan queue:work --stop-when-empty
```

The seed data is fifteen documents of realistic internal docs, so you can start asking it things right away.

**No API key?** Leave `EMBEDDING_DRIVER=fake` and everything except answer generation works, on a hash-based provider that fakes the vectors. Retrieval quality is much worse, but the pipeline runs end to end. For the real thing, set `EMBEDDING_DRIVER=openai`, add your `OPENAI_API_KEY`, and run `php artisan lacuna:reindex`.

<details>
<summary>Checking retrieval quality</summary>

<br>

```bash
php artisan lacuna:eval
```

Runs 20 questions with known correct sources and reports how often the right document came back first, and how often it came back in the top three. Useful for comparing embedding models, chunking strategies or prompt changes before and after.

For reference on the demo corpus: the hash-based provider gets 20% and 55%. `text-embedding-3-small` gets 80% and 90%. It's a small corpus, so treat the numbers as a way to spot regressions rather than as a benchmark.

Questions live in `tests/Fixtures/eval_questions.json`.

</details>

<details>
<summary>Setup on Arch Linux</summary>

<br>

pgvector isn't in the official repos and PostgreSQL needs initialising by hand.

```bash
sudo pacman -S php php-pgsql composer postgresql base-devel git

# both PostgreSQL extensions ship commented out
sudo sed -i 's/^;extension=pdo_pgsql/extension=pdo_pgsql/; s/^;extension=pgsql/extension=pgsql/' /etc/php/php.ini

sudo -u postgres initdb -D /var/lib/postgres/data --locale=C.UTF-8 --encoding=UTF8
sudo systemctl enable --now postgresql
sudo -u postgres createuser -s $USER
createdb lacuna

# pgvector from source
cd /tmp && git clone --branch v0.8.0 https://github.com/pgvector/pgvector.git
cd pgvector && make && sudo make install
```

`initdb` sets local connections to `trust`, so any local process can connect as any role without a password. Fine on a dev box, not anywhere else. It also means `psql -d lacuna` works with no flags.

Check pgvector is there before migrating, because the first migration enables it:

```bash
psql -d lacuna -c "SELECT name FROM pg_available_extensions WHERE name = 'vector';"
```

Then set `DB_USERNAME` to your system user, leave `DB_PASSWORD` empty, and carry on.

</details>

<details>
<summary>Setup on macOS</summary>

<br>

Anything that serves PostgreSQL over TCP works. If you use Yerd, note it doesn't do Unix sockets, so every `psql` and `createdb` needs the connection flags:

```bash
createdb -h 127.0.0.1 -p 5432 -U postgres lacuna
psql -h 127.0.0.1 -p 5432 -U postgres -d lacuna -c "SELECT name FROM pg_available_extensions WHERE name = 'vector';"
```

You'll also want `libpq` on your PATH for `psql` itself:

```bash
brew install libpq
echo 'export PATH="/opt/homebrew/opt/libpq/bin:$PATH"' >> ~/.zshrc
```

</details>

## What it doesn't do yet

Worth knowing before you point it at anything real.

- **No permissions.** Everyone who can log in sees everything.
- **Plain text and pasted code only.** PDF and Word aren't wired up.
- **Answers aren't versioned.** Editing a curated answer overwrites the old one.
- **Staleness is coarse.** A changed document flags every curated answer in its topic, not just the ones that actually depended on it. Deliberate, and [explained here](docs/decisions/005-staleness-at-topic-level.md).
- **Your documents leave your network** with `EMBEDDING_DRIVER=openai`. The app self-hosts, the model doesn't. Point it at a local model if that matters.
- **Nobody's running this in production**, including me. It works and it's tested, but it hasn't met real users yet.

## Built with

Laravel 13, PHP 8.5, PostgreSQL with pgvector, Blade, Pest.

Embeddings and text generation sit behind interfaces, wired through the service container and picked by an env variable. That's also what makes the test suite cheap to run: a fake provider stands in for both, so nothing hits an API.

## Roadmap

| | |
|:---:|---|
| ✅ | Upload, chunking, embeddings on a queue |
| ✅ | Search with visible similarity scores |
| ✅ | Answers with citations, and knowing when not to answer |
| ✅ | Topics, the pending queue, questions on upload |
| ✅ | Curation, human answers ranked above raw documents |
| ✅ | Flagging answers for review when their sources change |
| ✅ | Test suite and CI |
| ⬜ | Coverage over time, the chart that shows the base filling in |
| ⬜ | Version history on curated answers |
| ⬜ | Grouping near-duplicate questions inside a topic |
| ⬜ | Permissions per area |
| ⬜ | PDF and Word ingestion |

## Contributing

Bug reports and arguments about the design are both welcome. The reasoning behind each decision is in [docs/decisions](docs/decisions/), worth a look first, since the alternative you're about to suggest may already have been tried and measured. See [CONTRIBUTING.md](CONTRIBUTING.md).

## Licence

MIT. Do what you like with it.