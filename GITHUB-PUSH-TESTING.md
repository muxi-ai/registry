# GitHub Push Testing Guide

## ⚠️ IMPORTANT: Supervised Testing Required

GitHub operations are currently **commented out** to avoid spamming GitHub with test repositories during development. Before enabling, ensure you can monitor and clean up test repos immediately.

## Pre-Testing Checklist

- [ ] You have 30-60 minutes to supervise testing
- [ ] You're ready to delete test repos immediately after creation
- [ ] You have GitHub personal access token with repo creation permissions
- [ ] You understand this will create real GitHub repositories

## Steps to Enable GitHub Operations

### 1. Uncomment GitHub Code Block

Edit `website/app/controllers/api/formations.php`, line ~260:

```php
// Remove these lines:
// ========== GITHUB OPERATIONS TEMPORARILY DISABLED FOR TESTING ==========
/*

// And remove closing comment (line ~295):
*/
// ========== END GITHUB OPERATIONS ==========
```

### 2. Comment Out Mock Data

Comment out or remove the mock GitHub data block (lines ~297-327):

```php
/*
// Mock GitHub data for testing without actual GitHub push
$repoName = "muxi-{$formationData['id']}";
...
*/
```

### 3. Test with Disposable Formation

Create a test formation: `test-upload-delete-me`

```bash
# Create minimal test formation
mkdir test-upload-delete-me
cd test-upload-delete-me

cat > formation.yaml << 'YAML'
schema: "1.0.0"
id: "test-upload-delete-me"
description: "Temporary test formation - DELETE IMMEDIATELY"
runtime: 1.2.4
YAML

zip -r ../test-formation.zip .
cd ..
```

### 4. Upload Test Formation

```bash
curl -X POST https://registry.muxi.org/api/formations/publish \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "file=@test-formation.zip"
```

### 5. Verify GitHub Repository

Check that the following were created:

1. **Repository**: `https://github.com/USERNAME/muxi-test-upload-delete-me`
2. **Files**: formation.yaml, README.md pushed to main branch
3. **Release**: v1.0.0 with formation.zip asset
4. **Topics**: `muxi`, `formation`, and generated categories

### 6. Immediate Cleanup

**DELETE THE TEST REPO IMMEDIATELY**:

```bash
# Via GitHub CLI
gh repo delete USERNAME/muxi-test-upload-delete-me --confirm

# Or via web: https://github.com/USERNAME/muxi-test-upload-delete-me/settings
```

Also delete from database:

```sql
DELETE FROM formations WHERE name = 'test-upload-delete-me';
```

## What to Test

### Test Case 1: Personal Repository
- [ ] Upload without org parameter
- [ ] Verify repo created at `USERNAME/muxi-formation-name`
- [ ] Check README is LLM-generated
- [ ] Verify categories in database
- [ ] Confirm topics on GitHub repo
- [ ] Download and verify formation.zip asset

### Test Case 2: Organization Repository
- [ ] Upload with `org=your-org` parameter
- [ ] Verify repo created at `org/muxi-formation-name`
- [ ] Confirm user is credited in registry
- [ ] Verify org membership check works
- [ ] Test rejection for non-member users

### Test Case 3: Error Handling
- [ ] Invalid ZIP file
- [ ] Missing formation.yaml
- [ ] Invalid version format
- [ ] GitHub API failures (simulate by using invalid token)

## Expected Behavior

### Success Response
```json
{
  "status": "ok",
  "message": "Formation published successfully",
  "formation": {
    "name": "test-upload-delete-me",
    "user": "username",
    "version": "1.0.0",
    "github_repo": "username/muxi-test-upload-delete-me",
    "registry_url": "https://registry.muxi.org/@username/test-upload-delete-me",
    "download_url": "https://github.com/username/muxi-test-upload-delete-me/releases/download/v1.0.0/formation.zip"
  }
}
```

### GitHub Repository Should Have
- **README.md**: LLM-generated content
- **formation.yaml**: Original file from ZIP
- **Other files**: All files from ZIP (agents/, etc.)
- **Release v1.0.0**: With formation.zip asset
- **Topics**: muxi, formation, + categories

## Debugging

### If GitHub API Fails

Check error logs:
```bash
tail -f /Applications/ServBay/logs/php/8.4/errors.log
```

Common issues:
- **401 Unauthorized**: Token expired or invalid
- **422 Validation Failed**: Repo already exists
- **403 Forbidden**: Rate limit or permission issue
- **404 Not Found**: User/org doesn't exist

### If Files Don't Upload

- Check GitHub token has `repo` scope
- Verify file paths in ZIP don't have traversal attempts
- Check file size limits (100MB per file)

### If Release Fails

- Ensure tag doesn't already exist
- Check asset upload size limits
- Verify token has release creation permissions

## Rollback Plan

If something goes wrong:

1. **Disable GitHub Push Immediately**:
   ```bash
   git checkout website/app/controllers/api/formations.php
   ```

2. **Clean Up Test Data**:
   ```sql
   DELETE FROM formations WHERE name LIKE 'test-%';
   DELETE FROM downloads WHERE formation_id NOT IN (SELECT id FROM formations);
   ```

3. **Delete Test Repos**:
   - Visit https://github.com/USERNAME?tab=repositories
   - Delete all repos starting with `muxi-test-`

## Success Criteria

✅ Repository created successfully  
✅ All files pushed to main branch  
✅ Release created with asset  
✅ Topics/tags applied correctly  
✅ Database entry created  
✅ README is LLM-generated (not fallback)  
✅ Categories stored in database  
✅ Formation downloadable and installable  
✅ Test repos deleted with no traces  

## After Successful Testing

1. **Document any issues encountered**
2. **Update this guide with lessons learned**
3. **Consider rate limit implications**
4. **Plan production deployment**
5. **Monitor error logs for first real uploads**

## Estimated Time

- Setup: 5 minutes
- Testing: 15-30 minutes
- Cleanup: 5 minutes
- **Total: 30-60 minutes**

## Notes

- Test during off-peak hours if possible
- Have GitHub and database access ready
- Keep error logs visible during testing
- Be prepared to quickly disable if issues arise
- Document any GitHub API quirks discovered
