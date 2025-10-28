# Pull Tracking Refactor

**Date**: 2025-10-28  
**Status**: ✅ Complete

---

## Overview

Refactored the pull tracking logic to distinguish between **info requests** (getting formation metadata) and **actual pulls** (downloading formations).

## Problem

- **Before**: Every GET request to `/api/formations/@user/name` was counted as a download
- **Issue**: CLI needs to check formation info without inflating download counts
- **Need**: Separate info retrieval from actual pull operations

## Solution

### 1. Query Parameter for Pull Tracking

Only track downloads when `?pull=true` is present:

```bash
# Info only (no tracking)
GET /api/formations/@user/name

# Actual pull (tracks download)
GET /api/formations/@user/name?pull=true
```

### 2. Version Syntax Support

Added `:version` syntax for version-specific requests:

```bash
# Latest version
GET /api/formations/@user/name

# Specific version
GET /api/formations/@user/name:1.2.0

# Version-specific pull
GET /api/formations/@user/name:1.2.0?pull=true
```

### 3. Simplified Download Tracking

- **Removed**: Direct `total_downloads` increment in formations table
- **New**: Only track in `downloads` table (daily granular data)
- **Rationale**: `total_downloads` can be calculated from sum of downloads table
- **Benefit**: Single source of truth, easier analytics

```php
// Old approach (removed)
trackDownload() {
    1. Increment formations.total_downloads
    2. Insert/update downloads table
}

// New approach
trackDownload() {
    1. Insert/update downloads table only
    // total_downloads = SUM(downloads.download_count)
}
```

## Implementation Details

### Route Parsing

```php
// Parse URL: /api/formations/@user/name:version
$nameWithVersion = $parts[1];
$versionParts = explode(':', $nameWithVersion, 2);
$name = $versionParts[0];
$requestedVersion = $versionParts[1] ?? null; // null = latest
```

### Conditional Tracking

```php
// Only track if ?pull=true
if (isset($_GET['pull']) && $_GET['pull'] === 'true') {
    $this->trackDownload($formationId, $version);
}
```

### Version Validation

```php
// If specific version requested, verify it exists
if ($version !== null) {
    $versionRecord = tiny::db()->getOne('versions', [
        'formation_id' => $formation['id'],
        'version' => $version
    ]);
    
    if (!$versionRecord) {
        return null; // Version not found
    }
    
    $formation['latest_version'] = $version;
}
```

## GitHub Helper Refactoring

### Converted to Tiny Helper

- **From**: `website/app/lib/GitHub.php` (standalone class with curl)
- **To**: `website/tiny/helpers/github.php` (Tiny helper with `tiny::http()`)

### Key Improvements

1. **Uses Tiny HTTP Module**:
   ```php
   // Old
   $ch = curl_init($url);
   curl_setopt(...);
   
   // New
   $response = tiny::http()->get($url, ['headers' => $headers]);
   ```

2. **Token Management**:
   ```php
   $github->setToken($githubToken);   // Set for authenticated requests
   $github->clearToken();              // Clear for public requests
   ```

3. **Proper Registration**:
   ```php
   tiny::registerHelper('github', function () {
       return new GitHub();
   });
   ```

4. **Controller Usage**:
   ```php
   tiny::helpers(['github']);
   
   class ApiFormations extends TinyController {
       private GitHub $github;
       
       public function __construct() {
           $this->github = tiny::github(null, 'MUXI-Registry');
       }
   }
   ```

## Testing Results

### Test 1: Info Request (No Tracking)
```bash
# Before request
downloads WHERE formation_id=2 AND day=today: (no records)

# Request
curl "https://muxi.registry/api/formations/@ranaroussi/meeting-scheduler"

# After request
downloads WHERE formation_id=2 AND day=today: (no records)
✅ PASS: No tracking occurred
```

### Test 2: Pull Request (With Tracking)
```bash
# Before request
downloads WHERE formation_id=2 AND version='1.1.2' AND day=today: (no records)

# Request
curl "https://muxi.registry/api/formations/@ranaroussi/meeting-scheduler?pull=true"

# After request
downloads WHERE formation_id=2 AND version='1.1.2' AND day=today: count=1
✅ PASS: Download tracked
```

### Test 3: Version-Specific Pull
```bash
# Before request
downloads WHERE formation_id=13 AND version='1.2.3' AND day=today: count=51

# Request
curl "https://muxi.registry/api/formations/@muxi/customer-support:1.2.3?pull=true"

# After request
downloads WHERE formation_id=13 AND version='1.2.3' AND day=today: count=52
✅ PASS: Version-specific tracking works
```

### Test 4: Multiple Pulls Increment Count
```bash
# Request 1
curl "...?pull=true"  # count=7

# Request 2
curl "...?pull=true"  # count=8

✅ PASS: Increments correctly
```

## Benefits

1. **Accurate Analytics**: Only actual pulls are counted
2. **CLI Flexibility**: Can check info without affecting stats
3. **Version Tracking**: Know which versions are being used
4. **Cleaner Architecture**: Single source of truth for download data
5. **Future-Ready**: Easy to add weekly/monthly aggregations

## Database Schema

```sql
-- Downloads table (unchanged)
CREATE TABLE downloads (
  id INTEGER PRIMARY KEY,
  formation_id INTEGER NOT NULL,
  version TEXT NOT NULL,
  day DATE NOT NULL,
  download_count INTEGER DEFAULT 0,
  UNIQUE(formation_id, version, day)
);

-- Total downloads calculated via:
SELECT SUM(download_count) 
FROM downloads 
WHERE formation_id = ?;
```

## Migration Notes

- **No breaking changes**: Existing formations table unchanged
- **Backward compatible**: Old total_downloads values preserved
- **Can coexist**: Both tracking methods can run in parallel during transition
- **Analytics**: Historical data intact, new granular tracking begins now

## Next Steps

- [ ] Implement `muxi push` (file upload endpoint)
- [ ] Add `downloads_this_week` calculation
- [ ] Consider background job to sync total_downloads from downloads table
- [ ] Add download analytics dashboard
