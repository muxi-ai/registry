# Downloads Chart Implementation

Docker-style download chart for formation pages.

---

## SQL Queries (Ignoring Version)

### Get Chart Data (Last 30 Days)

```php
// app/models/formation.php

public function getDownloadChart($formationId, $days = 30) {
    return tiny::db()->query(
        "SELECT 
            day,
            SUM(download_count) as downloads  -- Sums all versions for the day
        FROM downloads
        WHERE formation_id = ?
          AND day >= DATE('now', '-' || ? || ' days')
        GROUP BY day
        ORDER BY day ASC",
        [$formationId, $days]
    )->fetchAll();
}
```

### Get Trending Formations (Last 7 Days)

```php
// app/models/formation.php

public function trending($limit = 10) {
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
            SUM(d.download_count) as downloads_7d  -- All versions combined
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
```

---

## Formation Page Chart

### Controller

```php
<?php
// app/controllers/_formation.php

class Formation extends TinyController {
    private $username;
    private $formationName;
    
    public function __construct() {
        $parts = explode('/', substr(tiny::router()->controller, 1));
        $this->username = $parts[0];
        $this->formationName = $parts[1] ?? null;
    }
    
    public function get($request, $response) {
        // Load formation
        $FormationModel = tiny::model('formation');
        $formation = $FormationModel->findByUsername($this->username, $this->formationName);
        
        if (!$formation) {
            return $response->status(404)->render('errors/404');
        }
        
        // Get download chart data (last 30 days)
        $chartData = $FormationModel->getDownloadChart($formation['id'], 30);
        
        tiny::data()->formation = $formation;
        tiny::data()->chartData = $chartData;
        
        $response->render('formation/index');
    }
}
```

### View (Simple SVG Chart)

```php
<?php
// app/views/formation/index.php

tiny::layout()->default(
    title: '@' . $formation['registry_username'] . '/' . $formation['name'],
    emptyLayout: false
);

$formation = tiny::data()->formation;
$chartData = tiny::data()->chartData;
?>

<div class="container mx-auto px-4 py-8">
    <!-- Formation Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold">
            @<?= $formation['registry_username'] ?>/<?= $formation['name'] ?>
        </h1>
        <p class="text-gray-600 mt-2"><?= $formation['description'] ?></p>
    </div>
    
    <!-- Stats -->
    <div class="flex gap-6 mb-8">
        <div>
            <span class="text-gray-600">Downloads:</span>
            <strong><?= number_format($formation['total_downloads']) ?></strong>
        </div>
        <div>
            <span class="text-gray-600">Stars:</span>
            <strong><?= number_format($formation['github_stars']) ?></strong>
        </div>
        <div>
            <span class="text-gray-600">Version:</span>
            <strong>v<?= $formation['latest_version'] ?></strong>
        </div>
    </div>
    
    <!-- Download Chart (Docker Style) -->
    <div class="bg-white border rounded-lg p-6 mb-8">
        <h2 class="text-xl font-semibold mb-4">Downloads (Last 30 Days)</h2>
        
        <?php if (empty($chartData)): ?>
            <p class="text-gray-500">No download data available yet.</p>
        <?php else: ?>
            <?php
            // Prepare chart data
            $maxDownloads = max(array_column($chartData, 'downloads'));
            $chartHeight = 80; // pixels
            $barWidth = 100 / count($chartData); // percentage
            ?>
            
            <!-- Simple Bar Chart -->
            <div class="relative" style="height: <?= $chartHeight ?>px">
                <div class="flex items-end h-full gap-px">
                    <?php foreach ($chartData as $day): ?>
                        <?php 
                        $height = $maxDownloads > 0 
                            ? ($day['downloads'] / $maxDownloads) * 100 
                            : 0;
                        ?>
                        <div 
                            class="bg-blue-500 hover:bg-blue-600 transition-colors cursor-pointer relative group"
                            style="width: <?= $barWidth ?>%; height: <?= $height ?>%"
                            title="<?= date('M j', strtotime($day['day'])) ?>: <?= $day['downloads'] ?> downloads"
                        >
                            <!-- Tooltip -->
                            <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none">
                                <?= date('M j', strtotime($day['day'])) ?>: <?= $day['downloads'] ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Date Labels -->
            <div class="flex justify-between mt-2 text-xs text-gray-500">
                <span><?= date('M j', strtotime($chartData[0]['day'])) ?></span>
                <span><?= date('M j', strtotime($chartData[count($chartData) - 1]['day'])) ?></span>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- README -->
    <div class="prose max-w-none">
        <?php echo tiny::markdown()->transform($formation['readme_md']); ?>
    </div>
</div>

<?php tiny::layout()->default('/'); ?>
```

