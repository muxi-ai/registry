-- MUXI Registry - Test Data
-- Run: sqlite3 html/registry.db < test-data.sql

BEGIN TRANSACTION;

-- ============================================
-- USERS (5 users + 1 org)
-- ============================================

-- Clean up any existing test data
DELETE FROM formation_stats WHERE version_id IN (SELECT id FROM versions WHERE formation_id IN (SELECT id FROM formations WHERE user_id IN (SELECT id FROM users WHERE github_id >= 99999900)));
DELETE FROM versions WHERE formation_id IN (SELECT id FROM formations WHERE user_id IN (SELECT id FROM users WHERE github_id >= 99999900));
DELETE FROM formations WHERE user_id IN (SELECT id FROM users WHERE github_id >= 99999900);
DELETE FROM users WHERE github_id >= 99999900;

-- @muxi (official org via muxi-ai GitHub)
INSERT INTO users (github_id, github_username, registry_username, github_avatar, github_email, github_type, is_verified, created_at)
VALUES (99999901, 'muxi-ai', 'muxi', 'https://avatars.githubusercontent.com/u/99999901', NULL, 'Organization', 1, datetime('now', '-180 days'));

-- @alice (fictional developer)
INSERT INTO users (github_id, github_username, registry_username, github_avatar, github_email, github_type, is_verified, created_at)
VALUES (99999902, 'alice-dev', 'alice', 'https://avatars.githubusercontent.com/u/99999902', 'alice@example.com', 'User', 0, datetime('now', '-120 days'));

-- @bob (fictional developer)
INSERT INTO users (github_id, github_username, registry_username, github_avatar, github_email, github_type, is_verified, created_at)
VALUES (99999903, 'bob-codes', 'bob', 'https://avatars.githubusercontent.com/u/99999903', 'bob@example.com', 'User', 0, datetime('now', '-90 days'));

-- @acmecorp (fictional company)
INSERT INTO users (github_id, github_username, registry_username, github_avatar, github_email, github_type, is_verified, created_at)
VALUES (99999904, 'acmecorp', 'acmecorp', 'https://avatars.githubusercontent.com/u/99999904', NULL, 'Organization', 0, datetime('now', '-60 days'));

-- @sarah (fictional developer)
INSERT INTO users (github_id, github_username, registry_username, github_avatar, github_email, github_type, is_verified, created_at)
VALUES (99999905, 'sarah-ml', 'sarah', 'https://avatars.githubusercontent.com/u/99999905', 'sarah@example.com', 'User', 0, datetime('now', '-45 days'));

-- ============================================
-- FORMATIONS (12 formations)
-- ============================================

-- @muxi formations (official, popular)
INSERT INTO formations (user_id, published_by_user_id, name, description, readme_md, latest_version, github_repo, github_stars, total_downloads, size_bytes, published_at, last_synced_at, created_at)
VALUES (
  (SELECT id FROM users WHERE registry_username = 'muxi'),
  NULL,
  'customer-support',
  'AI-powered customer support with intelligent escalation and sentiment analysis',
  '# Customer Support Formation\n\nComplete AI customer support system with ticket routing, sentiment analysis, and escalation management.',
  '1.2.3',
  'muxi-ai/muxi-customer-support',
  145,
  2847,
  2456789,
  datetime('now', '-150 days'),
  datetime('now', '-1 hour'),
  datetime('now', '-150 days')
);

INSERT INTO formations (user_id, published_by_user_id, name, description, readme_md, latest_version, github_repo, github_stars, total_downloads, size_bytes, published_at, last_synced_at, created_at)
VALUES (
  (SELECT id FROM users WHERE registry_username = 'muxi'),
  NULL,
  'sentiment-analyzer',
  'Real-time sentiment analysis for customer feedback and social media',
  '# Sentiment Analyzer\n\nAnalyze customer sentiment across multiple channels.',
  '2.1.0',
  'muxi-ai/muxi-sentiment-analyzer',
  89,
  1523,
  1234567,
  datetime('now', '-90 days'),
  datetime('now', '-2 hours'),
  datetime('now', '-90 days')
);

