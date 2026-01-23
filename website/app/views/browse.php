<?php
tiny::layout()->default(title: 'Browse Formations - MUXI Registry', emptyLayout: false);
tiny::components()->require('FormationCard');
?>

<div class="grid gap-x-8 my-5 relative">

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-sans! flex items-center">Browse Formations
                <span class="badge-secondary rounded-full mt-1.5 ml-2 text-xs!"><?php echo number_format(tiny::data()->totalCount); ?> </span>
            </h1>
            <p class="font-medium text-lg opacity-60 mt-2!">
                Find formations to get up and running quickly
            </p>
        </div>
        <a href="https://muxi.org/docs/registry/" class="link mt-6 mr-1">Read the docs ›</a>
    </div>

    <!-- Sort Controls -->
    <div class="tabs w-full mt-4 mb-2">
        <nav role="tablist" class="w-full space-x-1 bg-black/5 dark:bg-white/5">
            <a hx-boost="true" hx-push-url="true"
                href="?sort=recent" role="tab" aria-selected="<?php echo tiny::data()->sort === 'recent' ? "true" : "false"; ?>" class="btn-ghost">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="-ml-2 opacity-80 dark:opacity-60">
                    <g fill="none">
                        <path d="M24 0v24H0V0zM12.594 23.258l-.012.002-.071.035-.02.004-.014-.004-.071-.036c-.01-.003-.019 0-.024.006l-.004.01-.017.428.005.02.01.013.104.074.015.004.012-.004.104-.074.012-.016.004-.017-.017-.427c-.002-.01-.009-.017-.016-.018m.264-.113-.014.002-.184.093-.01.01-.003.011.018.43.005.012.008.008.201.092c.012.004.023 0 .029-.008l.004-.014-.034-.614c-.003-.012-.01-.02-.02-.022m-.715.002a.023.023 0 0 0-.027.006l-.006.014-.034.614c0 .012.007.02.017.024l.015-.002.201-.093.01-.008.003-.011.018-.43-.003-.012-.01-.01z"></path>
                        <path fill="currentColor" d="M12 2a6.99 6.99 0 0 1 2.263.374 4.5 4.5 0 0 0 4.5 7.447L19 9.743v2.784a1 1 0 0 0 .06.34l.046.107 1.716 3.433a1.1 1.1 0 0 1-.869 1.586l-.115.006H4.162a1.1 1.1 0 0 1-1.03-1.487l.046-.105 1.717-3.433a1 1 0 0 0 .098-.331L5 12.528V9a7 7 0 0 1 7-7m5.5 1a2.5 2.5 0 1 1 0 5 2.5 2.5 0 0 1 0-5M12 21a3.001 3.001 0 0 1-2.83-2h5.66A3.001 3.001 0 0 1 12 21"></path>
                    </g>
                </svg>
                Recent
            </a>
            <a hx-boost="true" hx-push-url="true"
                href="?sort=trending" role="tab" aria-selected="<?php echo tiny::data()->sort === 'trending' ? "true" : "false"; ?>" class="btn-ghost">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="-ml-2 opacity-80 dark:opacity-60 mt-px">
                    <g fill="none" fill-rule="nonzero">
                        <path fill="currentColor" d="M17 5.5a1.5 1.5 0 0 0 0 3h.379L14 11.879l-3.44-3.44a1.5 1.5 0 0 0-2.12 0l-6.5 6.5a1.5 1.5 0 0 0 2.12 2.122l5.44-5.44 3.44 3.44a1.5 1.5 0 0 0 2.12 0l4.44-4.44V11a1.5 1.5 0 0 0 3 0V7A1.5 1.5 0 0 0 21 5.5h-4Z"></path>
                    </g>
                </svg>
                Trending
            </a>
            <a hx-boost="true" hx-push-url="true"
                href="?sort=downloads" role="tab" aria-selected="<?php echo tiny::data()->sort === 'downloads' ? "true" : "false"; ?>" class="btn-ghost">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="-ml-2 opacity-80 dark:opacity-60">
                    <g fill="currentColor">
                        <path d="M19.48,12.35c-1.57-4.08-7.16-4.3-5.81-10.23c0.1-0.44-0.37-0.78-0.75-0.55C9.29,3.71,6.68,8,8.87,13.62 c0.18,0.46-0.36,0.89-0.75,0.59c-1.81-1.37-2-3.34-1.84-4.75c0.06-0.52-0.62-0.77-0.91-0.34C4.69,10.16,4,11.84,4,14.37 c0.38,5.6,5.11,7.32,6.81,7.54c2.43,0.31,5.06-0.14,6.95-1.87C19.84,18.11,20.6,15.03,19.48,12.35z M10.2,17.38 c1.44-0.35,2.18-1.39,2.38-2.31c0.33-1.43-0.96-2.83-0.09-5.09c0.33,1.87,3.27,3.04,3.27,5.08C15.84,17.59,13.1,19.76,10.2,17.38z"></path>
                        <path d="M19.48,12.35c-1.57-4.08-7.16-4.3-5.81-10.23c0.1-0.44-0.37-0.78-0.75-0.55C9.29,3.71,6.68,8,8.87,13.62 c0.18,0.46-0.36,0.89-0.75,0.59c-1.81-1.37-2-3.34-1.84-4.75c0.06-0.52-0.62-0.77-0.91-0.34C4.69,10.16,4,11.84,4,14.37 c0.38,5.6,5.11,7.32,6.81,7.54c2.43,0.31,5.06-0.14,6.95-1.87C19.84,18.11,20.6,15.03,19.48,12.35z M10.2,17.38 c1.44-0.35,2.18-1.39,2.38-2.31c0.33-1.43-0.96-2.83-0.09-5.09c0.33,1.87,3.27,3.04,3.27,5.08C15.84,17.59,13.1,19.76,10.2,17.38z"></path>
                    </g>
                </svg>
                Popular
            </a>
            <a hx-boost="true" hx-push-url="true"
                href="?sort=name" role="tab" aria-selected="<?php echo tiny::data()->sort === 'name' ? "true" : "false"; ?>" class="btn-ghost">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="-ml-2 opacity-80 dark:opacity-60">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                    <path d="M11 6h9"></path>
                    <path d="M11 12h9"></path>
                    <path d="M11 18h9"></path>
                    <path d="M4 10v-4.5a1.5 1.5 0 0 1 3 0v4.5"></path>
                    <path d="M4 8h3"></path>
                    <path d="M4 20h1.5a1.5 1.5 0 0 0 0 -3h-1.5h1.5a1.5 1.5 0 0 0 0 -3h-1.5v6z"></path>
                </svg>
                Name (A-Z)
            </a>
        </nav>
    </div>


    <!-- Formations Grid -->
    <?php if (empty(tiny::data()->formations)): ?>
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
                    No formations (yet)
                </p>
                <p class="mb-5 text-sm text-gray-500 dark:text-neutral-500">
                    To be the first to publish, go into the formation directory and type
                    <code>muxi push</code> in your terminal.
                </p>
                <p class="mb-5 text-sm text-gray-500 dark:text-neutral-500 pl-1.5">
                    Need help? Check out our
                    <a href="https://muxi.org/docs/registry/publishing-formations" target="_blank" class="link">publishing guide →</a>
                </p>
            </div>
        </div>
    <?php else: ?>
        <div class="divide-y px-0.5 grid grid-cols-1 md:grid-cols-2 gap-x-12 [&>div]:flex [&>div]:items-start [&>div]:justify-between [&>div]:py-6 &>div]:my-4">
            <?php foreach (tiny::data()->formations as $formation): ?>
                <?php tiny::components()->FormationCard(formation: $formation, withUsername: true); ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

<?php tiny::layout()->default('/'); ?>
