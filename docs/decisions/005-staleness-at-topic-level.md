# 005 - Flag stale answers by topic

**Date:** August 2026
**Status:** Accepted

## The problem

A curated answer is written by a person, so it has no recorded sources. When a source document changes, nothing links it to the answers that depended on it.

Three ways to work out which answers to flag:

1. Semantic similarity between the changed document and existing answers
2. Topic membership - flag everything in the changed document's topic
3. Ask whoever writes the answer to name the documents it rests on

## The choice

Topic membership.

## Why not similarity

Similarity measures subject overlap, not dependency. An answer about warranties and a document about the parts catalogue land close together because both talk about parts, without one depending on the other. That produces false flags in volume.

False positives cost more here than in gap detection. A false gap is one item somebody dismisses. A false review flag puts "possibly out of date" on an answer that's correct, and if that happens often enough people would stop reading the warnings, at which point the mechanism has stopped existing.

Third time in this project that cosine similarity looked like the right tool and wasn't. See [001](001-similarity-threshold.md) and [004](004-topic-naming.md).

## Why not declared sources

More rigorous, and probably where this ends up eventually. Rejected for now because it adds a field to the curation form that's easy to skip, and a field that's often left empty gives a false sense of precision: flagging would work well for the answers where it was filled in and silently not at all for the rest.

## Why topic level is good enough

It's deterministic and it explains itself. The review screen says which document changed and when, so whoever picks it up knows what to check. A warning that justifies itself is one people act on.

It's noisier than a real dependency graph, but the noise is bounded. A topic holds a few answers and a few documents, so a change flags a handful of items rather than the whole store.

## How it works

An observer on `Document::updated` checks `wasChanged('content_hash')` and flags unflagged curated answers in the same topic, recording which document triggered it. Editing a title or a description changes nothing.

Flags clear three ways from the review screen: **confirm** (records who and when, no re-index), **correct** (edits the answer and re-queues the embedding), or **remove**.

Confirm has to be one click from the list. If clearing a flag meant opening each item, the list wouldn't get cleared, and a review queue nobody clears is the same as not having one.

## Known cost

Answers get flagged that didn't depend on the changed document. Acceptable while topics stay small. If it becomes annoying, that's the signal to add declared sources with topic membership as the fallback.

Time-based decay - an answer confirmed today asking for review in six months regardless of what changed, is deliberately out of scope. It doubles the surface area, and there's no evidence yet that the first layer produces too little review to be useful.