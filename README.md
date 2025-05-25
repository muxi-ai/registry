# MUXI Registry

This document defines the product requirements for the MUXI Registry. It groups CLI commands into registry-related and server-related actions, and explains their roles within the architecture.

---

## 🧭 System Overview

MUXI consists of two decoupled systems that work together:

### 🔐 MUXI Registry

* Stores only **schema definitions** (agents, MCPs, formations).
* Provides versioned access to these definitions.
* Does **not** manage or execute agents or services.

### 🖥️ MUXI Server

* Manages and runs **formation runtimes**.
* Handles start/stop/lifecycle operations.
* Pulls fully-resolved formations for execution.

---

## 📦 Registry Commands (Schemas only)

### `muxi login <registry>`

Authenticate to a registry using a token from the web UI.

* Interactive prompt collects the token.
* Credentials saved to `~/.muxi/registries.json`

### `muxi push formation|agent|mcp <file>`

Upload a schema definition to a registry.

```bash
muxi push formation cool-formation.yaml --tag latest
muxi push agent summarizer.yaml --tag 1.0.0
```

* Requires login.
* Defaults to `muxihub.com`, override with `--registry`.

### `muxi pull <schema-ref>`

Download a schema from a registry or GitHub.

```bash
muxi pull muxihub.com/myorg/support-bot:1.0.0
muxi pull github.com/user/repo
```

### `muxi search <term>`

Search for schemas by name, tag, or type.

### `muxi list`

List schemas the user owns or has access to.

### `muxi delete <schema-ref>`

Remove a specific schema version from a registry.

### `muxi stats <schema-ref>`

View schema statistics (installs, downloads, etc.).

---


## 🗃 Registry Implementation Requirements

### Database

* Use **SQLite** + **sqlite-vec** for:

  * Users
  * Tokens
  * Schema metadata

### Blob Storage

* Use **S3 or compatible object storage** for schema files

  * Path: `org/schema/type/tag.yaml`

### Auth

* Token-based user auth
* Token creation and revocation via CLI/web UI

### API Endpoints

* `POST /auth/login`, `GET /auth/whoami`
* `POST /users`, `POST /tokens`, `DELETE /tokens/:id`
* `GET /schemas/:org/:name/:tag`
* `POST /schemas/:type`
* `DELETE /schemas/:org/:name/:tag`
* `GET /schemas/search`
