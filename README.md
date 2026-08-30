<div align="center">

# Lacuna

**An internal knowledge base that knows what it doesn't know.**

Turns failed searches into work items, and interviews you about what you upload.

[![License](https://img.shields.io/badge/license-MIT-green.svg?style=flat-square)](LICENSE)
[![PHP](https://img.shields.io/badge/PHP-8.5-777BB4.svg?style=flat-square&logo=php&logoColor=white)](https://php.net)
[![Laravel](https://img.shields.io/badge/Laravel-13-FF2D20.svg?style=flat-square&logo=laravel&logoColor=white)](https://laravel.com)
[![PostgreSQL](https://img.shields.io/badge/PostgreSQL-pgvector-4169E1.svg?style=flat-square&logo=postgresql&logoColor=white)](https://github.com/pgvector/pgvector)
[![Status](https://img.shields.io/badge/status-in%20development-orange.svg?style=flat-square)](#roadmap)

</div>

---

Most knowledge bases are graveyards. Someone writes documentation once, it goes stale and nobody notices until a question goes unanswered.

Lacuna inverts that: **the most valuable thing a knowledge base can tell you is what's missing from it.**

<!-- GIF of the ask -> gap -> queue cycle -->

## The two mechanisms

**It interviews you on ingestion.** When you add material, Lacuna reads it and asks three or four questions about what the document *doesn't* explain, the reasoning behind decisions, the exception cases, what to do when things break. Answer them whenever you have time. They queue up.

**It turns failed searches into work items.** When someone asks a question the material can't answer, that failure isn't a dead end, it becomes an item in the same queue, ranked *above* the auto-generated ones, because a real question has proven demand.

The knowledge base grows around what people actually ask, not around what someone once guessed would be important.

## Why not just RAG over a folder

RAG over existing documents has a hard ceiling: it can only surface what someone already wrote down. In a small team, most of what matters was never written, it lives in the head of whoever built the thing.

The ingestion interview is a cold-start mechanism for exactly that. You drop in a file with a one-line note, and the system does the work of figuring out what's missing from it.

Tools like Tettra and Guru run the demand-driven half of this loop well. The supply-driven half, asking the author while the context is still fresh in their head is the part this project explores.

## Pipeline

```
input ---> chunking ---> embeddings (queued) ---> pgvector
   |
   +------> question generation ------------------> pending queue
                                                         ^
question ---> retrieval ---> classification ---> answer   |
                                   |                      |
                                   +--- not in material ---+
```

Every answer cites the chunks it came from. When the model can't answer from the material, it says so, and the failure is recorded with the reason it fired.

---

## Engineering notes

Two decisions made against measurements rather than intuition. Both are reproducible with `php artisan lacuna:eval`.

### Similarity thresholds don't separate "knows" from "doesn't know"

The obvious way to detect a gap is a distance threshold. If nothing is close enough, the system doesn't know it. That fails.

Measured against the demo corpus:

| Question | Answerable? | Cosine similarity |
|---|:---:|---:|
| *"What warranty do we give on fitted parts?"* | ✗ nowhere in the corpus | **0.516** |
| *"Where do I get data to work on my machine?"* | ✓ fully documented | **0.441** |

There is no cut-off that separates the two populations. Embedding distance measures *topical proximity*, not whether an answer is present, a question can be squarely on-topic and completely unanswerable.

The threshold survives in the code, demoted. It now exists only to skip an API call when nothing remotely related comes back. The real decision moved to the model.

### Structured classification beats prompt instructions

Getting a model to admit it can't answer is harder than it looks, and the failure modes sit on both sides.

| Prompt version | Failure mode |
|---|---|
| Permissive | Answered questions the material didn't cover |
| Restrictive | Refused questions the material answered *by exclusion*, "can I deploy on Friday?" when the material defines a Tuesday - Thursday window |
| Restrictive + explicit rule | Ignored the rule; returned `supported: true` alongside an answer reading *"the material does not specify"* |

Prose instructions kept trading one error for the other. What worked was removing the judgement call entirely: the model now returns `answer_type` as one of `direct`, `negative_rule`, or `not_in_material`, and the application derives support from that classification. The model's own `supported` flag is discarded.

> A closed-set classification is a more reliable thing to ask a model for than adherence to a negative instruction about what *not* to write.

### Retrieval quality: baseline vs. real embeddings

The project ships a deterministic hash-based embedding provider so the pipeline runs without an API key. It also served as a control. Same corpus, same 20 questions, same code:

| Provider | Correct source ranked #1 | Correct source in top 3 |
|---|:---:|:---:|
| Hash-based (`fake`) | 20% | 55% |
| `text-embedding-3-small` | **80%** | **90%** |

The gap is the value of semantic embeddings, isolated. Questions were deliberately written in the vocabulary people actually use, not the vocabulary of the documents.

---

## Stack

`Laravel 13` · `PHP 8.5` · `PostgreSQL 18 + pgvector` · `Blade` · `Pest`

Embeddings and generation sit behind interfaces (`EmbeddingProvider`, `LlmProvider`), resolved through the service container and selected by environment variable. Swapping providers is a config change, not a refactor.

## Getting started

```bash
git clone git@github.com:luismacnunes/lacuna.git
cd lacuna
composer install
npm install && npm run build
cp .env.example .env
php artisan key:generate
```

Create a PostgreSQL database with the `vector` extension available, point `.env` at it, then:

```bash
php artisan migrate
php artisan db:seed --class=DemoKnowledgeSeeder
php artisan queue:work --stop-when-empty
```

The demo corpus is fifteen documents of realistic internal documentation, with a matching set of evaluation questions.

<details>
<summary><b>Running without an API key</b></summary>

<br>

Set `EMBEDDING_DRIVER=fake` in `.env`. Ingestion, chunking, indexing and similarity search all work, the hash-based provider produces deterministic vectors from word overlap.

Answer generation and gap detection need a real key. Set `EMBEDDING_DRIVER=openai` and `OPENAI_API_KEY`, then re-index:

```bash
php artisan lacuna:reindex
php artisan queue:work --stop-when-empty
```

</details>

<details>
<summary><b>Evaluating retrieval quality</b></summary>

<br>

```bash
php artisan lacuna:eval
```

Runs 20 questions with known correct sources against the current index and reports recall@1 and recall@3. Use it to compare embedding providers, chunking strategies, or prompt changes, the numbers in the tables above came from this command.

Questions live in `tests/Fixtures/eval_questions.json`.

</details>

## Roadmap

| | Milestone |
|:---:|---|
| ✅ | Ingestion, chunking, queued embeddings |
| ✅ | Vector search with visible similarity scores |
| ✅ | Answer generation with citations and gap classification |
| ✅ | Topics, pending queue, ingestion interview |
| ⬜ | Semantic deduplication of queue items |
| ⬜ | Curation, human-written answers, indexed above raw chunks |
| ⬜ | Staleness, source hash changes flag derived answers for review |
| ⬜ | Coverage metrics over time |
| ⬜ | Per-area permissions |

## Licence

MIT, see [LICENSE](LICENSE).