# AGENTS.md

MUXI is an open-source AI application server for deploying and operating agent formations in production.

**Treat it as infrastructure, not a framework:**

Preserve the distinction between Server, Runtime, SDKs, and Formation Schema, and prefer existing architecture, protocols, and conventions over introducing parallel abstractions. When working in this project, prioritize production safety, multi-tenant isolation, observability, token efficiency, and provider-agnostic behavior; do not weaken sandboxing, credential boundaries, or agent/user isolation, and do not add framework-style shortcuts that bypass the formation/deployment model.

Prefer changes that keep APIs, SDKs, docs, and formation behavior aligned, and verify behavior with the project’s existing test and validation commands before concluding work.

## MUXI-specific guidance

This project includes repo-specific MUXI guidance under `skills/muxi` (`https://github.com/muxi-ai/muxi`).

Agents working in this codebase should consult `skills/muxi` before changing behavior related to the CLI, formations, deployment, server/runtime internals, SDKs, MCP, memory, orchestration, or observability. Follow that skill for MUXI-specific workflows and conventions.

`AGENTS.md` defines the global architectural rules, constraints, and safety expectations. `skills/muxi` contains the deeper product-specific operating guidance.
