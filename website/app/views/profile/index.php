<?php
tiny::layout()->default(
    title: '@' . htmlspecialchars(tiny::data()->profile['registry_username']),
    emptyLayout: false
);
tiny::components()->require('FormationCard');
?>

<div class="max-w-6xl mx-auto">
    <!-- User Header -->
    <div class="bg-white border border-gray-200 rounded-lg p-8 mb-8">
        <div class="flex items-start gap-6">
            <?php if (tiny::data()->profile['github_avatar']): ?>
                <img
                    src="<?php echo htmlspecialchars(tiny::data()->profile['github_avatar']); ?>"
                    alt="<?php echo htmlspecialchars(tiny::data()->profile['registry_username']); ?>"
                    class="w-24 h-24 rounded-full"
                />
            <?php else: ?>
                <div class="w-24 h-24 rounded-full bg-gray-200 flex items-center justify-center text-4xl">
                    <?php echo strtoupper(substr(tiny::data()->profile['registry_username'], 0, 1)); ?>
                </div>
            <?php endif; ?>

            <div class="flex-1">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">
                    @<?php echo htmlspecialchars(tiny::data()->profile['registry_username']); ?>
                    <?php if (tiny::data()->profile['is_verified']): ?>
                        <span class="inline-flex items-center px-2 py-1 text-sm bg-blue-100 text-blue-800 rounded">
                            ✓ Verified
                        </span>
                    <?php endif; ?>
                    <?php if (tiny::data()->profile['github_type'] === 'Organization'): ?>
                        <span class="inline-flex items-center px-2 py-1 text-sm bg-gray-100 text-gray-700 rounded">
                            Organization
                        </span>
                    <?php endif; ?>
                </h1>

                <?php if (tiny::data()->profile['bio']): ?>
                    <p class="text-gray-600 mb-4"><?php echo htmlspecialchars(tiny::data()->profile['bio']); ?></p>
                <?php endif; ?>

                <div class="flex gap-6 text-sm text-gray-600">
                    <a href="https://github.com/<?php echo htmlspecialchars(tiny::data()->profile['github_username']); ?>"
                       target="_blank"
                       class="hover:text-blue-600 flex items-center gap-1">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                        </svg>
                        <?php echo htmlspecialchars(tiny::data()->profile['github_username']); ?>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-3 gap-4 mb-8">
        <div class="bg-white border border-gray-200 rounded-lg p-6 text-center">
            <div class="text-3xl font-bold text-blue-600 mb-1">
                <?php echo number_format(tiny::data()->stats['formations_count']); ?>
            </div>
            <div class="text-sm text-gray-600">
                Formation<?php echo tiny::data()->stats['formations_count'] != 1 ? 's' : '' ?>
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-lg p-6 text-center">
            <div class="text-3xl font-bold text-blue-600 mb-1">
                <?php echo number_format(tiny::data()->stats['total_downloads']); ?>
            </div>
            <div class="text-sm text-gray-600">
                Total Pulls
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-lg p-6 text-center">
            <div class="text-3xl font-bold text-blue-600 mb-1">
                <?php echo number_format(tiny::data()->stats['total_stars']); ?>
            </div>
            <div class="text-sm text-gray-600">
                GitHub Stars
            </div>
        </div>
    </div>

    <!-- Formations -->
    <div class="mb-8">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-gray-900">Formations</h2>
        </div>

        <?php if (empty(tiny::data()->formations)): ?>
            <div class="bg-white border border-gray-200 rounded-lg p-12 text-center">
                <div class="text-gray-400 text-5xl mb-4">📦</div>
                <p class="text-gray-600">No formations published yet</p>
            </div>
        <?php else: ?>
            <!-- Sort Controls -->
            <div class="bg-white border border-gray-200 rounded-lg p-4 mb-6">
                <div class="flex items-center gap-4">
                    <span class="text-sm font-semibold text-gray-700">Sort by:</span>
                    
                    <a href="<?php tiny::homeURL('/@' . tiny::data()->profile['registry_username'] . '?sort=trending'); ?>" 
                       class="px-4 py-2 text-sm rounded <?php echo tiny::data()->sort === 'trending' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?>">
                        🔥 Trending
                    </a>
                    
                    <a href="<?php tiny::homeURL('/@' . tiny::data()->profile['registry_username'] . '?sort=recent'); ?>" 
                       class="px-4 py-2 text-sm rounded <?php echo tiny::data()->sort === 'recent' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?>">
                        Recently Published
                    </a>
                    
                    <a href="<?php tiny::homeURL('/@' . tiny::data()->profile['registry_username'] . '?sort=downloads'); ?>" 
                       class="px-4 py-2 text-sm rounded <?php echo tiny::data()->sort === 'downloads' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?>">
                        Most Downloads
                    </a>
                    
                    <a href="<?php tiny::homeURL('/@' . tiny::data()->profile['registry_username'] . '?sort=name'); ?>" 
                       class="px-4 py-2 text-sm rounded <?php echo tiny::data()->sort === 'name' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?>">
                        Name (A-Z)
                    </a>
                </div>
            </div>


            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php foreach (tiny::data()->formations as $formation): ?>
                    <?php tiny::components()->FormationCard(formation: $formation, showStats: true); ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php tiny::layout()->default('/'); ?>
