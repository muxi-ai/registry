# Downloads Tracking System

## Overview

Track downloads both as:
1. **Total downloads** (formations.total_downloads) - All-time counter
2. **Daily downloads** (formation_downloads table) - Time-series data

This enables:
- ✅ Trending calculations (last 7 days, 30 days)
- ✅ Download charts/graphs
- ✅ Identify popularity spikes
- ✅ Compare growth rates

---

## Database Schema

### New Table: `downloads`

```sql
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
```

### Existing Table (No Changes)

```sql
-- formations table already has:
total_downloads INTEGER DEFAULT 0
```

---

## Recording Downloads

### When User Pulls Formation

```bash
POST /api/formations/@:user/:name/:version/download
```

**Implementation:**

```php
class FormationModel {
    public function recordDownload($formationId, $version) {
        $today = date('Y-m-d');
        
        // 1. Increment total downloads (all-time counter)
        tiny::db()->query(
            "UPDATE formations 
             SET total_downloads = total_downloads + 1 
             WHERE id = ?",
            [$formationId]
        );
        
        // 2. Increment daily downloads (time-series data)
        tiny::db()->query(
            "INSERT INTO downloads 
                (formation_id, version, day, download_count)
             VALUES (?, ?, ?, 1)
             ON CONFLICT(formation_id, version, day) 
             DO UPDATE SET download_count = download_count + 1",
            [$formationId, $version, $today]
        );
    }
}
```

**Result:**
- Every download increments both counters
- Daily record created/updated automatically
- Tracks which version was downloaded

---

## Trending Calculations

### "Trending" = Downloads in Last 7 Days

```sql
-- Get formations trending in last 7 days
SELECT 
    f.id,
    f.name,
    u.registry_username,
    f.total_downloads,
    SUM(d.download_count) as downloads_7d,
    f.github_stars
FROM formations f
JOIN users u ON f.user_id = u.id
LEFT JOIN downloads d 
    ON f.id = d.formation_id 
    AND d.day >= DATE('now', '-7 days')
GROUP BY f.id
ORDER BY downloads_7d DESC
LIMIT 20;
```

### "Rising" = Fastest Growing (7d vs Previous 7d)

```sql
-- Compare this week vs last week
WITH current_week AS (
    SELECT 
        formation_id,
        SUM(download_count) as downloads_current
    FROM downloads
    WHERE day >= DATE('now', '-7 days')
    GROUP BY formation_id
),
previous_week AS (
    SELECT 
        formation_id,
        SUM(download_count) as downloads_previous
    FROM downloads
    WHERE day >= DATE('now', '-14 days')
      AND day < DATE('now', '-7 days')
    GROUP BY formation_id
)
SELECT 
    f.id,
    f.name,
    u.registry_username,
    cw.downloads_current,
    pw.downloads_previous,
    -- Growth rate
    ROUND(
        ((cw.downloads_current - COALESCE(pw.downloads_previous, 0)) * 100.0) 
        / NULLIF(pw.downloads_previous, 0), 
        1
    ) as growth_percent
FROM formations f
JOIN users u ON f.user_id = u.id
LEFT JOIN current_week cw ON f.id = cw.formation_id
LEFT JOIN previous_week pw ON f.id = pw.formation_id
WHERE cw.downloads_current > 0
ORDER BY growth_percent DESC
LIMIT 20;
```

### "Popular This Month"

```sql
SELECT 
    f.id,
    f.name,
    u.registry_username,
    SUM(d.download_count) as downloads_30d
FROM formations f
JOIN users u ON f.user_id = u.id
LEFT JOIN downloads d 
    ON f.id = d.formation_id 
    AND d.day >= DATE('now', '-30 days')
GROUP BY f.id
ORDER BY downloads_30d DESC
LIMIT 20;
```

---

## Download Charts

### Daily Downloads for Formation

```sql
-- Get last 30 days of download data for charting
SELECT 
    day,
    SUM(download_count) as daily_downloads
FROM downloads
WHERE formation_id = ?
  AND day >= DATE('now', '-30 days')
GROUP BY day
ORDER BY day ASC;
```