INSERT INTO formations (user_id, published_by_user_id, name, description, readme_md, latest_version, github_repo, github_stars, total_downloads, size_bytes, published_at, last_synced_at, created_at)
VALUES (
  (SELECT id FROM users WHERE registry_username = 'muxi'),
  NULL,
  'data-processor',
  'ETL pipeline automation with AI-driven data quality checks',
  '# Data Processor\n\nAutomate your data pipelines with intelligent quality checks.',
  '1.0.0',
  'muxi-ai/muxi-data-processor',
  34,
  456,
  3456789,
  datetime('now', '-15 days'),
  datetime('now', '-30 minutes'),
  datetime('now', '-15 days')
);

-- @ranaroussi formations
INSERT INTO formations (user_id, published_by_user_id, name, description, readme_md, latest_version, github_repo, github_stars, total_downloads, size_bytes, published_at, last_synced_at, created_at)
VALUES (
  (SELECT id FROM users WHERE registry_username = 'ranaroussi'),
  (SELECT id FROM users WHERE registry_username = 'ranaroussi'),
  'code-reviewer',
  'Automated code review agent with best practices checking',
  '# Code Reviewer\n\nAI-powered code reviews following your team standards.',
  '0.9.0',
  'ranaroussi/muxi-code-reviewer',
  67,
  892,
  1789234,
  datetime('now', '-45 days'),
  datetime('now', '-3 hours'),
  datetime('now', '-45 days')
);

INSERT INTO formations (user_id, published_by_user_id, name, description, readme_md, latest_version, github_repo, github_stars, total_downloads, size_bytes, published_at, last_synced_at, created_at)
VALUES (
  (SELECT id FROM users WHERE registry_username = 'ranaroussi'),
  (SELECT id FROM users WHERE registry_username = 'ranaroussi'),
  'meeting-scheduler',
  'Smart meeting scheduler that finds optimal times and sends invites',
  '# Meeting Scheduler\n\nNever play calendar tetris again.',
  '1.1.2',
  'ranaroussi/muxi-meeting-scheduler',
  23,
  341,
  987654,
  datetime('now', '-30 days'),
  datetime('now', '-1 hour'),
  datetime('now', '-30 days')
);

-- @alice formations
INSERT INTO formations (user_id, published_by_user_id, name, description, readme_md, latest_version, github_repo, github_stars, total_downloads, size_bytes, published_at, last_synced_at, created_at)
VALUES (
  (SELECT id FROM users WHERE registry_username = 'alice'),
  (SELECT id FROM users WHERE registry_username = 'alice'),
  'slack-bot',
  'Intelligent Slack bot for team productivity and knowledge management',
  '# Slack Bot\n\nYour team productivity assistant.',
  '2.0.1',
  'alice-dev/muxi-slack-bot',
  112,
  1654,
  1567890,
  datetime('now', '-75 days'),
  datetime('now', '-2 hours'),
  datetime('now', '-75 days')
);

INSERT INTO formations (user_id, published_by_user_id, name, description, readme_md, latest_version, github_repo, github_stars, total_downloads, size_bytes, published_at, last_synced_at, created_at)
VALUES (
  (SELECT id FROM users WHERE registry_username = 'alice'),
  (SELECT id FROM users WHERE registry_username = 'alice'),
  'github-webhook',
  'GitHub webhook processor for CI/CD automation and notifications',
  '# GitHub Webhook Handler\n\nAutomate your GitHub workflows.',
  '1.3.0',
  'alice-dev/muxi-github-webhook',
  45,
  678,
  876543,
  datetime('now', '-50 days'),
  datetime('now', '-4 hours'),
  datetime('now', '-50 days')
);

