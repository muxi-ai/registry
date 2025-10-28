<?php
tiny::layout()->default(title: 'Browse Formations - MUXI Registry', emptyLayout: false);
tiny::components()->require('FormationCard');
?>

<div class="max-w-7xl mx-auto">
    
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Browse Formations</h1>
        <p class="text-gray-600">
            Explore all <?php echo number_format(tiny::data()->totalCount); ?> formations in the registry
        </p>
    </div>

    <!-- Sort Controls -->
    <div class="bg-white border border-gray-200 rounded-lg p-4 mb-6">
        <div class="flex items-center gap-4">
            <span class="text-sm font-semibold text-gray-700">Sort by:</span>
            
            <a href="<?php tiny::homeURL('/browse?sort=recent'); ?>" 
               class="px-4 py-2 text-sm rounded <?php echo tiny::data()->sort === 'recent' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?>">
                Recently Published
            </a>
            
            <a href="<?php tiny::homeURL('/browse?sort=downloads'); ?>" 
               class="px-4 py-2 text-sm rounded <?php echo tiny::data()->sort === 'downloads' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?>">
                Most Downloads
            </a>
            
            <a href="<?php tiny::homeURL('/browse?sort=stars'); ?>" 
               class="px-4 py-2 text-sm rounded <?php echo tiny::data()->sort === 'stars' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?>">
                Most Stars
            </a>
            
            <a href="<?php tiny::homeURL('/browse?sort=name'); ?>" 
               class="px-4 py-2 text-sm rounded <?php echo tiny::data()->sort === 'name' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?>">
                Name (A-Z)
            </a>
        </div>
    </div>

    <!-- Formations Grid -->
    <?php if (empty(tiny::data()->formations)): ?>
        <div class="bg-white border border-gray-200 rounded-lg p-12 text-center">
            <div class="text-gray-400 text-5xl mb-4">📦</div>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">No formations found</h3>
            <p class="text-gray-600">Be the first to publish!</p>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            <?php foreach (tiny::data()->formations as $formation): ?>
                <?php tiny::components()->FormationCard(formation: $formation, showStats: true); ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

<?php tiny::layout()->default('/'); ?>
