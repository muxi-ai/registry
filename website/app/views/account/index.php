<?php
tiny::layout()->default(title: 'My Account', emptyLayout: false);
tiny::components()->require('FormationCard');
?>

<div class="max-w-6xl mx-auto">
    <!-- User Header -->
    <div class="bg-white border border-gray-200 rounded-lg p-8 mb-8">
        <div class="flex items-start gap-6">
            <?php if (tiny::user()->github_avatar): ?>
                <img
                    src="<?php echo htmlspecialchars(tiny::user()->github_avatar); ?>"
                    alt="<?php echo htmlspecialchars(tiny::user()->registry_username); ?>"
                    class="w-24 h-24 rounded-full"
                />
            <?php else: ?>
                <div class="w-24 h-24 rounded-full bg-gray-200 flex items-center justify-center text-4xl">
                    <?php echo strtoupper(substr(tiny::user()->registry_username, 0, 1)); ?>
                </div>
            <?php endif; ?>

            <div class="flex-1">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">
                    @<?php echo htmlspecialchars(tiny::user()->registry_username); ?>
                    <?php if (tiny::user()->is_verified): ?>
                        <span class="inline-flex items-center px-2 py-1 text-sm bg-blue-100 text-blue-800 rounded">
                            ✓ Verified
                        </span>
                    <?php endif; ?>
                </h1>

                <?php if (tiny::user()->bio): ?>
                    <p class="text-gray-600 mb-4"><?php echo htmlspecialchars(tiny::user()->bio); ?></p>
                <?php endif; ?>

                <div class="flex gap-6 text-sm text-gray-600">
                    <a href="https://github.com/<?php echo htmlspecialchars(tiny::user()->github_username); ?>"
                       target="_blank"
                       class="hover:text-blue-600 flex items-center gap-1">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                        </svg>
                        GitHub Profile
                    </a>

                    <a href="https://github.com/apps/muxi-registry"
                       target="_blank"
                       class="hover:text-blue-600 flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        Manage GitHub App
                    </a>
                </div>
            </div>

            <div class="text-right">
                <a href="<?php tiny::homeURL('/@' . tiny::user()->registry_username); ?>"
                   class="text-sm text-blue-600 hover:text-blue-700">
                    View Public Profile →
                </a>
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
                Total Stars
            </div>
        </div>
    </div>

    <!-- Formations -->
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-4">Your Formations</h2>

        <?php if (empty(tiny::data()->formations)): ?>
            <div class="bg-white border border-gray-200 rounded-lg p-12 text-center">
                <div class="text-gray-400 text-5xl mb-4">📦</div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">No formations yet</h3>
                <p class="text-gray-600 mb-6">
                    Get started by publishing your first formation
                </p>
                <pre class="bg-gray-900 text-gray-100 p-4 rounded text-sm inline-block"><code>muxi push</code></pre>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php foreach (tiny::data()->formations as $formation): ?>
                    <?php tiny::components()->FormationCard(formation: $formation, showStats: true); ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php tiny::layout()->default('/'); ?>
