<?php
tiny::layout()->default(title: 'MUXI Registry - Docker Hub for AI Formations', emptyLayout: false);
tiny::components()->require('FormationCard');
?>

<!-- Hero Section -->
<div class="bg-gradient-br from-blue-50 to-indigo-100 -mt-6 -mx-6 px-6 py-16 mb-12">
    <div class="max-w-4xl mx-auto text-center">

        <!-- Stats -->
        <div class="flex justify-center gap-8 text-sm text-gray-600">
            <span class="font-semibold">
                <span class="text-2xl text-blue-600"><?php echo number_format(tiny::data()->stats['total_formations']); ?></span> formations
            </span>
            <span class="font-semibold">
                <span class="text-2xl text-blue-600"><?php echo number_format(tiny::data()->stats['total_users']); ?></span> users
            </span>
            <span class="font-semibold">
                <span class="text-2xl text-blue-600"><?php echo number_format(tiny::data()->stats['total_downloads']); ?></span> total pulls
            </span>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="max-w-7xl mx-auto">

    <!-- Recently Published -->
    <section class="mb-16">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                <span>🔥</span> Recently Published
            </h2>
            <a href="<?php tiny::homeURL('/browse?sort=recent'); ?>" class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                View all →
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <?php foreach (tiny::data()->formations['recent'] as $formation): ?>
                <?php tiny::components()->FormationCard(formation: $formation, showStats: true); ?>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Most Popular -->
    <section class="mb-16">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                <span>⭐</span> Most Popular
            </h2>
            <a href="<?php tiny::homeURL('/browse?sort=downloads'); ?>" class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                View all →
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <?php foreach (tiny::data()->formations['popular'] as $formation): ?>
                <?php tiny::components()->FormationCard(formation: $formation, showStats: true); ?>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Active Users -->
    <section class="mb-16">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                <span>👥</span> Active Publishers
            </h2>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-8 gap-4">
            <?php foreach (tiny::data()->activeUsers as $user): ?>
                <a href="<?php tiny::homeURL('/@' . $user['registry_username']); ?>" class="group">
                    <div class="bg-white border border-gray-200 rounded-lg p-4 hover:border-blue-400 hover:shadow-md transition text-center">
                        <?php if ($user['github_avatar']): ?>
                            <img
                                src="<?php echo htmlspecialchars($user['github_avatar']); ?>"
                                alt="<?php echo htmlspecialchars($user['registry_username']); ?>"
                                class="w-16 h-16 rounded-full mx-auto mb-2" />
                        <?php else: ?>
                            <div class="w-16 h-16 rounded-full bg-gray-200 mx-auto mb-2 flex items-center justify-center text-2xl">
                                <?php echo strtoupper(substr($user['registry_username'], 0, 1)); ?>
                            </div>
                        <?php endif; ?>

                        <div class="text-sm font-semibold text-gray-900 group-hover:text-blue-600 truncate">
                            @<?php echo htmlspecialchars($user['registry_username']); ?>
                        </div>

                        <div class="text-xs text-gray-500 mt-1">
                            <?php echo $user['formations_count'] ?> formation<?php echo $user['formations_count'] != 1 ? 's' : '' ?>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Getting Started -->
    <section class="bg-gray-50 rounded-lg p-8 mb-16">
        <h2 class="text-2xl font-bold text-gray-900 mb-4">🎯 Get Started</h2>

        <div class="grid md:grid-cols-2 gap-6">
            <div>
                <h3 class="font-semibold text-gray-900 mb-2">Pull a Formation</h3>
                <pre class="bg-gray-900 text-gray-100 p-4 rounded text-sm overflow-x-auto"><code>muxi pull @muxi/customer-support</code></pre>
                <p class="text-sm text-gray-600 mt-2">
                    Install any formation with a single command. No setup required.
                </p>
            </div>

            <div>
                <h3 class="font-semibold text-gray-900 mb-2">Publish Your Formation</h3>
                <pre class="bg-gray-900 text-gray-100 p-4 rounded text-sm overflow-x-auto"><code>muxi login
muxi push</code></pre>
                <p class="text-sm text-gray-600 mt-2">
                    Share your formations with the community in seconds.
                </p>
            </div>
        </div>
    </section>

</div>

<?php tiny::layout()->default('/'); ?>
