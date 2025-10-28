-- MUXI Registry Database Schema
-- SQLite 3
-- Last Updated: 2025-01-15

-- ============================================
-- USERS
-- ============================================

CREATE TABLE users (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  github_id INTEGER UNIQUE NOT NULL,
  github_username TEXT NOT NULL, -- Actual GitHub username (e.g., muxi-ai)
  registry_username TEXT UNIQUE NOT NULL, -- Display name on registry (e.g., muxi)
  github_avatar TEXT,
  github_email TEXT NOT NULL,
  github_type TEXT,
  github_installation_id INTEGER,        -- ← For app identity
  github_oauth_token TEXT,               -- ← ENCRYPTED! For repo operations
  first_name TEXT,
  last_name TEXT,
  company TEXT,
  bio TEXT,
  twitter_username TEXT,
  is_verified BOOLEAN DEFAULT 0, -- Official/verified account badge
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
  published_by_user_id INTEGER,            -- Who actually published it (optional audit)
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
  FOREIGN KEY (published_by_user_id) REFERENCES users(id) ON DELETE SET NULL
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

CREATE TABLE cli_tokens (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  user_id INTEGER NOT NULL,
  token_hash TEXT UNIQUE NOT NULL,         -- SHA256 hash of token
  name TEXT,                               -- "My Laptop", "CI/CD", etc.
  expires_at DATETIME,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  last_used_at DATETIME,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE INDEX idx_cli_tokens_user ON cli_tokens(user_id);
CREATE INDEX idx_cli_tokens_hash ON cli_tokens(token_hash);

-- ============================================
-- DOWNLOADS (Daily Tracking)
-- ============================================

CREATE TABLE downloads (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  formation_id INTEGER NOT NULL,
  version TEXT NOT NULL,
  day DATE NOT NULL,                       -- YYYY-MM-DD
  download_count INTEGER DEFAULT 0,
  FOREIGN KEY (formation_id) REFERENCES formations(id) ON DELETE CASCADE,
  UNIQUE(formation_id, version, day)
);

CREATE INDEX idx_downloads_formation ON downloads(formation_id, day DESC);
CREATE INDEX idx_downloads_day ON downloads(day DESC);

-- ============================================
-- SEARCH OPTIMIZATION (FTS5 + Spellfix1)
-- ============================================

-- Full-text search virtual table for better search performance
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

-- ============================================
-- SPELLFIX1 FOR FUZZY SEARCH
-- ============================================
-- Spellfix1 virtual table for handling typos and misspellings
-- NOTE: Requires SQLite to be compiled with SQLITE_ENABLE_FTS5 and spellfix1 extension
-- To enable: You may need to load the extension with: SELECT load_extension('spellfix1');

CREATE VIRTUAL TABLE IF NOT EXISTS formations_spellfix USING spellfix1;

-- Populate spellfix with formation names and important terms
-- This should be done after inserting formations
-- Example trigger to auto-populate:
CREATE TRIGGER formations_spellfix_insert AFTER INSERT ON formations BEGIN
  INSERT INTO formations_spellfix(word) VALUES (new.name);
END;

CREATE TRIGGER formations_spellfix_update AFTER UPDATE OF name ON formations BEGIN
  DELETE FROM formations_spellfix WHERE word = old.name;
  INSERT INTO formations_spellfix(word) VALUES (new.name);
END;

CREATE TRIGGER formations_spellfix_delete AFTER DELETE ON formations BEGIN
  DELETE FROM formations_spellfix WHERE word = old.name;
END;

-- ============================================
-- FUZZY SEARCH EXAMPLE QUERIES
-- ============================================

/*
EXAMPLE 1: Basic typo correction with fuzzy search
------------------------------------------------------
User searches for "custmer-suport" (with typos)

Step 1: Get spelling suggestions using Spellfix1
*/
-- SELECT word FROM formations_spellfix
-- WHERE word MATCH 'custmer-suport'
-- AND top=3
-- ORDER BY score;
-- Result: 'customer-support', 'customer-service', etc.

/*
Step 2: Use corrected spelling in FTS5 search
*/
-- SELECT f.*, rank
-- FROM formations_fts
-- JOIN formations f ON f.id = formations_fts.rowid
-- WHERE formations_fts MATCH 'customer-support'
-- ORDER BY rank;

/*
EXAMPLE 2: Combined fuzzy search with fallback
------------------------------------------------------
Automatically correct typos and search in one query
*/
-- WITH corrected_terms AS (
--   SELECT word, score
--   FROM formations_spellfix
--   WHERE word MATCH 'custmer' AND top=1
-- )
-- SELECT DISTINCT f.id, f.name, f.description, f.github_stars
-- FROM formations f
-- LEFT JOIN formations_fts fts ON fts.rowid = f.id
-- WHERE fts MATCH (SELECT word FROM corrected_terms)
--    OR f.name LIKE '%' || (SELECT word FROM corrected_terms) || '%'
-- ORDER BY f.github_stars DESC, f.total_downloads DESC
-- LIMIT 20;

/*
EXAMPLE 3: Multi-word fuzzy search
------------------------------------------------------
Search for "ai chatbut asistant" (multiple typos)
*/
-- WITH RECURSIVE split_terms(term, rest) AS (
--   SELECT '', 'ai chatbut asistant' || ' '
--   UNION ALL
--   SELECT
--     substr(rest, 1, instr(rest, ' ') - 1),
--     substr(rest, instr(rest, ' ') + 1)
--   FROM split_terms WHERE rest != ''
-- ),
-- corrected AS (
--   SELECT GROUP_CONCAT(
--     (SELECT word FROM formations_spellfix
--      WHERE word MATCH split_terms.term AND top=1),
--     ' '
--   ) as corrected_query
--   FROM split_terms WHERE term != ''
-- )
-- SELECT f.*, rank
-- FROM formations_fts fts
-- JOIN formations f ON f.id = fts.rowid
-- WHERE fts MATCH (SELECT corrected_query FROM corrected)
-- ORDER BY rank
-- LIMIT 20;

/*
PERFORMANCE TIPS:
-----------------
1. Regularly rebuild spellfix index for better suggestions:
   DELETE FROM formations_spellfix;
   INSERT INTO formations_spellfix(word) SELECT DISTINCT name FROM formations;

2. Add common search terms manually:
   INSERT INTO formations_spellfix(word) VALUES ('support'), ('customer'), ('automation');

3. For very large datasets, consider using editdist3 parameter:
   SELECT word FROM formations_spellfix WHERE word MATCH 'custmer' AND top=5 AND editdist3=200;
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
