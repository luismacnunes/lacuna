# Security policy

## Reporting something

Use [GitHub's private advisory form](https://github.com/luismacnunes/lacuna/security/advisories/new) rather than opening a public issue.

I'll get back to you within a few days. This is a personal project rather than a commercial product, so that's the only commitment I can make.

## Scope

Lacuna is built to run inside a trusted network with authenticated users. It isn't hardened for the public internet and shouldn't be deployed that way without more work than currently exists.

In scope:

- Anything letting a logged-in user read material they shouldn't
- Prompt injection through an uploaded document that changes behaviour for other users
- API keys leaking through logs, error pages or generated output
- The usual web classes: injection, XSS, CSRF, insecure direct object references

Out of scope:

- Missing per-area permissions. Known gap, on the roadmap. Right now everyone who can log in sees everything.
- Anything needing access to the server or the database
- Denial of service through large uploads or question volume

## Before you point it at real data

**Your material leaves your network.** With `EMBEDDING_DRIVER=openai`, document text goes to OpenAI to be embedded, and retrieved chunks go again when an answer is generated. If your documentation can't leave your infrastructure, use a local model, the provider interfaces exist for exactly this.

**The `.env` holds a live API key.** It's gitignored by default, but check before pushing, and set a spending limit on the key.

**Nobody's running this in production**, including me. It's tested, but it hasn't been exposed to real users or real load.