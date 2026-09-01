## What this changes

<!-- One or two sentences. What behaviour is different after this? -->

## Why

<!-- What problem it solves. Link the issue if there is one. -->

## Evaluation

<!--
Required if this touches retrieval, chunking, or the answer prompt.
Run `php artisan lacuna:eval` before and after, and paste both.
Delete this section if the change can't affect retrieval quality.
-->

| | recall@1 | recall@3 |
|---|---:|---:|
| Before | | |
| After | | |

Gap questions still correctly classified as unanswerable: <!-- yes / no / which ones changed -->

## Known limitations

<!-- What this deliberately doesn't handle. Write it here rather than leaving it for someone to discover. -->

## Checklist

- [ ] `./vendor/bin/pest` passes
- [ ] New behaviour is covered by a test, or there's a reason it isn't
- [ ] No API keys, real client data, or internal documents in the diff
- [ ] README or `docs/decisions/` updated if this changes a documented decision
