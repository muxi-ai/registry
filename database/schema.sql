-- MUXI Registry Database Schema
-- SQLite 3
-- Last Updated: 2025-01-15

-- ============================================
-- USERS
-- ============================================

CREATE TABLE users (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  github_id INTEGER UNIQUE NOT NULL,
  github_username TEXT NOT NULL,           -- Actual GitHub username (e.g., muxi-ai)
  registry_username TEXT UNIQUE NOT NULL,  -- Display name on registry (e.g., muxi)
  github_avatar TEXT,
  is_verified BOOLEAN DEFAULT 0,           -- Official/verified account badge
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  last_seen_at DATETIME
);

CREATE INDEX idx_users_github_id ON users(github_id);
CREATE INDEX idx_users_github_username ON users(github_username);
CREATE INDEX idx_users_registry_username ON users(registry_username);

-- ============================================
-- RESERVED USERNAMES
-- ============================================

CREATE TABLE reserved_usernames (
  registry_username TEXT PRIMARY KEY,
  github_username TEXT NOT NULL,           -- Which GitHub account owns this
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Pre-seed official MUXI mappings
INSERT INTO reserved_usernames (registry_username, github_username) VALUES 
  ('muxi', 'muxi-ai'),
  ('admin', 'muxi-ai'),
  ('support', 'muxi-ai'),
  ('official', 'muxi-ai');

-- ============================================
-- FORMATIONS
-- ============================================

CREATE TABLE formations (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  user_id INTEGER NOT NULL,
  name TEXT NOT NULL,                      -- Without 'muxi-' prefix (e.g., customer-support)
  description TEXT,
  readme_md TEXT,                          -- Cached README from GitHub
  latest_version TEXT,
  license TEXT,
  github_repo TEXT NOT NULL,               -- Full repo name (e.g., muxi-ai/muxi-customer-support)
  github_stars INTEGER DEFAULT 0,
  total_downloads INTEGER DEFAULT 0,
  size_bytes INTEGER DEFAULT 0,
  is_public BOOLEAN DEFAULT 1,             -- For future private formations
  published_at DATETIME,
  last_synced_at DATETIME,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE(user_id, name),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE INDEX idx_formations_user ON formations(user_id);
CREATE INDEX idx_formations_name ON formations(name);
CREATE INDEX idx_formations_downloads ON formations(total_downloads DESC);
CREATE INDEX idx_formations_stars ON formations(github_stars DESC);
CREATE INDEX idx_formations_published ON formations(published_at DESC);

-- ============================================
-- VERSIONS
-- ============================================

CREATE TABLE versions (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  formation_id INTEGER NOT NULL,
  version TEXT NOT NULL,                   -- Semver: 1.0.0 (without 'v' prefix)
  release_notes TEXT,                      -- From GitHub release body
  size_bytes INTEGER,
  sha256 TEXT,                             -- Bundle hash for integrity
  download_url TEXT,                       -- GitHub release asset URL
  download_count INTEGER DEFAULT 0,
  published_at DATETIME,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE(formation_id, version),
  FOREIGN KEY (formation_id) REFERENCES formations(id) ON DELETE CASCADE
);

CREATE INDEX idx_versions_formation ON versions(formation_id);
CREATE INDEX idx_versions_version ON versions(version);
CREATE INDEX idx_versions_downloads ON versions(download_count DESC);

-- ============================================
-- FORMATION STATS
-- ============================================

CREATE TABLE formation_stats (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  version_id INTEGER NOT NULL,
  agents_count INTEGER DEFAULT 0,
  mcps_count INTEGER DEFAULT 0,
  sops_count INTEGER DEFAULT 0,
  triggers_count INTEGER DEFAULT 0,
  knowledge_count INTEGER DEFAULT 0,
  stats_json TEXT,                         -- Full JSON blob with detailed stats
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (version_id) REFERENCES versions(id) ON DELETE CASCADE
);

CREATE INDEX idx_formation_stats_version ON formation_stats(version_id);

-- ============================================
-- TOKENS (CLI Authentication)
-- ============================================

CREATE TABLE tokens (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  user_id INTEGER NOT NULL,
  token_hash TEXT UNIQUE NOT NULL,         -- SHA256 hash of token
  name TEXT,                               -- "My Laptop", "CI/CD", etc.
  github_installation_id INTEGER,          -- GitHub App installation ID
  expires_at DATETIME,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  last_used_at DATETIME,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE INDEX idx_tokens_user ON tokens(user_id);
CREATE INDEX idx_tokens_hash ON tokens(token_hash);

-- ============================================
-- SEARCH OPTIMIZATION (Optional - for FTS)
-- ============================================

-- Full-text search virtual table (if needed for better search)
-- Uncomment if you want to use SQLite FTS5
/*
CREATE VIRTUAL TABLE formations_fts USING fts5(
  name,
  description,
  readme_md,
  content=formations,
  content_rowid=id
);

-- Triggers to keep FTS in sync
CREATE TRIGGER formations_fts_insert AFTER INSERT ON formations BEGIN
  INSERT INTO formations_fts(rowid, name, description, readme_md)
  VALUES (new.id, new.name, new.description, new.readme_md);
END;

CREATE TRIGGER formations_fts_update AFTER UPDATE ON formations BEGIN
  UPDATE formations_fts 
  SET name = new.name, 
      description = new.description, 
      readme_md = new.readme_md
  WHERE rowid = new.id;
END;

CREATE TRIGGER formations_fts_delete AFTER DELETE ON formations BEGIN
  DELETE FROM formations_fts WHERE rowid = old.id;
END;
*/

-- ============================================
-- SAMPLE DATA (for testing)
-- ============================================

-- Uncomment for local testing
/*
-- Sample user (muxi-ai → @muxi)
INSERT INTO users (github_id, github_username, registry_username, github_avatar, is_verified)
VALUES (12345678, 'muxi-ai', 'muxi', 'https://avatars.githubusercontent.com/u/12345678', 1);

-- Sample formation
INSERT INTO formations (
  user_id, name, description, latest_version, 
  github_repo, github_stars, total_downloads,
  published_at, last_synced_at
)
VALUES (
  1, 
  'customer-support',
  'AI-powered customer support with intelligent escalation',
  '1.0.0',
  'muxi-ai/muxi-customer-support',
  45,
  1234,
  datetime('now'),
  datetime('now')
);

-- Sample version
INSERT INTO versions (formation_id, version, download_url, size_bytes, published_at)
VALUES (
  1,
  '1.0.0',
  'https://github.com/muxi-ai/muxi-customer-support/releases/download/v1.0.0/bundle.zip',
  29593,
  datetime('now')
);
*/