-- @bob formations
INSERT INTO formations (user_id, published_by_user_id, name, description, readme_md, latest_version, github_repo, github_stars, total_downloads, size_bytes, published_at, last_synced_at, created_at)
VALUES (
  (SELECT id FROM users WHERE registry_username = 'bob'),
  (SELECT id FROM users WHERE registry_username = 'bob'),
  'email-classifier',
  'ML-powered email classification and routing system',
  '# Email Classifier\n\nIntelligent email organization.',
  '1.0.5',
  'bob-codes/muxi-email-classifier',
  28,
  423,
  1234321,
  datetime('now', '-60 days'),
  datetime('now', '-5 hours'),
  datetime('now', '-60 days')
);

INSERT INTO formations (user_id, published_by_user_id, name, description, readme_md, latest_version, github_repo, github_stars, total_downloads, size_bytes, published_at, last_synced_at, created_at)
VALUES (
  (SELECT id FROM users WHERE registry_username = 'bob'),
  (SELECT id FROM users WHERE registry_username = 'bob'),
  'report-generator',
  'Automated report generation from multiple data sources',
  '# Report Generator\n\nBeautiful reports, automatically.',
  '0.8.0',
  'bob-codes/muxi-report-generator',
  19,
  234,
  2345678,
  datetime('now', '-25 days'),
  datetime('now', '-1 hour'),
  datetime('now', '-25 days')
);

-- @acmecorp formations
INSERT INTO formations (user_id, published_by_user_id, name, description, readme_md, latest_version, github_repo, github_stars, total_downloads, size_bytes, published_at, last_synced_at, created_at)
VALUES (
  (SELECT id FROM users WHERE registry_username = 'acmecorp'),
  NULL,
  'sales-assistant',
  'AI sales assistant for lead qualification and follow-ups',
  '# Sales Assistant\n\nYour AI SDR that never sleeps.',
  '1.4.2',
  'acmecorp/muxi-sales-assistant',
  56,
  987,
  1876543,
  datetime('now', '-40 days'),
  datetime('now', '-3 hours'),
  datetime('now', '-40 days')
);

INSERT INTO formations (user_id, published_by_user_id, name, description, readme_md, latest_version, github_repo, github_stars, total_downloads, size_bytes, published_at, last_synced_at, created_at)
VALUES (
  (SELECT id FROM users WHERE registry_username = 'acmecorp'),
  NULL,
  'hr-onboarding',
  'Automated employee onboarding with document management',
  '# HR Onboarding\n\nStreamline your hiring process.',
  '2.3.0',
  'acmecorp/muxi-hr-onboarding',
  41,
  567,
  1456789,
  datetime('now', '-20 days'),
  datetime('now', '-2 hours'),
  datetime('now', '-20 days')
);

-- @sarah formation (recent)
INSERT INTO formations (user_id, published_by_user_id, name, description, readme_md, latest_version, github_repo, github_stars, total_downloads, size_bytes, published_at, last_synced_at, created_at)
VALUES (
  (SELECT id FROM users WHERE registry_username = 'sarah'),
  (SELECT id FROM users WHERE registry_username = 'sarah'),
  'document-qa',
  'Question answering system for large document collections',
  '# Document Q&A\n\nAsk questions about your documents.',
  '1.0.0',
  'sarah-ml/muxi-document-qa',
  12,
  89,
  2123456,
  datetime('now', '-5 days'),
  datetime('now', '-10 minutes'),
  datetime('now', '-5 days')
);

-- ============================================
-- VERSIONS (at least one version per formation)
-- ============================================

-- customer-support versions
INSERT INTO versions (formation_id, version, release_notes, size_bytes, download_url, download_count, published_at)
VALUES (
  (SELECT id FROM formations WHERE name = 'customer-support' AND user_id = (SELECT id FROM users WHERE registry_username = 'muxi')),
  '1.2.3',
  'Added multi-language support and improved escalation rules',
  2456789,
  'https://github.com/muxi-ai/muxi-customer-support/releases/download/v1.2.3/bundle.zip',
  2847,
  datetime('now', '-2 days')
);

