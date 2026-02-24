<?php
$pageTitle = !empty(tiny::data()->query)
    ? 'Search results for: ' . htmlspecialchars(tiny::data()->query)
    : 'Search Formations';

tiny::layout()->default(title: $pageTitle, emptyLayout: false);
tiny::components()->require('FormationCard');
?>

<div class="lg:grid gap-x-8 my-5 relative">

    <?php if (!empty(tiny::data()->query)): ?>
        <!-- Header -->
        <header class="flex items-center justify-between mb-4">
            <div>
                <h1 class="text-4xl font-bold flex items-center mb-3"><?php echo $pageTitle; ?></h1>
                <?php // if (!empty(tiny::data()->query)):
                ?>
                <h3 class="text-base -mt-1! mb-4 opacity-70">
                    <?php if (tiny::data()->resultCount === 0): ?>
                        No results found for <strong class="italic"><?php echo htmlspecialchars(tiny::data()->correctedQuery ?? tiny::data()->query); ?></strong>
                    <?php elseif (tiny::data()->resultCount === 1): ?>
                        Found <strong>1</strong> result for <strong><?php echo htmlspecialchars(tiny::data()->correctedQuery ?? tiny::data()->query); ?></strong>
                    <?php else: ?>
                        Found <strong><?php echo number_format(tiny::data()->resultCount); ?></strong> results for <strong class="italic"><?php echo htmlspecialchars(tiny::data()->correctedQuery ?? tiny::data()->query); ?></strong>
                    <?php endif; ?>


                    <?php if (!empty(tiny::data()->correctedQuery)): ?>
                        <span class="text-sm ml-4">(search instead for
                            <a href="<?php tiny::homeURL('/search?q=' . urlencode(tiny::data()->originalQuery)); ?>" class="italic text-blue-700 dark:text-blue-400 hover:underline font-medium">
                                <?php echo htmlspecialchars(tiny::data()->originalQuery); ?></a>?)</span>
                    <?php endif; ?>
                </h3>
            </div>
        </header>
    <?php endif; ?>


    <!-- Results -->
    <?php if (!empty(tiny::data()->query)): ?>
        <!-- Results Grid -->
        <?php if (tiny::data()->resultCount === 0): ?>
            <!-- Empty State -->
            <div class="p-5 min-h-96 flex flex-col justify-center items-center text-center">
                <svg class="w-48 mx-auto mb-4" width="178" height="90" viewBox="0 0 178 90" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect x="27" y="50.5" width="124" height="39" rx="7.5" fill="currentColor" class="fill-white dark:fill-neutral-800" />
                    <rect x="27" y="50.5" width="124" height="39" rx="7.5" stroke="currentColor" class="stroke-gray-50 dark:stroke-neutral-700/10" />
                    <rect x="34.5" y="58" width="24" height="24" rx="4" fill="currentColor" class="fill-gray-50 dark:fill-neutral-700/30" />
                    <rect x="66.5" y="61" width="60" height="6" rx="3" fill="currentColor" class="fill-gray-50 dark:fill-neutral-700/30" />
                    <rect x="66.5" y="73" width="77" height="6" rx="3" fill="currentColor" class="fill-gray-50 dark:fill-neutral-700/30" />
                    <rect x="19.5" y="28.5" width="139" height="39" rx="7.5" fill="currentColor" class="fill-white dark:fill-neutral-800" />
                    <rect x="19.5" y="28.5" width="139" height="39" rx="7.5" stroke="currentColor" class="stroke-gray-100 dark:stroke-neutral-700/30" />
                    <rect x="27" y="36" width="24" height="24" rx="4" fill="currentColor" class="fill-gray-100 dark:fill-neutral-700/70" />
                    <rect x="59" y="39" width="60" height="6" rx="3" fill="currentColor" class="fill-gray-100 dark:fill-neutral-700/70" />
                    <rect x="59" y="51" width="92" height="6" rx="3" fill="currentColor" class="fill-gray-100 dark:fill-neutral-700/70" />
                    <g filter="url(#filter19)">
                        <rect x="12" y="6" width="154" height="40" rx="8" fill="currentColor" class="fill-white dark:fill-neutral-800" shape-rendering="crispEdges" />
                        <rect x="12.5" y="6.5" width="153" height="39" rx="7.5" stroke="currentColor" class="stroke-gray-100 dark:stroke-neutral-700/60" shape-rendering="crispEdges" />
                        <rect x="20" y="14" width="24" height="24" rx="4" fill="currentColor" class="fill-gray-200 dark:fill-neutral-700 " />
                        <rect x="52" y="17" width="60" height="6" rx="3" fill="currentColor" class="fill-gray-200 dark:fill-neutral-700" />
                        <rect x="52" y="29" width="106" height="6" rx="3" fill="currentColor" class="fill-gray-200 dark:fill-neutral-700" />
                    </g>
                    <defs>
                        <filter id="filter19" x="0" y="0" width="178" height="64" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                            <feFlood flood-opacity="0" result="BackgroundImageFix" />
                            <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha" />
                            <feOffset dy="6" />
                            <feGaussianBlur stdDeviation="6" />
                            <feComposite in2="hardAlpha" operator="out" />
                            <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.03 0" />
                            <feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow_1187_14810" />
                            <feBlend mode="normal" in="SourceGraphic" in2="effect1_dropShadow_1187_14810" result="shape" />
                        </filter>
                    </defs>
                </svg>

                <div class="max-w-sm mx-auto">
                    <p class="mt-2 text-base font-medium text-gray-800 dark:text-neutral-200">
                        No results found :(
                    </p>
                    <p class="mb-8 text-sm text-gray-500 dark:text-neutral-500">
                        No formations found matching your search.<br>
                        Try different keywords or browse all formations
                    </p>
                    <p class="pt-4">
                        <a href="<?php tiny::homeURL('/browse'); ?>" class="btn">Browse All Formations ›</a>
                    </p>
                </div>
            </div>
        <?php else: ?>
            <div class="mb-24 divide-y border-y pt-2 pb-4 px-0.5 grid grid-cols-1 md:grid-cols-2 gap-x-12 [&>div]:flex [&>div]:items-start [&>div]:justify-between [&>div]:py-6 &>div]:my-4">
                <?php foreach (tiny::data()->formations as $formation): ?>
                    <?php tiny::components()->FormationCard(formation: $formation, withUsername: true); ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    <?php else: ?>

        <!-- Empty State -->
        <div class="p-5 min-h-96 flex flex-col justify-center items-center text-center">
            <div class="max-w-md mx-auto">
                <p class="mt-2 text-xl font-medium text-gray-800 dark:text-neutral-200">
                    Search formations
                </p>
                <p class="mb-8 text-sm text-gray-500 dark:text-neutral-500">
                    Enter keywords to find formations by name, description, or content
                </p>

                <form action="<?php tiny::homeURL('/search'); ?>" method="GET">
                    <input class="input w-full bg-white dark:bg-black" type="search" placeholder="Enter a phrase and hit enter" name="q" value="<?php echo urldecode(tiny::router()->query['q'] ?? ''); ?>">
                </form>

            </div>
        </div>


    <?php endif; ?>

</div>

<?php tiny::layout()->default('/'); ?>
