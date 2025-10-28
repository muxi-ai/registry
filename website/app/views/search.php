<?php
$pageTitle = !empty(tiny::data()->query)
    ? 'Search: ' . htmlspecialchars(tiny::data()->query) . ' - MUXI Registry'
    : 'Search Formations - MUXI Registry';

tiny::layout()->default(title: $pageTitle, emptyLayout: false);
tiny::components()->require('FormationCard');
?>

<div class="max-w-7xl mx-auto">

    <!-- Results -->
    <?php if (!empty(tiny::data()->query)): ?>

        <!-- Results Count -->
        <div class="mb-6">
            <p class="text-gray-600">
                <?php if (tiny::data()->resultCount === 0): ?>
                    No results found for <strong><?php echo htmlspecialchars(tiny::data()->correctedQuery ?? tiny::data()->query); ?></strong>
                <?php elseif (tiny::data()->resultCount === 1): ?>
                    Found <strong>1</strong> result for <strong><?php echo htmlspecialchars(tiny::data()->correctedQuery ?? tiny::data()->query); ?></strong>
                <?php else: ?>
                    Found <strong><?php echo number_format(tiny::data()->resultCount); ?></strong> results for <strong><?php echo htmlspecialchars(tiny::data()->correctedQuery ?? tiny::data()->query); ?></strong>
                <?php endif; ?>
            </p>
            <?php if (!empty(tiny::data()->correctedQuery)): ?>
                <p class="text-sm text-gray-600">
                    Search instead for
                    <a href="<?php tiny::homeURL('/search?q=' . urlencode(tiny::data()->originalQuery)); ?>"
                       class="text-blue-600 hover:underline">
                        <?php echo htmlspecialchars(tiny::data()->originalQuery); ?>
                    </a>
                </p>
        <?php endif; ?>
        </div>

        <!-- Results Grid -->
        <?php if (tiny::data()->resultCount === 0): ?>
            <div class="bg-white border border-gray-200 rounded-lg p-12 text-center">
                <div class="text-gray-400 text-5xl mb-4">🔍</div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">No formations found</h3>
                <p class="text-gray-600 mb-4">
                    Try different keywords or browse all formations
                </p>
                <a href="<?php tiny::homeURL('/browse'); ?>"
                   class="inline-block px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                    Browse All Formations
                </a>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                <?php foreach (tiny::data()->formations as $formation): ?>
                    <?php tiny::components()->FormationCard(formation: $formation, showStats: true); ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    <?php else: ?>

        <!-- Empty State (No Search Performed) -->
        <div class="bg-white border border-gray-200 rounded-lg p-12 text-center">
            <div class="text-gray-400 text-5xl mb-4">🔍</div>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">Search formations</h3>
            <p class="text-gray-600">
                Enter keywords to find formations by name, description, or content
            </p>
        </div>

    <?php endif; ?>

</div>

<?php tiny::layout()->default('/'); ?>
