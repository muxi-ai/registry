# Search Model

**Location**: `website/app/models/searchmodel.php`

The SearchModel provides a centralized, reusable search implementation shared between the web UI and API controllers.

---

## Usage

### Basic Search

```php
// In any controller
$results = tiny::model('searchmodel')->searchFormations(
    $query,    // Search query string
    $sort,     // Sort order: trending|downloads|recent|stars
    $limit     // Maximum results (1-100)
);
```

### Search with Typo Correction

```php
// Returns both results and suggested correction
$result = tiny::model('searchmodel')->searchWithTypoCorrection($query);

$formations = $result['formations'];
$correction = $result['correction']; // "support" if user typed "supprot"
```

### Helper Methods

```php
// Get trending formations (last 7 days)
$trending = tiny::model('searchmodel')->getTrending(10);

// Get most downloaded (all time)
$popular = tiny::model('searchmodel')->getPopular(10);

// Get recently published
$recent = tiny::model('searchmodel')->getRecent(10);
```

---

## Search Strategies

The model implements a **multi-strategy approach** for optimal results:

### 1. FTS5 Exact Match (Fast)
Uses SQLite Full-Text Search index for exact phrase matching.

```sql
SELECT ... WHERE formations_fts MATCH 'query'
```

### 2. FTS5 Prefix Match (Partial Words)
Matches partial words with wildcard suffix.

```sql
WHERE formations_fts MATCH 'query*'
```

### 3. LIKE Pattern Fallback (Fuzzy)
Catches substring matches anywhere in name/description/readme.

```sql
WHERE name LIKE '%query%' 
   OR description LIKE '%query%'
   OR readme_md LIKE '%query%'
```

### 4. Levenshtein Distance (Typo Correction)
Calculates edit distance for "did you mean" suggestions.

```php
levenshtein('supprot', 'support') => 2 (close enough!)
```

---

## Controllers Using This Model

### Web UI: `app/controllers/search.php`
- Provides search results page
- Includes typo correction UI ("Did you mean X?")
- Renders HTML with formation cards

### API: `app/controllers/api/search.php`
- Returns JSON search results
- No typo correction (API clients handle that)
- Includes pagination and sort options

---

## Return Format

Both strategies 1-3 return an array of formation objects:

```php
[
    [
        'id' => 1,
        'name' => 'customer-support',
        'registry_username' => 'muxi',
        'github_username' => 'muxi-ai',
        'description' => '...',
        'latest_version' => '1.2.3',
        'total_downloads' => 1234,
        'github_stars' => 145,
        'github_repo' => 'muxi-ai/muxi-customer-support',
        'published_at' => '2025-01-15 10:30:00',
        // ... other fields
    ],
    // ... more results
]
```

---

## Sort Options

| Option | Behavior |
|--------|----------|
| `trending` | By downloads in last 7 days, then stars |
| `downloads` | By total downloads (all time) |
| `recent` | By publish date (newest first) |
| `stars` | By GitHub stars |

---

## Example: Adding a New Controller

```php
class Browse extends TinyController
{
    public function get($request, $response)
    {
        // Get trending formations for the homepage
        $trending = tiny::model('searchmodel')->getTrending(12);
        
        // Get popular formations
        $popular = tiny::model('searchmodel')->getPopular(12);
        
        // Or do a custom search
        $results = tiny::model('searchmodel')->searchFormations(
            'customer support',
            'trending',
            20
        );
        
        $response->render('browse', [
            'trending' => $trending,
            'popular' => $popular,
            'results' => $results
        ]);
    }
}
```

---

## Benefits of DRY Architecture

✅ **Single Source of Truth**: One place to update search logic  
✅ **Consistency**: UI and API always return same results  
✅ **Testable**: Model can be unit tested independently  
✅ **Reusable**: Easy to add search to new controllers  
✅ **Maintainable**: Bug fixes apply everywhere automatically

---

## Future Enhancements

Potential improvements to the search model:

- [ ] Add search result caching (Redis/Memcached)
- [ ] Implement proper trending score (velocity + recency)
- [ ] Add tag-based filtering
- [ ] Support advanced search operators (AND/OR/NOT)
- [ ] Add search analytics tracking
- [ ] Implement result ranking ML model
- [ ] Add personalized search (user preferences)

---

**Last Updated**: 2025-10-28  
**Related Files**: 
- `app/controllers/search.php` (Web UI)
- `app/controllers/api/search.php` (API)
- `schema.sql` (FTS5 table: formations_fts)
