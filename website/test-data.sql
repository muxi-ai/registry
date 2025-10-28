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

COMMIT;

-- ============================================
-- VERIFICATION
-- ============================================
SELECT '✓ Test data loaded successfully!' as status;
SELECT 'Users: ' || COUNT(*) FROM users;
SELECT 'Formations: ' || COUNT(*) FROM formations;
SELECT 'Versions: ' || COUNT(*) FROM versions;
