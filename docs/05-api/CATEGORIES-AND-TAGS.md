# Categories and GitHub Tags

**Date**: 2025-10-28  
**Status**: ✅ Complete

---

## Overview

Formations now have automatic categorization via LLM, which is:
1. Stored in the database
2. Added as GitHub repository topics/tags
3. Included in generated READMEs with links

---

## Features

### 1. LLM-Generated Categories

When a formation is published without a README, the LLM generates:
- Comprehensive README
- Up to 3 relevant categories

**Categories**:
- Lowercase with hyphens (e.g., `customer-support`, `automation`)
- Based on formation purpose and structure
- Suggested by GPT-4o-mini analyzing formation data

### 2. Database Storage

**Schema**:
```sql
ALTER TABLE formations ADD COLUMN categories TEXT;
```

**Format**: JSON array stored as TEXT
```json
["customer-support", "automation", "ai-assistant"]
```

**Example**:
```sql
SELECT id, name, categories FROM formations WHERE categories IS NOT NULL;

-- Result:
-- 1 | customer-support | ["customer-support","automation","ai-assistant"]
-- 2 | data-processor | ["data-processing","automation","etl"]
```

### 3. GitHub Topics

Formations automatically get GitHub repository topics:
- `muxi` (always)
- `formation` (always)
- Generated categories (up to 3)

**Example**:
- Repository: `github.com/user/muxi-customer-support`
- Topics: `muxi`, `formation`, `customer-support`, `automation`, `ai-assistant`

**Visibility**: Topics appear on GitHub repo page and in search results

### 4. Registry Links in README

Generated READMEs now include links section:
```markdown
## Links

- [Formation on MUXI Registry](https://registry.muxi.org/@owner/formation-name)
- [MUXI Documentation](https://muxi.org)
```

---

## Implementation

### Categories Flow

```
1. Upload formation.zip
   ↓
2. If no README: generateReadmeWithLLM()
   ↓
3. LLM returns: {readme, categories}
   ↓
4. Store categories in formationData['_generated_categories']
   ↓
5. Set GitHub topics: ['muxi', 'formation', ...categories]
   ↓
6. Store in database: categories JSON field
```

### Code Components

#### 1. LLM Generation
```php
// In generateReadmeWithLLM()
if (isset($data['categories']) && is_array($data['categories'])) {
    $formationData['_generated_categories'] = $data['categories'];
    error_log("Generated categories: " . implode(', ', $data['categories']));
}
```

#### 2. GitHub Topics
```php
// New method: setGitHubTopics()
private function setGitHubTopics($repoName, $formationData)
{
    $topics = ['muxi', 'formation'];
    
    if (isset($formationData['_generated_categories'])) {
        foreach ($formationData['_generated_categories'] as $category) {
            $topics[] = $category;
        }
    }
    
    $this->github->setTopics($repoName, array_unique($topics));
}
```

#### 3. Database Storage
```php
// In storeFormationInDatabase()
$categories = null;
if (isset($formationData['_generated_categories'])) {
    $categories = json_encode($formationData['_generated_categories']);
}

$data = [
    // ... other fields
    'categories' => $categories,
];
```

#### 4. GitHub Helper
```php
// New method in GitHub class
public function setTopics($repo, $topics)
{
    $topics = array_map(function($topic) {
        return strtolower(str_replace(' ', '-', trim($topic)));
    }, $topics);
    
    return $this->request("/repos/$repo/topics", 'PUT', [
        'names' => $topics
    ]);
}
```

---

## GitHub Topics API

### Endpoint
```
PUT /repos/{owner}/{repo}/topics
```

### Request
```json
{
  "names": ["muxi", "formation", "customer-support"]
}
```

### Requirements
- Topics must be lowercase
- Spaces should be hyphens
- Maximum 20 topics per repo
- Requires `public_repo` or `repo` scope

### Response
```json
{
  "names": ["muxi", "formation", "customer-support"]
}
```

---

## Database Schema

### Migration

```sql
-- Add categories column to formations table
ALTER TABLE formations ADD COLUMN categories TEXT;
```

### Query Examples

**Find formations by category**:
```sql
SELECT * FROM formations 
WHERE categories LIKE '%"customer-support"%';
```

**Count formations by category**:
```sql
SELECT 
    json_extract(value, '$') as category,
    COUNT(*) as count
FROM formations, json_each(categories)
WHERE categories IS NOT NULL
GROUP BY category
ORDER BY count DESC;
```

**Get all unique categories**:
```sql
SELECT DISTINCT json_extract(value, '$') as category
FROM formations, json_each(categories)
WHERE categories IS NOT NULL
ORDER BY category;
```

---

## README Template Updated