-- sentiment-analyzer versions
INSERT INTO versions (formation_id, version, release_notes, size_bytes, download_url, download_count, published_at)
VALUES (
  (SELECT id FROM formations WHERE name = 'sentiment-analyzer' AND user_id = (SELECT id FROM users WHERE registry_username = 'muxi')),
  '2.1.0',
  'Performance improvements and new emotion detection',
  1234567,
  'https://github.com/muxi-ai/muxi-sentiment-analyzer/releases/download/v2.1.0/bundle.zip',
  1523,
  datetime('now', '-10 days')
);

-- data-processor (recent!)
INSERT INTO versions (formation_id, version, release_notes, size_bytes, download_url, download_count, published_at)
VALUES (
  (SELECT id FROM formations WHERE name = 'data-processor' AND user_id = (SELECT id FROM users WHERE registry_username = 'muxi')),
  '1.0.0',
  'Initial release with ETL pipeline automation',
  3456789,
  'https://github.com/muxi-ai/muxi-data-processor/releases/download/v1.0.0/bundle.zip',
  456,
  datetime('now', '-15 days')
);

-- code-reviewer
INSERT INTO versions (formation_id, version, release_notes, size_bytes, download_url, download_count, published_at)
VALUES (
  (SELECT id FROM formations WHERE name = 'code-reviewer' AND user_id = (SELECT id FROM users WHERE registry_username = 'ranaroussi')),
  '0.9.0',
  'Beta release with support for Python, JavaScript, and Go',
  1789234,
  'https://github.com/ranaroussi/muxi-code-reviewer/releases/download/v0.9.0/bundle.zip',
  892,
  datetime('now', '-45 days')
);

-- meeting-scheduler
INSERT INTO versions (formation_id, version, release_notes, size_bytes, download_url, download_count, published_at)
VALUES (
  (SELECT id FROM formations WHERE name = 'meeting-scheduler' AND user_id = (SELECT id FROM users WHERE registry_username = 'ranaroussi')),
  '1.1.2',
  'Added Google Calendar and Outlook integration',
  987654,
  'https://github.com/ranaroussi/muxi-meeting-scheduler/releases/download/v1.1.2/bundle.zip',
  341,
  datetime('now', '-30 days')
);

-- slack-bot
INSERT INTO versions (formation_id, version, release_notes, size_bytes, download_url, download_count, published_at)
VALUES (
  (SELECT id FROM formations WHERE name = 'slack-bot' AND user_id = (SELECT id FROM users WHERE registry_username = 'alice')),
  '2.0.1',
  'Major update with slash commands and interactive messages',
  1567890,
  'https://github.com/alice-dev/muxi-slack-bot/releases/download/v2.0.1/bundle.zip',
  1654,
  datetime('now', '-75 days')
);

-- github-webhook
INSERT INTO versions (formation_id, version, release_notes, size_bytes, download_url, download_count, published_at)
VALUES (
  (SELECT id FROM formations WHERE name = 'github-webhook' AND user_id = (SELECT id FROM users WHERE registry_username = 'alice')),
  '1.3.0',
  'Added support for deployment events and PR comments',
  876543,
  'https://github.com/alice-dev/muxi-github-webhook/releases/download/v1.3.0/bundle.zip',
  678,
  datetime('now', '-50 days')
);

-- email-classifier
INSERT INTO versions (formation_id, version, release_notes, size_bytes, download_url, download_count, published_at)
VALUES (
  (SELECT id FROM formations WHERE name = 'email-classifier' AND user_id = (SELECT id FROM users WHERE registry_username = 'bob')),
  '1.0.5',
  'Improved accuracy and added custom rule support',
  1234321,
  'https://github.com/bob-codes/muxi-email-classifier/releases/download/v1.0.5/bundle.zip',
  423,
  datetime('now', '-60 days')
);

