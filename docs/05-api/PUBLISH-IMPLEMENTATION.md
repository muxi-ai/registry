# Publish Flow Implementation (muxi push)

**Date**: 2025-10-28  
**Status**: ✅ Complete (Pending E2E Testing)

---

## Overview

Implemented complete `muxi push` functionality. Registry now accepts formation ZIP uploads, processes them, creates GitHub repositories, and publishes releases.

## Architecture Shift

**OLD Approach** (Phase 2.0):
- CLI creates GitHub repo
- CLI creates release
- CLI uploads assets
- CLI notifies registry
- ❌ Problem: CLI needs GitHub OAuth token

**NEW Approach** (Phase 2.5):
- ✅ CLI only has `mxr_` token
- ✅ CLI zips and uploads to registry
- ✅ Registry has GitHub OAuth token (stored securely)
- ✅ Registry handles all GitHub operations
- ✅ Registry is the gatekeeper

## Endpoint

### `POST /api/formations/publish`

**Purpose**: Publish a formation from uploaded ZIP file

**Authentication**: Required (Bearer token)

**Request**: `multipart/form-data`
```bash
POST /api/formations/publish
Authorization: Bearer mxr_xxx
Content-Type: multipart/form-data

file=@formation.zip
org=optional-org-name (optional)
```

**Response** (200 OK):
```json
{
  "status": "ok",
  "message": "Formation published successfully",
  "formation": {
    "name": "test-formation",
    "user": "ranaroussi",
    "version": "1.0.0",
    "github_repo": "ranaroussi/muxi-test-formation",
    "registry_url": "https://registry.muxi.org/@ranaroussi/test-formation",
    "download_url": "https://github.com/ranaroussi/muxi-test-formation/releases/download/v1.0.0/formation.zip"
  }
}
```

## Implementation Flow

```
1. Validate uploaded file
   └─ Check file exists and is ZIP
   
2. Unzip to temp directory
   └─ Extract all files
   
3. Parse formation.yaml
   └─ Validate YAML syntax
   
4. Validate required fields
   ├─ id (formation name)
   ├─ version (semver format)
   └─ description
   
5. Generate README (if missing)
   └─ Basic template
   └─ TODO: LLM-generated comprehensive README
   
6. Verify GitHub permissions
   ├─ If org: check membership
   └─ Get user's GitHub OAuth token
   
7. Create/verify GitHub repository
   ├─ Check if muxi-{id} exists
   └─ Create if not found
   
8. Push files to GitHub
   └─ Use GitHub Contents API
   └─ Update existing files (uses SHA)
   
9. Create GitHub release
   ├─ Tag: v{version}
   ├─ Name: v{version}
   └─ Body: description
   
10. Upload ZIP as release asset
    └─ Upload to GitHub releases
    
11. Store metadata in database
    ├─ formations table
    └─ versions table
    
12. Clean up temp files
    └─ Remove temp directory
```

## Key Methods

### Main Handler
```php
processAndPublishFormation($user, $uploadedFile, $orgName)
```
- Orchestrates entire publish flow
- Handles cleanup in finally block
- Returns formatted response

### Helper Methods

**File Processing**:
- `generateBasicReadme($formationData)` - Creates basic README
- `getFilesRecursive($dir)` - Gets all files from directory
- `repackFormation($dir, $formationId)` - Creates ZIP archive
- `removeDirectory($dir)` - Recursive directory removal

**GitHub Operations**:
- `createOrGetGitHubRepo($owner, $repoName, $data)` - Create or get repo
- `pushFilesToGitHub($repoName, $tempDir)` - Push files via Contents API
- `createGitHubRelease($repoName, $version, $data)` - Create release
- `uploadReleaseAsset($repoName, $releaseId, $zipPath, $fileName)` - Upload asset

**Database**:
- `storeFormationInDatabase($userId, $data, $repo, $release)` - Store metadata

## Validation Rules

### Required Fields in formation.yaml
```yaml
id: string          # Formation name (alphanumeric, dashes, underscores)
version: string     # Semver format (e.g., 1.0.0)
description: string # Short description
```

### Optional Fields
```yaml
author: string      # Author name
license: string     # License identifier (default: MIT)
```

### Version Format
- Must be valid semver: `\d+\.\d+\.\d+`
- Examples: `1.0.0`, `2.3.1`, `0.1.0`
- No `v` prefix

### File Validation
- Must be valid ZIP archive
- Must contain `formation.yaml` at root
- YAML must be parseable

## Error Handling

| Error Code | Message | HTTP Code |
|-----------|---------|-----------|
| API-01 | Authentication required | 401 |
| API-13 | No formation.zip file uploaded | 400 |
| API-14 | Uploaded file must be a ZIP archive | 400 |
| API-15 | {exception message} | 400 |

### Exception Messages
- `Invalid ZIP file or unable to extract`
- `formation.yaml not found in ZIP archive`
- `Invalid formation.yaml format`
- `Missing or empty required field: {field}`
- `Version must be in semver format (e.g., 1.0.0)`
- `You are not a member of organization: {org}`
- GitHub API errors (propagated from GitHub class)

