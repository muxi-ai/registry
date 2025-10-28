# LLM README Generation

**Date**: 2025-10-28  
**Status**: ✅ Complete

---

## Overview

Implemented automatic README generation using OpenAI's GPT-4o-mini for formations that don't include a README.md file.

## Features

### 1. Comprehensive README Generation

**What it does**:
- Analyzes formation structure (agents, MCPs, SOPs, triggers, knowledge)
- Reads formation.yaml metadata
- Generates professional, well-structured README with:
  - Title and description
  - Features/capabilities
  - Installation instructions
  - Usage/configuration guide
  - Requirements/dependencies
  - License information

### 2. Automatic Categorization

**Categories suggested**:
- Up to 3 relevant categories (e.g., "customer-support", "automation", "data-processing")
- Lowercase with hyphens
- Based on formation purpose and structure
- Currently logged, ready for database storage

---

## Implementation

### Location

`website/app/controllers/api/formations.php`

### Methods

#### 1. generateReadmeWithLLM()

Main method that generates README using LLM.

```php
private function generateReadmeWithLLM($formationData, $tempDir)
{
    try {
        // Analyze formation structure
        $structure = $this->analyzeFormationStructure($tempDir);
        
        // Build prompt with formation data
        $formationInfo = json_encode([
            'id' => $formationData['id'] ?? 'unknown',
            'description' => $formationData['description'] ?? '',
            'version' => $formationData['version'] ?? '1.0.0',
            'runtime' => $formationData['runtime'] ?? 'Not specified',
            'author' => $formationData['author'] ?? null,
            'url' => $formationData['url'] ?? null,
            'license' => $formationData['license'] ?? 'MIT',
            'structure' => $structure
        ], JSON_PRETTY_PRINT);
        
        // Call OpenAI with comprehensive prompt
        $response = tiny::openai()->sendMessage(
            $userPrompt,
            $systemPrompt,
            [],
            2000,  // More tokens for comprehensive README
            'gpt-4o-mini'
        );
        
        // Parse response and return README
        // Falls back to basic README if LLM fails
        
    } catch (Exception $e) {
        // Fallback to basic README
        return $this->generateBasicReadme($formationData);
    }
}
```

#### 2. analyzeFormationStructure()

Analyzes the formation directory to understand its components.

```php
private function analyzeFormationStructure($tempDir)
{
    $structure = [
        'files' => [],
        'components' => [
            'agents' => 0,
            'mcps' => 0,
            'sops' => 0,
            'triggers' => 0,
            'knowledge' => 0
        ]
    ];
    
    // Count files by pattern matching
    // Returns comprehensive structure information
}
```

---

## Prompt Design

### System Prompt

```
You are a technical documentation expert. Generate comprehensive, professional README files for MUXI formations (AI agent configurations).
```

### User Prompt Template

```
Generate a comprehensive README.md for this MUXI formation:

{formation_info}

Requirements:
1. Create a professional, well-structured README with these sections:
   - Title and description
   - Features/capabilities
   - Installation instructions (use: muxi pull @owner/{id})
   - Usage/configuration guide (if applicable)
   - Requirements/dependencies
   - License information

2. Suggest up to 3 relevant categories for this formation

3. Return ONLY valid JSON in this exact format:
{
  "readme": "# Full README content here...",
  "categories": ["category1", "category2", "category3"]
}

Important:
- Make the README engaging and informative
- Use markdown formatting
- Be specific about what this formation does
- Include code examples if relevant based on the structure
- Keep categories lowercase with hyphens
```

### Formation Info Structure

```json
{
  "id": "customer-support",
  "description": "AI-powered customer support agent",
  "version": "1.0.0",
  "runtime": "server 1.0.0",
  "author": "John Doe",
  "url": "https://example.com",
  "license": "MIT",
  "structure": {
    "files": ["formation.yaml", "agent.yaml", "knowledge/faq.md"],
    "components": {
      "agents": 2,
      "mcps": 1,
      "sops": 3,
      "triggers": 1,
      "knowledge": 5
    }
  }
}
```

---

## Response Format

### Expected JSON Response

```json
{
  "readme": "# customer-support\n\nAI-powered customer support agent...",
  "categories": ["customer-support", "automation", "ai-assistant"]
}
```

### README Structure

Generated READMEs typically include:

```markdown
# formation-name

Brief description

## Features

- Feature 1
- Feature 2
- Feature 3

## Installation

```bash
muxi pull @owner/formation-name
```

## Usage

How to use this formation...

## Requirements

- Requirement 1
- Requirement 2

## Configuration

Configuration details...

## License

MIT
```

---

## Fallback Strategy

### When LLM is Used

1. **README.md doesn't exist** in the uploaded ZIP
2. **OpenAI API key is configured** (`APP_OPENAI_API_KEY`)
3. **API call succeeds**

### When Fallback is Used

1. **README.md exists** → Use existing
2. **API key missing** → Use basic template
3. **API call fails** → Use basic template
4. **Invalid response** → Use basic template
5. **Exception occurs** → Use basic template

### Basic Template (Fallback)

```markdown
# {id}

{description}

## Installation

```bash
muxi pull @owner/{id}
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

---

## Error Handling

### Try-Catch Protection

```php
try {
    $readme = $this->generateReadmeWithLLM($formationData, $tempDir);
} catch (Exception $e) {
    error_log("LLM README generation failed: " . $e->getMessage());
    $readme = $this->generateBasicReadme($formationData);
}
```

### Graceful Degradation

| Issue | Behavior |
|-------|----------|
| API key missing | Falls back to basic template |
| API timeout | Falls back to basic template |
| Invalid JSON response | Falls back to basic template |
| Missing fields in response | Uses partial data + fallback |
| Exception thrown | Logs error, uses basic template |

