<?php
$formation = tiny::data()->formation;
$latestVersion = tiny::data()->latestVersion;
$stats = tiny::data()->stats;
$versions = tiny::data()->versions;
$homeURL = tiny::getHomeURL('/');

tiny::layout()->default(
    title: '@' . $formation['registry_username'] . '/' . $formation['name'],
    emptyLayout: false
);
?>

<div class="max-w-6xl mx-auto">
    <!-- Formation Header -->
    <div class="bg-white border border-gray-200 rounded-lg p-8 mb-8">
        <div class="flex items-start justify-between mb-6">
            <div class="flex-1">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">
                    @<?= htmlspecialchars($formation['registry_username']) ?>/<span class="text-blue-600"><?= htmlspecialchars($formation['name']) ?></span>
                </h1>

                <p class="text-gray-600 text-lg mb-4">
                    <?= htmlspecialchars($formation['description']) ?>
                </p>

                <div class="flex items-center gap-6 text-sm text-gray-600">
                    <span class="flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
                        </svg>
                        <strong><?= number_format($formation['total_downloads']) ?></strong> pulls
                    </span>
                    <span class="flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                        </svg>
                        <strong><?= number_format($formation['github_stars']) ?></strong> stars
                    </span>
                    <span class="text-gray-500">
                        v<?= htmlspecialchars($formation['latest_version']) ?>
                    </span>
                    <?php if ($formation['license']): ?>
                        <span><?= htmlspecialchars($formation['license']) ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <a href="<?= $homeURL ?>@<?= $formation['registry_username'] ?>"
                   class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 border border-gray-300 rounded">
                    <?php if ($formation['github_avatar']): ?>
                        <img src="<?= htmlspecialchars($formation['github_avatar']) ?>"
                             alt="<?= htmlspecialchars($formation['registry_username']) ?>"
                             class="w-6 h-6 rounded-full" />
                    <?php endif; ?>
                    @<?= htmlspecialchars($formation['registry_username']) ?>
                </a>
            </div>
        </div>

        <!-- Install Command Box -->
        <div class="bg-gray-50 border border-gray-200 rounded-lg p-6">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">🚀 Installation</h3>
            <div class="flex items-center gap-3">
                <code class="flex-1 bg-gray-900 text-gray-100 px-4 py-3 rounded text-sm font-mono">
                    muxi pull @<?= htmlspecialchars($formation['registry_username']) ?>/<?= htmlspecialchars($formation['name']) ?>
                </code>
                <button
                    onclick="navigator.clipboard.writeText('muxi pull @<?= htmlspecialchars($formation['registry_username']) ?>/<?= htmlspecialchars($formation['name']) ?>')"
                    class="px-4 py-3 bg-blue-600 text-white rounded hover:bg-blue-700 transition text-sm font-medium"
                >
                    Copy
                </button>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-8">

            <!-- Components/Stats -->
            <?php if ($stats): ?>
                <div class="bg-white border border-gray-200 rounded-lg p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">📊 Contains</h2>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                        <?php if ($stats['agents_count'] > 0): ?>
                            <div class="text-center p-4 bg-blue-50 rounded">
                                <div class="text-2xl font-bold text-blue-600"><?= $stats['agents_count'] ?></div>
                                <div class="text-sm text-gray-600">Agent<?= $stats['agents_count'] != 1 ? 's' : '' ?></div>
                            </div>
                        <?php endif; ?>

                        <?php if ($stats['mcps_count'] > 0): ?>
                            <div class="text-center p-4 bg-purple-50 rounded">
                                <div class="text-2xl font-bold text-purple-600"><?= $stats['mcps_count'] ?></div>
                                <div class="text-sm text-gray-600">MCP<?= $stats['mcps_count'] != 1 ? 's' : '' ?></div>
                            </div>
                        <?php endif; ?>

                        <?php if ($stats['sops_count'] > 0): ?>
                            <div class="text-center p-4 bg-green-50 rounded">
                                <div class="text-2xl font-bold text-green-600"><?= $stats['sops_count'] ?></div>
                                <div class="text-sm text-gray-600">SOP<?= $stats['sops_count'] != 1 ? 's' : '' ?></div>
                            </div>
                        <?php endif; ?>

                        <?php if ($stats['triggers_count'] > 0): ?>
                            <div class="text-center p-4 bg-orange-50 rounded">
                                <div class="text-2xl font-bold text-orange-600"><?= $stats['triggers_count'] ?></div>
                                <div class="text-sm text-gray-600">Trigger<?= $stats['triggers_count'] != 1 ? 's' : '' ?></div>
                            </div>
                        <?php endif; ?>

                        <?php if ($stats['knowledge_count'] > 0): ?>
                            <div class="text-center p-4 bg-indigo-50 rounded">
                                <div class="text-2xl font-bold text-indigo-600"><?= $stats['knowledge_count'] ?></div>
                                <div class="text-sm text-gray-600">Knowledge Base<?= $stats['knowledge_count'] != 1 ? 's' : '' ?></div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- README -->
            <?php if ($formation['readme_md']): ?>
                <div class="bg-white border border-gray-200 rounded-lg p-8">
                    <div class="prose max-w-none">
                        <?= nl2br(htmlspecialchars($formation['readme_md'])) ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">

            <!-- Links -->
            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <h3 class="font-semibold text-gray-900 mb-4">Links</h3>
                <div class="space-y-3">
                    <a href="https://github.com/<?= htmlspecialchars($formation['github_repo']) ?>"
                       target="_blank"
                       class="flex items-center gap-2 text-sm text-blue-600 hover:text-blue-700">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                        </svg>
                        View on GitHub
                    </a>
                    <a href="https://github.com/<?= htmlspecialchars($formation['github_repo']) ?>/issues"
                       target="_blank"
                       class="flex items-center gap-2 text-sm text-blue-600 hover:text-blue-700">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                        Report Issue
                    </a>
                </div>
            </div>

            <!-- Version History -->
            <?php if (!empty($versions)): ?>
                <div class="bg-white border border-gray-200 rounded-lg p-6">
                    <h3 class="font-semibold text-gray-900 mb-4">Versions</h3>
                    <div class="space-y-2">
                        <?php foreach (array_slice($versions, 0, 5) as $version): ?>
                            <div class="text-sm">
                                <div class="flex items-center justify-between">
                                    <span class="font-mono text-blue-600">v<?= htmlspecialchars($version['version']) ?></span>
                                    <span class="text-xs text-gray-500">
                                        <?= date('M j, Y', strtotime($version['published_at'])) ?>
                                    </span>
                                </div>
                                <?php if ($version['release_notes']): ?>
                                    <p class="text-xs text-gray-600 mt-1">
                                        <?= htmlspecialchars(substr($version['release_notes'], 0, 60)) ?><?= strlen($version['release_notes']) > 60 ? '...' : '' ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>

                        <?php if (count($versions) > 5): ?>
                            <a href="https://github.com/<?= htmlspecialchars($formation['github_repo']) ?>/releases"
                               target="_blank"
                               class="text-xs text-blue-600 hover:text-blue-700">
                                View all <?= count($versions) ?> versions →
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php tiny::layout()->default('/'); ?>