## README Generation

### Basic Template (Current)
```markdown
# {formation-id}

{description}

## Installation

```bash
muxi pull @owner/{formation-id}
```

## Version

Current version: {version}

## Author

{author}

## License

{license}

---

*This README was auto-generated. Please update with detailed documentation.*
```

### TODO: LLM-Generated README
```php
// TODO: Use LLM to generate comprehensive README from formation data
// - Analyze formation structure (agents, MCPs, triggers, etc.)
// - Generate usage examples
- Create configuration documentation
// - Add troubleshooting section
// - Include component descriptions
```

## Database Updates

### formations Table
```sql
INSERT INTO formations (
  user_id, name, description, readme_md,
  latest_version, github_repo, github_stars, license,
  published_at, last_synced_at, created_at
) VALUES (...);
```

### versions Table
```sql
INSERT INTO versions (
  formation_id, version, release_notes,
  download_url, published_at, created_at
) VALUES (...);
```

## Security Considerations

1. **File Upload**:
   - MIME type validation (application/zip)
   - ZIP bomb protection via temp directory cleanup
   - No arbitrary code execution from ZIP contents

2. **GitHub Token**:
   - User's OAuth token retrieved securely from database
   - Token only used for authenticated GitHub API calls
   - Token cleared after operations

3. **Organization Verification**:
   - Checks membership via GitHub API
   - Prevents publishing to unauthorized orgs

4. **Temp Files**:
   - Unique temp directories per upload
   - Automatic cleanup in finally block
   - Prevents temp file leakage

## Testing

### Test Formation Structure
```
test-formation/
├── formation.yaml   # Required
├── agent.yaml       # Optional component
└── README.md        # Auto-generated if missing
```

### Test Formation YAML
```yaml
id: test-publish
version: 1.0.0
description: Test formation for registry publish flow
author: Test User
license: MIT
```

### Creating Test ZIP
```bash
cd /tmp/test-formation
zip -r test-formation.zip .
```

### Manual Test (with auth)
```bash
curl -X POST https://muxi.registry/api/formations/publish \
  -H "Authorization: Bearer mxr_YOUR_TOKEN" \
  -F "file=@test-formation.zip"
```

## Known Limitations

1. **GitHub Contents API**:
   - Used for pushing files (single file per request)
   - Not ideal for large formations with many files
   - TODO: Consider using Git Data API or direct git push

2. **No Diff/History**:
   - Each publish creates new commits for all files
   - No comparison with previous version
   - TODO: Add smart diff to only update changed files

3. **Synchronous Processing**:
   - Entire flow is synchronous
   - Large formations may timeout
   - TODO: Consider async processing with job queue

4. **README Generation**:
   - Currently basic template only
   - TODO: Integrate LLM for comprehensive README

5. **No Rollback**:
   - If any step fails after GitHub operations, manual cleanup required
   - TODO: Implement transaction-like rollback

## Future Enhancements

### Phase 3: Advanced Features
- [ ] LLM-generated comprehensive READMEs
- [ ] Async processing with progress tracking
- [ ] Smart file diff (only update changed files)
- [ ] Automatic changelog generation
- [ ] Formation validation (structure, syntax, completeness)
- [ ] Preview mode (validate without publishing)
- [ ] Private formations support
- [ ] Team/org permission management
- [ ] Webhook notifications
- [ ] CI/CD integration

### Phase 4: Enterprise Features
- [ ] Formation templates library
- [ ] Version migration tools
- [ ] Dependency resolution
- [ ] Security scanning
- [ ] Compliance checks
- [ ] Analytics dashboard
- [ ] Usage metering
- [ ] SLA monitoring

## Dependencies

### PHP Extensions Required
- `yaml` - YAML parsing
- `zip` - ZIP archive operations
- `fileinfo` - MIME type detection

### Tiny Framework
- `tiny::http()` - HTTP client for GitHub API
- `tiny::db()` - Database operations
- `tiny::user()` - User authentication
- `tiny::github()` - GitHub helper

### External Services
- GitHub API v3
- GitHub Uploads API

## Related Documentation

- [API Implementation](./API-IMPLEMENTATION.md)
- [Pull Tracking Refactor](./PULL-TRACKING-REFACTOR.md)
- [Implementation Plan](./IMPLEMENTATION-PLAN.md)
- [CLI API Scope](./CLI-API-SCOPE.md)

## Conclusion

The publish implementation is feature-complete and ready for testing. All major components are in place:

✅ File upload handling  
✅ Formation validation  
✅ GitHub repository creation  
✅ File pushing via Contents API  
✅ Release creation  
✅ Asset upload  
✅ Database storage  
✅ Proper error handling  
✅ Cleanup and security

**Next Steps**:
1. End-to-end testing with real authentication
2. LLM README generation
3. Performance optimization
4. Production deployment