### Alternative: Chart.js (More Features)

```php
<!-- In <head> -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>

<!-- In view -->
<div class="bg-white border rounded-lg p-6 mb-8">
    <h2 class="text-xl font-semibold mb-4">Downloads (Last 30 Days)</h2>
    <canvas id="downloadsChart" height="80"></canvas>
</div>

<script>
const chartData = <?= json_encode($chartData) ?>;
const ctx = document.getElementById('downloadsChart').getContext('2d');

new Chart(ctx, {
    type: 'bar',
    data: {
        labels: chartData.map(d => {
            const date = new Date(d.day);
            return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
        }),
        datasets: [{
            label: 'Downloads',
            data: chartData.map(d => d.downloads),
            backgroundColor: 'rgba(59, 130, 246, 0.5)',
            borderColor: 'rgba(59, 130, 246, 1)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return context.parsed.y + ' downloads';
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    precision: 0
                }
            }
        }
    }
});
</script>
```

---

## Homepage Trending Box

```php
<?php
// app/controllers/home.php

public function get($request, $response) {
    $Formation = tiny::model('formation');
    
    tiny::data()->trending = $Formation->trending(8);  // Top 8 trending
    tiny::data()->recent = $Formation->recent(8);
    
    $response->render('home');
}
```

```php
<?php
// app/views/home.php
?>

<!-- Trending This Week Section -->
<section class="mb-12">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold">🔥 Trending This Week</h2>
        <a href="/browse?sort=trending" class="text-blue-600 hover:underline">
            View all →
        </a>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <?php foreach (tiny::data()->trending as $formation): ?>
            <a href="/@<?= $formation['registry_username'] ?>/<?= $formation['name'] ?>" 
               class="block border rounded-lg p-4 hover:shadow-lg transition-shadow">
                
                <h3 class="font-semibold mb-2">
                    @<?= $formation['registry_username'] ?>/<?= $formation['name'] ?>
                </h3>
                
                <p class="text-sm text-gray-600 mb-3 line-clamp-2">
                    <?= $formation['description'] ?>
                </p>
                
                <div class="flex justify-between text-xs text-gray-500">
                    <span>⬇ <?= number_format($formation['total_downloads']) ?></span>
                    <span>⭐ <?= number_format($formation['github_stars']) ?></span>
                    <span class="text-green-600 font-semibold">
                        +<?= number_format($formation['downloads_7d']) ?> this week
                    </span>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
</section>
```

---

## Browse Page with Sort

```php
<?php
// app/controllers/browse.php

class Browse extends TinyController {
    public function get($request, $response) {
        $sort = $request->query('sort', 'trending'); // trending, popular, recent
        $Formation = tiny::model('formation');
        
        switch ($sort) {
            case 'popular':
                $formations = $Formation->popular(50);  // All-time downloads
                break;
            case 'recent':
                $formations = $Formation->recent(50);
                break;
            case 'trending':
            default:
                $formations = $Formation->trending(50);  // Last 7 days
                break;
        }
        
        tiny::data()->formations = $formations;
        tiny::data()->sort = $sort;
        
        $response->render('browse');
    }
}
```

```php
<?php
// app/models/formation.php

public function popular($limit = 50) {
    return tiny::db()->query(
        "SELECT 
            f.*,
            u.registry_username,
            u.github_avatar
        FROM formations f
        JOIN users u ON f.user_id = u.id
        WHERE f.total_downloads > 0
        ORDER BY f.total_downloads DESC, f.github_stars DESC
        LIMIT ?",
        [$limit]
    )->fetchAll();
}
```

---

## Test Queries

After loading test data, try these queries:

```sql
-- See trending formations
SELECT 
    f.name,
    SUM(d.download_count) as downloads_7d
FROM formations f
LEFT JOIN downloads d ON f.id = d.formation_id 
    AND d.day >= DATE('now', '-7 days')
GROUP BY f.id
ORDER BY downloads_7d DESC
LIMIT 10;

-- Expected results:
-- document-qa: ~400+ (VIRAL - last 10 days only)
-- customer-support: ~350+ (trending with spike)
-- data-processor: ~250+ (new but rising fast)
-- sentiment-analyzer: ~200+ (steady)
-- code-reviewer: ~200+ (popular)

-- See chart data for a formation
SELECT day, download_count
FROM downloads
WHERE formation_id = (SELECT id FROM formations WHERE name = 'customer-support')
ORDER BY day DESC
LIMIT 30;
```

---

**Last Updated:** 2025-10-28
