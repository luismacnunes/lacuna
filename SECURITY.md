# Security policy

## Reporting a vulnerability

Report security issues privately through [GitHub's security advisory form](https://github.com/luismacnunes/lacuna/security/advisories/new) rather than opening a public issue.

I'll acknowledge within a few days. This is a personal project, not a commercial product, so there's no formal response-time commitment beyond that.

## Scope

Lacuna is designed to run inside a trusted network with authenticated users. It is not hardened for public internet exposure and should not be deployed that way without additional work.

Issues in scope:

- Anything that lets an authenticated user read material they shouldn't have access to
- Prompt injection through uploaded documents that changes the system's behaviour for other users
- Leakage of the API key through logs, error pages, or generated output
- The usual web application classes: injection, XSS, CSRF, insecure direct object references

Out of scope:

- Missing per-area permissions. This is a known gap and it's on the roadmap. All authenticated users currently see all material.
- Attacks that require access to the server or database
- Denial of service through large uploads or high question volume

## For anyone running this

Two things worth knowing before you point it at real data.

**Your material goes to a third party.** With `EMBEDDING_DRIVER=openai`, document text is sent to OpenAI for embedding, and retrieved chunks are sent again when generating answers. If your internal documentation can't leave your infrastructure, run a local model instead, the provider interfaces exist for exactly this reason.

**The `.env` holds a live API key.** It's gitignored by default. Check that it still is before pushing, and set a spending limit on the key.