**Result:**
```json
[
  { "date": "2025-10-01", "downloads": 12 },
  { "date": "2025-10-02", "downloads": 15 },
  { "date": "2025-10-03", "downloads": 8 },
  // ... etc
]
```

---

## Version Popularity

### Most Downloaded Versions

```sql
-- Which versions are most popular?
SELECT 
    version,
    SUM(download_count) as total_downloads,
    MAX(day) as last_downloaded
FROM downloads
WHERE formation_id = ?
GROUP BY version
ORDER BY total_downloads DESC;
```

**Example:**
```
v1.2.0  →  1,245 downloads (last: 2025-10-28)
v1.1.0  →    823 downloads (last: 2025-10-25)
v1.0.0  →    156 downloads (last: 2025-09-15)
```

---

## Model Methods

### Add to `app/models/formation.php`

```php
class FormationModel extends TinyModel {
    
    /**
     * Record a download (updates both counters)
     */
    public function recordDownload($formationId, $version) {
        $today = date('Y-m-d');
        
        // Update total downloads
        tiny::db()->query(
            "UPDATE formations 
             SET total_downloads = total_downloads + 1 
             WHERE id = ?",
            [$formationId]
        );
        
        // Update daily downloads
        tiny::db()->query(
            "INSERT INTO downloads 
                (formation_id, version, day, download_count)
             VALUES (?, ?, ?, 1)
             ON CONFLICT(formation_id, version, day) 
             DO UPDATE SET download_count = download_count + 1",
            [$formationId, $version, $today]
        );
    }
    
    /**
     * Get trending formations (last 7 days)
     */
    public function trending($limit = 20) {
        return tiny::db()->query(
            "SELECT 
                f.id,
                f.name,
                f.description,
                f.latest_version,
                f.total_downloads,
                f.github_stars,
                u.registry_username,
                u.github_avatar,
                SUM(d.download_count) as downloads_7d
            FROM formations f
            JOIN users u ON f.user_id = u.id
            LEFT JOIN downloads d 
                ON f.id = d.formation_id 
                AND d.day >= DATE('now', '-7 days')
            GROUP BY f.id
            HAVING downloads_7d > 0
            ORDER BY downloads_7d DESC, f.github_stars DESC
            LIMIT ?",
            [$limit]
        )->fetchAll();
    }
    
    /**
     * Get popular this month
     */
    public function popularThisMonth($limit = 20) {
        return tiny::db()->query(
            "SELECT 
                f.id,
                f.name,
                f.description,
                f.latest_version,
                f.total_downloads,
                f.github_stars,
                u.registry_username,
                u.github_avatar,
                SUM(d.download_count) as downloads_30d
            FROM formations f
            JOIN users u ON f.user_id = u.id
            LEFT JOIN downloads d 
                ON f.id = d.formation_id 
                AND d.day >= DATE('now', '-30 days')
            GROUP BY f.id
            HAVING downloads_30d > 0
            ORDER BY downloads_30d DESC, f.github_stars DESC
            LIMIT ?",
            [$limit]
        )->fetchAll();
    }
    
    /**
     * Get rising formations (fastest growing)
     */
    public function rising($limit = 20) {
        return tiny::db()->query(
            "WITH current_week AS (
                SELECT 
                    formation_id,
                    SUM(download_count) as downloads_current
                FROM downloads
                WHERE day >= DATE('now', '-7 days')
                GROUP BY formation_id
            ),
            previous_week AS (
                SELECT 
                    formation_id,
                    SUM(download_count) as downloads_previous
                FROM downloads
                WHERE day >= DATE('now', '-14 days')
                  AND day < DATE('now', '-7 days')
                GROUP BY formation_id
            )
            SELECT 
                f.id,
                f.name,
                f.description,
                f.latest_version,
                f.total_downloads,
                f.github_stars,
                u.registry_username,
                u.github_avatar,
                cw.downloads_current,
                COALESCE(pw.downloads_previous, 0) as downloads_previous,
                ROUND(
                    ((cw.downloads_current - COALESCE(pw.downloads_previous, 0)) * 100.0) 
                    / NULLIF(pw.downloads_previous, 0), 
                    1
                ) as growth_percent
            FROM formations f
            JOIN users u ON f.user_id = u.id
            LEFT JOIN current_week cw ON f.id = cw.formation_id
            LEFT JOIN previous_week pw ON f.id = pw.formation_id
            WHERE cw.downloads_current > 0
            ORDER BY growth_percent DESC
            LIMIT ?",
            [$limit]
        )->fetchAll();
    }
    
    /**
     * Get download chart data (last 30 days)
     */
    public function getDownloadChart($formationId, $days = 30) {
        return tiny::db()->query(
            "SELECT 
                day as date,
                SUM(download_count) as downloads
            FROM downloads
            WHERE formation_id = ?
              AND day >= DATE('now', '-' || ? || ' days')
            GROUP BY day
            ORDER BY day ASC",
            [$formationId, $days]
        )->fetchAll();
    }
    
    /**
     * Get version popularity
     */
    public function getVersionStats($formationId) {
        return tiny::db()->query(
            "SELECT 
                version,
                SUM(download_count) as total_downloads,
                MAX(day) as last_downloaded
            FROM downloads
            WHERE formation_id = ?
            GROUP BY version
            ORDER BY total_downloads DESC",
            [$formationId]
        )->fetchAll();
    }
}
```

