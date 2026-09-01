# 002 - Ask the model to classify, not to judge

**Date:** August 2026
**Status:** Accepted
**Follows:** [001](001-similarity-threshold.md)

## The idea

With the threshold ruled out, the decision moved to the model. It gets the question and the retrieved chunks, and reports whether the material supports an answer.

Getting a model to admit it can't answer turned out to be harder than expected. The failure modes sit on opposite sides, and every instruction that fixed one broke the other.

## Three attempts

**Permissive.** Asked for a boolean `supported` field, told it not to use outside knowledge.

It answered questions the material didn't cover. Given chunks that were roughly on-topic, it produced something plausible instead of declining. The queue stayed empty, which made the system look like it knew everything.

**Restrictive.** Added: *material on the same subject that doesn't answer the specific question doesn't count as support.*

Now it refused questions the material answered by exclusion. Asked *"can I deploy on Friday morning?"* against material saying the window is Tuesday to Thursday and Fridays are excluded by agreement, it returned `supported: false`. The answer exists, it's just a no.

**Restrictive with a carve-out.** Added: *a negative answer is still an answer. Never write "the material does not specify" with supported set to true.*

It fixed Friday and broke something else. Asked how long the test environment stays down during a restore, against material saying only *"for some time"*, it returned `supported: true` with the answer *"the material does not specify the exact time."* It ignored the rule in exactly the case the rule described.

That's the worst of the three for this product. The user gets no answer and the system records no gap, so the failure is invisible.

## What I did instead

Stopped asking for a judgement and asked for a label:

```json
{
  "answer_type": "direct" | "negative_rule" | "not_in_material",
  "answer": "..."
}
```

- `direct` - the material says what was asked
- `negative_rule` - the material sets a rule, limit or closed list, and the question falls outside it
- `not_in_material` - the information isn't written down, even if the subject is covered

The app decides from the label: `not_in_material` is a gap, the other two are answers. The model's own `supported` flag is thrown away.

## Why this held up better

Picking one of three labels is a smaller ask than following a negative instruction about what not to write. *"Never write X with supported=true"* requires the model to inspect its own output and apply a rule to it. Choosing a label doesn't.

It also makes mistakes visible. A wrong label is a wrong value in a column you can query. A `supported` flag that contradicts the answer text sits there unnoticed until someone reads both.

That's from one project with one model, not a general finding. But it's the version that passed all four calibration cases when the prose versions couldn't pass more than three.

## Calibration set

Four cases covering both directions. All four get run after any prompt change:

| Question | Expected |
|---|---|
| Which days can we deploy? | answered, `direct` |
| Can I deploy on Friday morning? | answered, `negative_rule` |
| How long is the test environment down? | gap, `not_in_material` |
| What warranty do we give on fitted parts? | gap, `not_in_material` |

## Side effects

`answer_type` is stored on every answer, so it's possible to see how often the system answers negatively rather than directly, and to catch drift when the model version changes.

The prompt is also shorter than the third attempt. Three label definitions replaced four rules that were all circling the same distinction.