### Before
```markdown
# formation-name

Description here

## Installation

```bash
muxi pull @owner/formation-name
```

## License

MIT
```

### After (with links)
```markdown
# formation-name

Description here

## Installation

```bash
muxi pull @owner/formation-name
```

## License

MIT

## Links

- [Formation on MUXI Registry](https://registry.muxi.org/@owner/formation-name)
- [MUXI Documentation](https://muxi.org)
```

---

## Example Categories by Type

### Customer Support
- `customer-support`
- `automation`
- `ai-assistant`

### Data Processing
- `data-processing`
- `automation`
- `etl`

### Content Generation
- `content-generation`
- `ai-writing`
- `marketing`

### Development Tools
- `developer-tools`
- `code-review`
- `automation`

### Business Automation
- `business-automation`
- `workflow`
- `productivity`

---

## Future: Browse by Category

### When Enough Data Exists

**UI Features** (not yet implemented):
- Browse page with category filters
- Category-based search
- "Related formations" based on categories
- Category tags on formation cards

**Example UI**:
```
Browse Formations

Categories:
☑ customer-support (12)
☐ automation (45)
☐ data-processing (8)
☐ ai-assistant (23)

Results: 12 formations
```

### API Endpoint (Future)

```bash
GET /api/formations?category=customer-support

# Response:
{
  "formations": [...],
  "count": 12,
  "category": "customer-support"
}
```

---

## Error Handling

### Topics Setting Failure

**Behavior**: Logged but doesn't fail publish
```php
try {
    $this->github->setTopics($repoName, $topics);
} catch (Exception $e) {
    error_log("Failed to set GitHub topics: " . $e->getMessage());
    // Continue publish flow
}
```

**Reasons for failure**:
- GitHub API rate limit
- Permission issue
- Network error
- Invalid topic format

**Impact**: Formation still publishes, just without topics

### Categories Not Generated

**When**:
- LLM API fails
- API key missing
- Invalid response

**Behavior**: 
- Uses basic README template
- No categories stored
- GitHub topics: only `muxi` and `formation`

---

## Benefits

### For Discovery
✅ **GitHub Search** - Topics make formations findable on GitHub  
✅ **Related Repos** - GitHub shows related repos with same topics  
✅ **Future Browse** - Categories enable filtering on registry

### For Organization
✅ **Automatic Classification** - No manual tagging needed  
✅ **Consistent Naming** - LLM ensures standard category names  
✅ **Scalable** - Works for any formation type

### For SEO
✅ **GitHub Visibility** - Topics improve GitHub discoverability  
✅ **Registry Links** - README links drive traffic to registry  
✅ **MUXI Brand** - muxi.org link in every formation

---

## Monitoring

### Logs

```bash
# Categories generated
tail -f /path/to/error.log | grep "Generated categories"

# Topics setting failures
tail -f /path/to/error.log | grep "Failed to set GitHub topics"
```

### Metrics

**To Track**:
- % of formations with categories
- Most common categories
- Topics setting success rate
- Click-through rate on registry links

**Queries**:
```sql
-- Formations with categories
SELECT COUNT(*) FROM formations WHERE categories IS NOT NULL;

-- Total formations
SELECT COUNT(*) FROM formations;

-- Category distribution
SELECT 
    json_extract(value, '$') as category,
    COUNT(*) as count
FROM formations, json_each(categories)
WHERE categories IS NOT NULL
GROUP BY category
ORDER BY count DESC
LIMIT 10;
```

---

## Testing

### Manual Test

1. **Publish formation without README**:
   ```bash
   cd /tmp/test-formation
   # Only create formation.yaml (no README.md)
   zip -r formation.zip .
   
   curl -X POST https://muxi.registry/api/formations/publish \
     -H "Authorization: Bearer mxr_xxx" \
     -F "file=@formation.zip"
   ```

2. **Check GitHub topics**:
   - Visit `github.com/user/muxi-formation-name`
   - Topics should show: `muxi`, `formation`, + categories

3. **Check README**:
   - Should include links section
   - Links should point to registry and muxi.org

4. **Check database**:
   ```sql
   SELECT categories FROM formations 
   WHERE name = 'formation-name';
   ```

---

## Related Documentation

- [LLM-README-GENERATION.md](./LLM-README-GENERATION.md) - README generation
- [PUBLISH-IMPLEMENTATION.md](./PUBLISH-IMPLEMENTATION.md) - Publish flow

---

## Summary

✅ **Categories stored** - JSON in database  
✅ **GitHub topics set** - muxi, formation, + categories  
✅ **Registry links added** - In generated READMEs  
✅ **Graceful fallback** - Errors don't break publish  
✅ **Ready for browse** - Data structure in place

**Impact**: Better discoverability on GitHub and foundation for category browsing! 🏷️