-- report-generator
INSERT INTO versions (formation_id, version, release_notes, size_bytes, download_url, download_count, published_at)
VALUES (
  (SELECT id FROM formations WHERE name = 'report-generator' AND user_id = (SELECT id FROM users WHERE registry_username = 'bob')),
  '0.8.0',
  'Beta release with PDF and Excel export',
  2345678,
  'https://github.com/bob-codes/muxi-report-generator/releases/download/v0.8.0/bundle.zip',
  234,
  datetime('now', '-25 days')
);

-- sales-assistant
INSERT INTO versions (formation_id, version, release_notes, size_bytes, download_url, download_count, published_at)
VALUES (
  (SELECT id FROM formations WHERE name = 'sales-assistant' AND user_id = (SELECT id FROM users WHERE registry_username = 'acmecorp')),
  '1.4.2',
  'Added CRM integrations and email templates',
  1876543,
  'https://github.com/acmecorp/muxi-sales-assistant/releases/download/v1.4.2/bundle.zip',
  987,
  datetime('now', '-40 days')
);

-- hr-onboarding
INSERT INTO versions (formation_id, version, release_notes, size_bytes, download_url, download_count, published_at)
VALUES (
  (SELECT id FROM formations WHERE name = 'hr-onboarding' AND user_id = (SELECT id FROM users WHERE registry_username = 'acmecorp')),
  '2.3.0',
  'Enhanced document management and e-signature support',
  1456789,
  'https://github.com/acmecorp/muxi-hr-onboarding/releases/download/v2.3.0/bundle.zip',
  567,
  datetime('now', '-20 days')
);

-- document-qa (very recent!)
INSERT INTO versions (formation_id, version, release_notes, size_bytes, download_url, download_count, published_at)
VALUES (
  (SELECT id FROM formations WHERE name = 'document-qa' AND user_id = (SELECT id FROM users WHERE registry_username = 'sarah')),
  '1.0.0',
  'Initial release with RAG-based document Q&A',
  2123456,
  'https://github.com/sarah-ml/muxi-document-qa/releases/download/v1.0.0/bundle.zip',
  89,
  datetime('now', '-5 days')
);

-- ============================================
-- FORMATION STATS (sample for popular ones)
-- ============================================

INSERT INTO formation_stats (version_id, agents_count, mcps_count, sops_count, triggers_count, knowledge_count, stats_json)
VALUES (
  (SELECT id FROM versions WHERE version = '1.2.3' AND formation_id = (SELECT id FROM formations WHERE name = 'customer-support')),
  3, 2, 1, 2, 1,
  '{"agents": ["escalation", "sentiment", "router"], "mcps": ["zendesk", "slack"]}'
);

INSERT INTO formation_stats (version_id, agents_count, mcps_count, sops_count, triggers_count, knowledge_count, stats_json)
VALUES (
  (SELECT id FROM versions WHERE version = '2.0.1' AND formation_id = (SELECT id FROM formations WHERE name = 'slack-bot')),
  2, 1, 2, 3, 0,
  '{"agents": ["bot-core", "knowledge"], "mcps": ["slack"]}'
);

-- ============================================
-- DOWNLOADS (Daily tracking for last 60 days)
-- ============================================
-- Generate realistic download patterns for trending calculations

-- Helper: Generate downloads for a formation over date range
-- Pattern: customer-support (TRENDING - recent spike)
INSERT INTO downloads (formation_id, version, day, download_count)
SELECT 
  (SELECT id FROM formations WHERE name = 'customer-support'),
  '1.2.3',
  DATE('now', '-' || value || ' days'),
  CASE 
    WHEN value <= 7 THEN 45 + (RANDOM() % 15)  -- Last week: 45-60 downloads/day
    WHEN value <= 14 THEN 30 + (RANDOM() % 10) -- Week before: 30-40
    WHEN value <= 30 THEN 20 + (RANDOM() % 8)  -- Earlier: 20-28
    ELSE 10 + (RANDOM() % 5)                   -- Old: 10-15
  END
