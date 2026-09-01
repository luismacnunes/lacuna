# 001 - A similarity threshold can't detect gaps

**Date:** August 2026
**Status:** Accepted

## The idea

Lacuna needs to know when it can't answer something, because that's what fills the queue. The obvious way to do it is a distance threshold: if nothing close enough comes back from retrieval, the answer isn't there.

Before building on that, I measured it against the demo corpus, 15 documents, around 30 chunks, `text-embedding-3-small`.

## What came back

Questions with no answer anywhere in the corpus, scored against the nearest chunk:

| Question | Top similarity |
|---|---:|
| What warranty do we give on fitted parts? | 0.516 |
| What's the remote work policy? | 0.448 |
| How do I request time off? | 0.342 |

And questions the corpus answers in full:

| Question | Top similarity |
|---|---:|
| Where do I get data to work on my machine? | 0.441 |
| In which timezone do we store dates? | 0.485 |
| Which days of the week can we deploy? | 0.639 |

The ranges overlap. The warranty question scores higher than eight of the twenty answerable ones in the eval set. Draw the line at 0.50 and you lose those eight while the warranty question still gets through.

## Why it happens

Cosine distance tells you how close two things are in subject matter, not whether one answers the other. The warranty question is full of vocabulary that shows up all over the corpus, parts, warranty and fitted, so it lands near several documents without any of them answering it.

Being on-topic and containing the answer are different things, and the embedding only knows the first one. That's why no amount of tuning fixes it: the number being thresholded isn't measuring what the threshold is trying to decide.

## What I did instead

The threshold stays at 0.30, demoted to a cost guard. It skips the generation call when nothing remotely related comes back, and plays no part in deciding whether the system knows something.

That decision moved to the model, which gets the retrieved chunks and classifies whether they support an answer. See [002](002-structured-classification.md).

## Trade-off

Every question above 0.30 costs one generation call, including the ones that turn out to be gaps. Fine at this size. If volume ever makes it expensive, a cheaper first-pass classifier could sit between retrieval and generation.

## What I ruled out

**A threshold per topic.** The overlap isn't a calibration problem, so per-topic numbers would move the failures around rather than remove them.

**Requiring several chunks above the threshold.** Same issue. An on-topic question with no answer retrieves several close chunks, not one.