---

## Homepage Implementation

### Update `app/controllers/home.php`

```php
public function get($request, $response) {
    $Formation = tiny::model('formation');
    
    tiny::data()->stats = [
        'formations' => Formation::count(),
        'users' => User::count(),
        'total_pulls' => Formation::totalDownloads()
    ];
    
    // Use new trending methods
    tiny::data()->trending = $Formation->trending(8);
    tiny::data()->recent = $Formation->recent(8);
    tiny::data()->rising = $Formation->rising(4);  // NEW!
    
    $response->render('home');
}
```

### Update `app/views/home.php`

```php
<!-- Trending Section -->
<section>
    <h2>Trending This Week 🔥</h2>
    <div class="formations-grid">
        <?php foreach (tiny::data()->trending as $formation): ?>
            <?php tiny::components()->FormationCard([
                'formation' => $formation,
                'badge' => $formation->downloads_7d . ' pulls this week'
            ]); ?>
        <?php endforeach; ?>
    </div>
</section>

<!-- Rising Section (NEW!) -->
<section>
    <h2>Rising Stars ⭐</h2>
    <div class="formations-grid">
        <?php foreach (tiny::data()->rising as $formation): ?>
            <?php tiny::components()->FormationCard([
                'formation' => $formation,
                'badge' => '+' . $formation->growth_percent . '% growth'
            ]); ?>
        <?php endforeach; ?>
    </div>
</section>
```

---

## Migration SQL

### Add to Database Schema

```sql
-- Add new table
CREATE TABLE downloads (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  formation_id INTEGER NOT NULL,
  version TEXT NOT NULL,
  day DATE NOT NULL,
  download_count INTEGER DEFAULT 0,
  FOREIGN KEY (formation_id) REFERENCES formations(id) ON DELETE CASCADE,
  UNIQUE(formation_id, version, day)
);

CREATE INDEX idx_downloads_formation ON downloads(formation_id, day DESC);
CREATE INDEX idx_downloads_day ON downloads(day DESC);
```

---

## Benefits

### What This Enables

1. **Accurate Trending**
   - Based on recent activity, not all-time totals
   - Detect popularity spikes
   - Identify seasonal patterns

2. **Better Discovery**
   - "Trending this week" section
   - "Rising stars" section
   - "Popular this month" section

3. **Analytics**
   - Download charts on formation pages
   - Growth rate calculations
   - Version adoption tracking

4. **User Insights**
   - "Your formation gained 150 downloads this week!"
   - "Version 2.0 is 3x more popular than 1.x"
   - Weekly/monthly email summaries

5. **API Data**
   - Rich analytics for CLI/web
   - Historical data for developers
   - Competitive intelligence

---

**Last Updated:** 2025-10-28