FROM (
  SELECT 0 AS value UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 UNION
  SELECT 10 UNION SELECT 11 UNION SELECT 12 UNION SELECT 13 UNION SELECT 14 UNION SELECT 15 UNION SELECT 16 UNION SELECT 17 UNION SELECT 18 UNION SELECT 19 UNION
  SELECT 20 UNION SELECT 21 UNION SELECT 22 UNION SELECT 23 UNION SELECT 24 UNION SELECT 25 UNION SELECT 26 UNION SELECT 27 UNION SELECT 28 UNION SELECT 29 UNION
  SELECT 30 UNION SELECT 31 UNION SELECT 32 UNION SELECT 33 UNION SELECT 34 UNION SELECT 35 UNION SELECT 36 UNION SELECT 37 UNION SELECT 38 UNION SELECT 39 UNION
  SELECT 40 UNION SELECT 41 UNION SELECT 42 UNION SELECT 43 UNION SELECT 44 UNION SELECT 45 UNION SELECT 46 UNION SELECT 47 UNION SELECT 48 UNION SELECT 49 UNION
  SELECT 50 UNION SELECT 51 UNION SELECT 52 UNION SELECT 53 UNION SELECT 54 UNION SELECT 55 UNION SELECT 56 UNION SELECT 57 UNION SELECT 58 UNION SELECT 59
);

-- sentiment-analyzer (STEADY - consistent popularity)
INSERT INTO downloads (formation_id, version, day, download_count)
SELECT 
  (SELECT id FROM formations WHERE name = 'sentiment-analyzer'),
  '1.0.0',
  DATE('now', '-' || value || ' days'),
  25 + (RANDOM() % 8)  -- Steady 25-33 downloads/day
FROM (
  SELECT 0 AS value UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 UNION
  SELECT 10 UNION SELECT 11 UNION SELECT 12 UNION SELECT 13 UNION SELECT 14 UNION SELECT 15 UNION SELECT 16 UNION SELECT 17 UNION SELECT 18 UNION SELECT 19 UNION
  SELECT 20 UNION SELECT 21 UNION SELECT 22 UNION SELECT 23 UNION SELECT 24 UNION SELECT 25 UNION SELECT 26 UNION SELECT 27 UNION SELECT 28 UNION SELECT 29 UNION
  SELECT 30 UNION SELECT 31 UNION SELECT 32 UNION SELECT 33 UNION SELECT 34 UNION SELECT 35 UNION SELECT 36 UNION SELECT 37 UNION SELECT 38 UNION SELECT 39 UNION
  SELECT 40 UNION SELECT 41 UNION SELECT 42 UNION SELECT 43 UNION SELECT 44 UNION SELECT 45 UNION SELECT 46 UNION SELECT 47 UNION SELECT 48 UNION SELECT 49 UNION
  SELECT 50 UNION SELECT 51 UNION SELECT 52 UNION SELECT 53 UNION SELECT 54 UNION SELECT 55 UNION SELECT 56 UNION SELECT 57 UNION SELECT 58 UNION SELECT 59
);

-- data-processor (NEW/RISING - just launched, growing fast)
INSERT INTO downloads (formation_id, version, day, download_count)
SELECT 
  (SELECT id FROM formations WHERE name = 'data-processor'),
  '1.0.0',
  DATE('now', '-' || value || ' days'),
  CASE 
    WHEN value <= 7 THEN 35 + (RANDOM() % 10)  -- Last week: 35-45
    WHEN value <= 14 THEN 18 + (RANDOM() % 8)  -- Week before: 18-26
    WHEN value <= 21 THEN 8 + (RANDOM() % 5)   -- Week 3: 8-13
    ELSE 0                                      -- Didn't exist before
  END
FROM (
  SELECT 0 AS value UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 UNION
  SELECT 10 UNION SELECT 11 UNION SELECT 12 UNION SELECT 13 UNION SELECT 14 UNION SELECT 15 UNION SELECT 16 UNION SELECT 17 UNION SELECT 18 UNION SELECT 19 UNION
  SELECT 20 UNION SELECT 21 UNION SELECT 22 UNION SELECT 23 UNION SELECT 24 UNION SELECT 25 UNION SELECT 26 UNION SELECT 27 UNION SELECT 28 UNION SELECT 29
) WHERE CASE WHEN value <= 7 THEN 35 + (RANDOM() % 10) WHEN value <= 14 THEN 18 + (RANDOM() % 8) WHEN value <= 21 THEN 8 + (RANDOM() % 5) ELSE 0 END > 0;