---

## Configuration

### Required Environment Variable

```bash
# .env or environment
APP_OPENAI_API_KEY=sk-...
```

### OpenAI Settings

- **Model**: `gpt-4o-mini` (cost-effective, fast)
- **Max tokens**: `2000` (enough for comprehensive README)
- **Temperature**: Default (balanced creativity/accuracy)

### Cost Estimate

**Per README generation**:
- Input: ~500 tokens (formation info + prompt)
- Output: ~1000-1500 tokens (comprehensive README)
- Total: ~2000 tokens per generation

**At GPT-4o-mini pricing** (~$0.15 per 1M tokens):
- Cost per README: ~$0.0003 (0.03 cents)
- 1000 formations: ~$0.30

**Very affordable!** 💰

---

## Categories

### Current Implementation

Categories are generated but only logged:

```php
if (isset($data['categories'])) {
    // TODO: Store categories in database
    error_log("Generated categories: " . implode(', ', $data['categories']));
}
```

### Future: Database Storage

**Schema addition needed**:
```sql
CREATE TABLE formation_categories (
  id INTEGER PRIMARY KEY,
  formation_id INTEGER NOT NULL,
  category TEXT NOT NULL,
  FOREIGN KEY (formation_id) REFERENCES formations(id)
);
```

**Usage**:
- Browse by category
- Filter formations
- Show related formations
- Category-based search

---

## Testing

### Manual Test

1. **Create formation without README**:
   ```bash
   cd /tmp/test-formation
   # Only create formation.yaml and agent.yaml
   # Do NOT create README.md
   zip -r formation.zip .
   ```

2. **Upload to registry**:
   ```bash
   curl -X POST https://muxi.registry/api/formations/publish \
     -H "Authorization: Bearer mxr_xxx" \
     -F "file=@formation.zip"
   ```

3. **Check generated README**:
   - README should be comprehensive
   - Categories should be logged
   - Should match formation structure

### Verify Fallback

1. **Temporarily disable API key**:
   ```bash
   unset APP_OPENAI_API_KEY
   ```

2. **Upload formation**:
   - Should use basic template
   - Should not crash
   - Should log fallback usage

---

## Examples

### Example 1: Customer Support Formation

**Input**:
```yaml
id: customer-support
description: AI agent for handling customer inquiries
version: 1.0.0
author: MUXI Team
license: MIT
```

**Generated README** (excerpt):
```markdown
# customer-support

AI-powered customer support agent that intelligently handles customer inquiries, provides instant responses, and escalates complex issues to human agents.

## Features

- 24/7 automated customer inquiry handling
- Natural language understanding for common questions
- Intelligent escalation to human agents
- Multi-channel support (email, chat, social media)
- Knowledge base integration

## Installation

```bash
muxi pull @muxi/customer-support
```

## Usage

1. Configure your communication channels
2. Set escalation rules
3. Deploy the formation
...
```

**Generated Categories**:
- `customer-support`
- `automation`
- `ai-assistant`

---

### Example 2: Data Processing Formation

**Input**:
```yaml
id: data-processor
description: Automated data transformation pipeline
version: 2.1.0
```

**Generated README** (excerpt):
```markdown
# data-processor

Automated data transformation pipeline that processes, validates, and transforms data from various sources.

## Features

- Multi-format data ingestion (CSV, JSON, XML)
- Automated validation and cleaning
- Custom transformation rules
- Error handling and logging
- Scheduled batch processing

## Installation

```bash
muxi pull @owner/data-processor
```

...
```

**Generated Categories**:
- `data-processing`
- `automation`
- `etl`

---

## Benefits

### For Publishers

✅ **No README writing required** - Auto-generated on publish  
✅ **Professional quality** - LLM ensures good structure  
✅ **Consistent format** - All READMEs follow same pattern  
✅ **Time saved** - Focus on building, not documenting

### For Users

✅ **Always have documentation** - Every formation has README  
✅ **Quality content** - Comprehensive and helpful  
✅ **Accurate categories** - Easy to discover relevant formations  
✅ **Clear instructions** - Know how to use formations

### For Platform

✅ **Better discovery** - Categories improve search  
✅ **Higher quality** - Professional documentation standard  
✅ **User engagement** - Better docs = more usage  
✅ **Low cost** - ~$0.0003 per formation

---

## Monitoring

### Logs

```bash
# Success
tail -f /path/to/error.log | grep "Generated categories"

# Failures
tail -f /path/to/error.log | grep "LLM README generation failed"
```

### Metrics to Track

- % of formations using LLM README
- % falling back to basic template
- Average API response time
- Category distribution
- Cost per month

---

## Future Enhancements

### Phase 1 (Current)
- ✅ Basic LLM README generation
- ✅ Category suggestions
- ✅ Fallback to basic template

### Phase 2 (Next)
- [ ] Store categories in database
- [ ] Browse/filter by category
- [ ] Category-based search
- [ ] Show related formations

### Phase 3 (Future)
- [ ] Allow README regeneration
- [ ] User feedback on generated docs
- [ ] Fine-tune prompts based on feedback
- [ ] Multi-language README support
- [ ] Include screenshots/diagrams

---

## Related Documentation

- [PUBLISH-IMPLEMENTATION.md](./PUBLISH-IMPLEMENTATION.md) - Where this is used
- [API-IMPLEMENTATION.md](./API-IMPLEMENTATION.md) - API endpoints

---

## Summary

✅ **LLM README generation implemented**  
✅ **Automatic categorization**  
✅ **Graceful fallback**  
✅ **Cost-effective** (~$0.0003 per README)  
✅ **Production-ready**

**Impact**: Every formation now gets professional documentation automatically! 🎉
