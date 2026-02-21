<?php
$name = trim(htmlspecialchars(tiny::data()->profile['first_name'] . ' ' . tiny::data()->profile['last_name']));
tiny::layout()->default(
    title: '@' . htmlspecialchars(tiny::data()->profile['registry_username']) . ($name ? ' (' . $name . ')' : ''),
    emptyLayout: false
);

// tiny::dd(tiny::data()->profile);
tiny::components()->require('FormationCard');
?>

<div class="grid grid-cols-12 gap-x-8 my-5 relative">
    <div class="col-span-4 h-full">

        <div class="relative w-fit">
            <?php if (tiny::data()->profile['github_avatar']): ?>
                <img
                    loading="lazy"
                    src="<?php echo htmlspecialchars(tiny::data()->profile['github_avatar']); ?>"
                    alt="<?php echo htmlspecialchars(tiny::data()->profile['registry_username']); ?>"
                    class="size-20 rounded-full" />
            <?php else: ?>
                <div class="size-20 rounded-full bg-gray-200 dark:bg-gray-500 flex items-center justify-center text-4xl">
                    <?php echo strtoupper(substr(tiny::data()->profile['registry_username'], 0, 1)); ?>
                </div>
            <?php endif; ?>

            <a href="https://github.com/<?php echo htmlspecialchars(tiny::data()->profile['github_username']); ?>" target="_blank" data-side="right" data-tooltip="github.com/<?php echo htmlspecialchars(tiny::data()->profile['github_username']); ?>" class="absolute bottom-0 -right-2">
                <svg class="size-7 hover:scale-110 transition-all bg-[#fbfbf8] dark:bg-[#131215] rounded-full p-0.5 dark:text-white" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z" />
                </svg>
            </a>
        </div>

        <h1 class="text-3xl font-sans! leading-none font-bold mt-5 flex items-center">
            <?php
            if (tiny::data()->profile['first_name'] || tiny::data()->profile['last_name']) {
                echo htmlspecialchars(tiny::data()->profile['first_name']); ?> <?php echo htmlspecialchars(tiny::data()->profile['last_name']);
            } else {
                echo htmlspecialchars(tiny::data()->profile['registry_username']);
            }
            ?>
            <?php if (tiny::data()->profile['github_type'] === 'Organization'): ?>
                <svg class="size-3.5 opacity-70 mt-1.5 ml-2" viewBox="0 0 16 16" version="1.1">
                    <path fill="currentColor" d="M1.75 16A1.75 1.75 0 0 1 0 14.25V1.75C0 .784.784 0 1.75 0h8.5C11.216 0 12 .784 12 1.75v12.5c0 .085-.006.168-.018.25h2.268a.25.25 0 0 0 .25-.25V8.285a.25.25 0 0 0-.111-.208l-1.055-.703a.749.749 0 1 1 .832-1.248l1.055.703c.487.325.779.871.779 1.456v5.965A1.75 1.75 0 0 1 14.25 16h-3.5a.766.766 0 0 1-.197-.026c-.099.017-.2.026-.303.026h-3a.75.75 0 0 1-.75-.75V14h-1v1.25a.75.75 0 0 1-.75.75Zm-.25-1.75c0 .138.112.25.25.25H4v-1.25a.75.75 0 0 1 .75-.75h2.5a.75.75 0 0 1 .75.75v1.25h2.25a.25.25 0 0 0 .25-.25V1.75a.25.25 0 0 0-.25-.25h-8.5a.25.25 0 0 0-.25.25ZM3.75 6h.5a.75.75 0 0 1 0 1.5h-.5a.75.75 0 0 1 0-1.5ZM3 3.75A.75.75 0 0 1 3.75 3h.5a.75.75 0 0 1 0 1.5h-.5A.75.75 0 0 1 3 3.75Zm4 3A.75.75 0 0 1 7.75 6h.5a.75.75 0 0 1 0 1.5h-.5A.75.75 0 0 1 7 6.75ZM7.75 3h.5a.75.75 0 0 1 0 1.5h-.5a.75.75 0 0 1 0-1.5ZM3 9.75A.75.75 0 0 1 3.75 9h.5a.75.75 0 0 1 0 1.5h-.5A.75.75 0 0 1 3 9.75ZM7.75 9h.5a.75.75 0 0 1 0 1.5h-.5a.75.75 0 0 1 0-1.5Z"></path>
                </svg>
            <?php endif; ?>
        </h1>

        <div class="flex w-full items-center gap-1 mt-1.5">
            <p class="text-lg font-medium m-0! opacity-60 -mt-[3px]!">@<?php echo tiny::data()->profile['registry_username']; ?></p>
            <?php if (tiny::data()->profile['is_verified']): ?>
                <span class="scale-80 badge-secondary bg-blue-500/80 text-white! dark:bg-blue-500 rounded-full leading-none pt-[3px]! pb-[2.5px]! pl-1.5 pr-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3.85 8.62a4 4 0 0 1 4.78-4.77 4 4 0 0 1 6.74 0 4 4 0 0 1 4.78 4.78 4 4 0 0 1 0 6.74 4 4 0 0 1-4.77 4.78 4 4 0 0 1-6.75 0 4 4 0 0 1-4.78-4.77 4 4 0 0 1 0-6.76Z" />
                        <path d="m9 12 2 2 4-4" />
                    </svg>
                    Verified
                </span>
            <?php endif; ?>

        </div>

        <?php if (tiny::data()->profile['bio']): ?>
            <p class="my-5! text-base subpixel-antialiased leading-[1.8] opacity-80 text-pretty"><?php echo preg_replace('/@([a-zA-Z0-9_-]+)/', '<a href="/@$1" class="text-black dark:text-white font-medium hover:text-blue-500!">@$1</a>', htmlspecialchars(str_replace('@muxi-ai', '@muxi', tiny::data()->profile['bio']))); ?></p>
        <?php endif; ?>


        <ul class="text-sm mt-8! [&>li]:pl-px [&>li]:flex [&>li]:items-center [&>li]:space-x-2 [&>li>a]:flex [&>li>a]:items-center [&>li>a]:space-x-2 [&>li>svg]:size-4 [&>li>a>svg]:size-4 [&>li>strong]:font-semibold [&>li]:opacity-80 [&>li:hover]:opacity-100">
            <li>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none">
                    <path d="M7.49988 9.5L16.5 4M12 12.5L21 7M12 12.5L3 7M12 12.5V22.5M2 9.71771V14.2823C2 15.2733 2 15.7688 2.14219 16.2141C2.26802 16.6081 2.47396 16.9718 2.74708 17.2824C3.05572 17.6334 3.48062 17.8884 4.33042 18.3983L9.53042 21.5183C10.4283 22.057 10.8773 22.3264 11.3565 22.4316C11.7805 22.5247 12.2195 22.5247 12.6435 22.4316C13.1227 22.3264 13.5717 22.057 14.4696 21.5183L19.6696 18.3983C20.5194 17.8884 20.9443 17.6334 21.2529 17.2824C21.526 16.9718 21.732 16.6081 21.8578 16.2141C22 15.7688 22 15.2733 22 14.2823V9.71771C22 8.72669 22 8.23117 21.8578 7.78593C21.732 7.39192 21.526 7.02818 21.2529 6.71757C20.9443 6.36657 20.5194 6.11163 19.6696 5.60175L14.4696 2.48175C13.5717 1.94301 13.1227 1.67364 12.6435 1.56839C12.2195 1.4753 11.7805 1.4753 11.3565 1.56839C10.8773 1.67364 10.4283 1.94301 9.53042 2.48175L4.33042 5.60175C3.48062 6.11163 3.05572 6.36657 2.74708 6.71757C2.47396 7.02818 2.26802 7.39192 2.14219 7.78593C2 8.23117 2 8.72669 2 9.71771Z" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
                <span><strong><?php echo number_format(tiny::data()->stats['formations_count']); ?></strong> Formation<?php echo tiny::data()->stats['formations_count'] != 1 ? 's' : ''; ?></span>
            </li>

            <li>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="px-px">
                    <path d="M3.2 18C1.88484 17.235 1 15.8051 1 14.1674C1 12.1053 2.40285 10.3727 4.30122 9.88197C4.30041 9.83571 4.3 9.78935 4.3 9.7429C4.3 5.46661 7.74741 2 12 2C15.6211 2 18.6584 4.51348 19.4806 7.90009C21.5395 8.69955 23 10.7089 23 13.0613C23 14.8707 22.1359 16.4772 20.8 17.4862M12 13V22M12 22L15.5 18.5M12 22L8.5 18.5" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
                <span><strong><?php echo number_format(tiny::data()->stats['total_downloads']); ?></strong> Pull<?php echo tiny::data()->stats['total_downloads'] != 1 ? 's' : ''; ?></span>
            </li>

            <li>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                    <path fill="currentColor" d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"></path>
                </svg>
                <span><strong><?php echo number_format(tiny::data()->stats['total_stars']); ?></strong> GitHub Star<?php echo tiny::data()->stats['total_stars'] != 1 ? 's' : ''; ?></span>
            </li>

            <?php if (tiny::data()->isOrgAdmin ?? false): ?>
            <li class="border-t mt-6! pt-6!">
                <a href="https://github.com/apps/muxi-registry" target="_blank">
                    <svg class="p-px" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z" />
                    </svg>
                    <span>Manage GitHub App ↗</span>
                </a>
            </li>
            <?php endif; ?>
        </ul>
    </div>

    <div class="col-span-8 h-full lg:min-h-[64dvh]">
        <h2 class="text-2xl font-semibold font-sans! mb-2 px-0.5">Formations</h2>

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
                        No formations
                    </p>
                    <p class="mb-5 text-sm text-gray-500 dark:text-neutral-500">
                        <strong><?php echo tiny::data()->profile['registry_username']; ?></strong> did not publish any formation yet
                    </p>
                </div>
            </div>
        <?php else: ?>
            <div class="tabs w-full my-4">
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


            <div class="divide-y border-y px-0.5 gap-4 [&>div]:flex [&>div]:items-start [&>div]:justify-between [&>div]:py-6 &>div]:my-4">
            <?php foreach (tiny::data()->formations as $formation): ?>
                <?php tiny::components()->FormationCard(formation: $formation, withUsername: false); ?>
            <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>


<?php tiny::layout()->default('/'); ?>