-- code-reviewer (POPULAR)
INSERT INTO downloads (formation_id, version, day, download_count)
SELECT 
  (SELECT id FROM formations WHERE name = 'code-reviewer'),
  '2.1.0',
  DATE('now', '-' || value || ' days'),
  CASE 
    WHEN value <= 7 THEN 28 + (RANDOM() % 10)
    ELSE 22 + (RANDOM() % 8)
  END
FROM (
  SELECT 0 AS value UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 UNION
  SELECT 10 UNION SELECT 11 UNION SELECT 12 UNION SELECT 13 UNION SELECT 14 UNION SELECT 15 UNION SELECT 16 UNION SELECT 17 UNION SELECT 18 UNION SELECT 19 UNION
  SELECT 20 UNION SELECT 21 UNION SELECT 22 UNION SELECT 23 UNION SELECT 24 UNION SELECT 25 UNION SELECT 26 UNION SELECT 27 UNION SELECT 28 UNION SELECT 29 UNION
  SELECT 30 UNION SELECT 31 UNION SELECT 32 UNION SELECT 33 UNION SELECT 34 UNION SELECT 35 UNION SELECT 36 UNION SELECT 37 UNION SELECT 38 UNION SELECT 39 UNION
  SELECT 40 UNION SELECT 41 UNION SELECT 42 UNION SELECT 43 UNION SELECT 44 UNION SELECT 45 UNION SELECT 46 UNION SELECT 47 UNION SELECT 48 UNION SELECT 49 UNION
  SELECT 50 UNION SELECT 51 UNION SELECT 52 UNION SELECT 53 UNION SELECT 54 UNION SELECT 55 UNION SELECT 56 UNION SELECT 57 UNION SELECT 58 UNION SELECT 59
);

-- meeting-scheduler (MODERATE)
INSERT INTO downloads (formation_id, version, day, download_count)
SELECT 
  (SELECT id FROM formations WHERE name = 'meeting-scheduler'),
  '1.5.0',
  DATE('now', '-' || value || ' days'),
  15 + (RANDOM() % 5)
FROM (
  SELECT 0 AS value UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 UNION
  SELECT 10 UNION SELECT 11 UNION SELECT 12 UNION SELECT 13 UNION SELECT 14 UNION SELECT 15 UNION SELECT 16 UNION SELECT 17 UNION SELECT 18 UNION SELECT 19 UNION
  SELECT 20 UNION SELECT 21 UNION SELECT 22 UNION SELECT 23 UNION SELECT 24 UNION SELECT 25 UNION SELECT 26 UNION SELECT 27 UNION SELECT 28 UNION SELECT 29 UNION
  SELECT 30 UNION SELECT 31 UNION SELECT 32 UNION SELECT 33 UNION SELECT 34 UNION SELECT 35 UNION SELECT 36 UNION SELECT 37 UNION SELECT 38 UNION SELECT 39 UNION
  SELECT 40 UNION SELECT 41 UNION SELECT 42 UNION SELECT 43 UNION SELECT 44 UNION SELECT 45
);

-- slack-bot (POPULAR but declining slightly)
INSERT INTO downloads (formation_id, version, day, download_count)
SELECT 
  (SELECT id FROM formations WHERE name = 'slack-bot'),
  '2.0.1',
  DATE('now', '-' || value || ' days'),
  CASE 
    WHEN value <= 14 THEN 18 + (RANDOM() % 6)
    ELSE 25 + (RANDOM() % 8)
  END
