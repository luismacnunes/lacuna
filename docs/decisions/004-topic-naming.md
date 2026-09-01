# 004 - Group gaps by generated topic name, not by question text

**Date:** August 2026
**Status:** Accepted, with a caveat

## The idea

When a question fails it creates a gap. Three people asking about the same thing in different words should produce one queue item with three questions, not three separate topics.

First version compared the text of the failed question against existing topics.

## What went wrong

Three questions about parts warranty, phrased differently:

```
"Que garantia damos nas peças aplicadas?"
"As peças têm garantia de quanto tempo?"
"Qual é o período de garantia numa reparação?"
```

The queue ended up with three separate topics, one per question, each titled with the full question text. Unreadable, and it would only get worse.

Measured afterwards, the first two questions sit at **0.759**, well short of the 0.85 merge threshold. Question phrasing carries enough weight in the embedding to keep two identically-subjected questions apart.

## What I did instead

When a question fails, the model produces a short topic name first, two to four words, the subject with the interrogative structure stripped, and matching happens between names.

## Why it works, which isn't what I expected

My assumption was that short normalised names would sit closer together than the questions they came from, giving the threshold more headroom. **That's wrong.** Measured:

| Pair | Similarity |
|---|---:|
| The two warranty questions | 0.759 |
| *"Garantias de peças"* vs *"Garantia de reparações"* | 0.730 |

The names score *lower* than the questions. Semantic headroom isn't the mechanism.

What actually happens is that the model collapses different phrasings of the same subject to the **same string**. Both of the first two questions produced *"Garantias de peças"* verbatim, so they merged at similarity 1.0. The grouping works by normalisation, not by proximity.

## The caveat

This only holds while the model names consistently, and it doesn't always. Tested on four phrasings of the same subject:

| Question | Generated name |
|---|---|
| Que garantia damos nas peças aplicadas? | Garantias de peças |
| As peças têm garantia de quanto tempo? | Garantias de peças |
| As peças novas têm garantia? | Garantias de peças |
| Qual é o período de garantia numa reparação? | **Período de garantia** |

Three out of four collapse and merge. The fourth splits off into its own topic.

The model isn't being unreasonable, that question asks *how long* where the others ask *what*, which is a real distinction. It's just not one the queue should care about.

So the mechanism is more brittle than the queue makes it look. It works on the cases where naming is stable and fails quietly on the ones where it isn't, which is the harder failure to spot.

Temperature is 0 for this call, which removes run-to-run variation but doesn't stop the model reading two differently-worded questions as different subjects.

Unresolved: whether to lower the merge threshold for topic names specifically, or to compare against every name in a topic rather than just the canonical one. Both need measuring against a proper set of name pairs rather than guessed at.

## Trade-offs

One extra generation call per failed question. It only happens when the system can't answer, so the cost follows gaps rather than traffic.

Grouping quality depends on naming quality, so the curation form lets whoever answers correct the topic name before saving. That's also the current mitigation for the caveat above: a human fixing a badly-named topic is what merges it in practice.

The topic name is what shows up in the queue, and short names are readable where full question texts weren't.

## What I ruled out

**Lowering the merge threshold on question text.** Low enough to merge the warranty questions at 0.759 is also low enough to merge unrelated questions that share vocabulary. The threshold isn't the problem, same class of error as [001](001-similarity-threshold.md).

## Note

The similarity figures in this record were measured after the fact, when writing it up. The original version of this document asserted numbers I'd assumed rather than measured, and the assumed numbers told the wrong story about why the change worked.