FROM (
  SELECT 0 AS value UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 UNION
  SELECT 10 UNION SELECT 11 UNION SELECT 12 UNION SELECT 13 UNION SELECT 14 UNION SELECT 15 UNION SELECT 16 UNION SELECT 17 UNION SELECT 18 UNION SELECT 19 UNION
  SELECT 20 UNION SELECT 21 UNION SELECT 22 UNION SELECT 23 UNION SELECT 24 UNION SELECT 25 UNION SELECT 26 UNION SELECT 27 UNION SELECT 28 UNION SELECT 29 UNION
  SELECT 30 UNION SELECT 31 UNION SELECT 32 UNION SELECT 33 UNION SELECT 34 UNION SELECT 35 UNION SELECT 36 UNION SELECT 37 UNION SELECT 38 UNION SELECT 39 UNION
  SELECT 40 UNION SELECT 41 UNION SELECT 42 UNION SELECT 43 UNION SELECT 44 UNION SELECT 45 UNION SELECT 46 UNION SELECT 47 UNION SELECT 48 UNION SELECT 49 UNION
  SELECT 50 UNION SELECT 51 UNION SELECT 52 UNION SELECT 53 UNION SELECT 54 UNION SELECT 55 UNION SELECT 56 UNION SELECT 57 UNION SELECT 58 UNION SELECT 59
);

-- github-webhook (NICHE - low but steady)
INSERT INTO downloads (formation_id, version, day, download_count)
SELECT 
  (SELECT id FROM formations WHERE name = 'github-webhook'),
  '1.1.0',
  DATE('now', '-' || value || ' days'),
  5 + (RANDOM() % 4)
FROM (
  SELECT 0 AS value UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 UNION
  SELECT 10 UNION SELECT 11 UNION SELECT 12 UNION SELECT 13 UNION SELECT 14 UNION SELECT 15 UNION SELECT 16 UNION SELECT 17 UNION SELECT 18 UNION SELECT 19 UNION
  SELECT 20 UNION SELECT 21 UNION SELECT 22 UNION SELECT 23 UNION SELECT 24 UNION SELECT 25 UNION SELECT 26 UNION SELECT 27 UNION SELECT 28 UNION SELECT 29 UNION
  SELECT 30
);

-- document-qa (VERY TRENDING - recent viral growth)
INSERT INTO downloads (formation_id, version, day, download_count)
SELECT 
  (SELECT id FROM formations WHERE name = 'document-qa'),
  '1.0.0',
  DATE('now', '-' || value || ' days'),
  CASE 
    WHEN value <= 3 THEN 55 + (RANDOM() % 15)   -- Last 3 days: VIRAL (55-70)
    WHEN value <= 7 THEN 38 + (RANDOM() % 12)   -- Last week: 38-50
    WHEN value <= 10 THEN 12 + (RANDOM() % 8)   -- Just before: 12-20
    ELSE 0                                       -- Didn't exist
  END
FROM (
  SELECT 0 AS value UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 UNION SELECT 10
) WHERE CASE WHEN value <= 3 THEN 55 + (RANDOM() % 15) WHEN value <= 7 THEN 38 + (RANDOM() % 12) WHEN value <= 10 THEN 12 + (RANDOM() % 8) ELSE 0 END > 0;

-- Update total_downloads for all formations based on downloads table
UPDATE formations SET total_downloads = (
  SELECT COALESCE(SUM(download_count), 0)
  FROM downloads
  WHERE downloads.formation_id = formations.id
);

-- ============================================
-- REBUILD FTS5 INDEX
-- ============================================
-- After bulk inserts, the FTS5 index needs to be explicitly rebuilt for search to work.
INSERT INTO formations_fts(formations_fts) VALUES('rebuild');

COMMIT;

-- ============================================
-- VERIFICATION
-- ============================================
SELECT '✓ Test data loaded successfully!' as status;
SELECT 'Users: ' || COUNT(*) FROM users;
SELECT 'Formations: ' || COUNT(*) FROM formations;
SELECT 'Versions: ' || COUNT(*) FROM versions;
