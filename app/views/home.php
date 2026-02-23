<?php
tiny::layout()->default(title: 'Discover & Share AI Formations', emptyLayout: false);
tiny::components()->require('FormationCard');
?>


<div class="grid grid-cols-12 gap-x-10 my-5 relative">
    <div class="col-span-8 h-full lg:min-h-[64dvh]">
        <h1 class="text-4xl font-bold mb-4">Welcome to the Registry</h1>
        <h3 class="text-lg font-medium -mt-1! mb-4 opacity-70">Find ready-to-use formations or publish your own for others to discover.</h3>

        <?php if (!empty(tiny::data()->formations['trending'])): ?>
            <!-- trending -->
            <header class="flex items-center justify-between">
                <h2 class="text-xl font-semibold mb-4 font-sans! flex items-center gap-x-2 mt-8">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="size-6 pt-px text-amber-500 dark:text-amber-400">
                        <g fill="none" fill-rule="nonzero">
                            <path fill="currentColor" d="M17 5.5a1.5 1.5 0 0 0 0 3h.379L14 11.879l-3.44-3.44a1.5 1.5 0 0 0-2.12 0l-6.5 6.5a1.5 1.5 0 0 0 2.12 2.122l5.44-5.44 3.44 3.44a1.5 1.5 0 0 0 2.12 0l4.44-4.44V11a1.5 1.5 0 0 0 3 0V7A1.5 1.5 0 0 0 21 5.5h-4Z"></path>
                        </g>
                    </svg>
                    <span>Trending this week</span>
                </h2>
                <a href="<?php tiny::homeURL('/browse?sort=trending'); ?>" class="link mt-3">
                    View all ›
                </a>
            </header>
            <div class="mb-8 divide-y border-t px-0.5 gap-2 [&>div]:flex [&>div]:items-start [&>div]:justify-between [&>div]:py-4 &>div]:my-0">
                <?php foreach (tiny::data()->formations['trending'] as $formation): ?>
                    <?php tiny::components()->FormationCard(formation: $formation, withUsername: true); ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- recently published -->
        <header class="flex items-center justify-between">
            <h2 class="text-xl font-semibold mb-4 font-sans! flex items-center gap-x-2 mt-8">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="size-6 pt-px text-pink-500 dark:text-pink-400">
                    <g fill="none" fill-rule="nonzero   ">
                        <path fill="currentColor" d="M12 2a6.99 6.99 0 0 1 2.263.374 4.5 4.5 0 0 0 4.5 7.447L19 9.743v2.784a1 1 0 0 0 .06.34l.046.107 1.716 3.433a1.1 1.1 0 0 1-.869 1.586l-.115.006H4.162a1.1 1.1 0 0 1-1.03-1.487l.046-.105 1.717-3.433a1 1 0 0 0 .098-.331L5 12.528V9a7 7 0 0 1 7-7m5.5 1a2.5 2.5 0 1 1 0 5 2.5 2.5 0 0 1 0-5M12 21a3.001 3.001 0 0 1-2.83-2h5.66A3.001 3.001 0 0 1 12 21"></path>
                    </g>
                </svg>
                <span>Recently published</span>
            </h2>
            <a href="<?php tiny::homeURL('/browse?sort=recent'); ?>" class="link mt-3">
                View all ›
            </a>
        </header>
        <div class="mb-8 divide-y border-t px-0.5 gap-2 [&>div]:flex [&>div]:items-start [&>div]:justify-between [&>div]:py-4 &>div]:my-0">
            <?php foreach (tiny::data()->formations['recent'] as $formation): ?>
                <?php tiny::components()->FormationCard(formation: $formation, withUsername: true); ?>
            <?php endforeach; ?>
        </div>

        <!-- most popular -->
        <header class="flex items-center justify-between">
            <h2 class="text-xl font-semibold mb-4 font-sans! flex items-center gap-x-2 mt-8">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="size-6 pt-px text-green-600 dark:text-green-400">
                    <g fill="currentColor">
                        <path d="M19.48,12.35c-1.57-4.08-7.16-4.3-5.81-10.23c0.1-0.44-0.37-0.78-0.75-0.55C9.29,3.71,6.68,8,8.87,13.62 c0.18,0.46-0.36,0.89-0.75,0.59c-1.81-1.37-2-3.34-1.84-4.75c0.06-0.52-0.62-0.77-0.91-0.34C4.69,10.16,4,11.84,4,14.37 c0.38,5.6,5.11,7.32,6.81,7.54c2.43,0.31,5.06-0.14,6.95-1.87C19.84,18.11,20.6,15.03,19.48,12.35z M10.2,17.38 c1.44-0.35,2.18-1.39,2.38-2.31c0.33-1.43-0.96-2.83-0.09-5.09c0.33,1.87,3.27,3.04,3.27,5.08C15.84,17.59,13.1,19.76,10.2,17.38z"></path>
                        <path d="M19.48,12.35c-1.57-4.08-7.16-4.3-5.81-10.23c0.1-0.44-0.37-0.78-0.75-0.55C9.29,3.71,6.68,8,8.87,13.62 c0.18,0.46-0.36,0.89-0.75,0.59c-1.81-1.37-2-3.34-1.84-4.75c0.06-0.52-0.62-0.77-0.91-0.34C4.69,10.16,4,11.84,4,14.37 c0.38,5.6,5.11,7.32,6.81,7.54c2.43,0.31,5.06-0.14,6.95-1.87C19.84,18.11,20.6,15.03,19.48,12.35z M10.2,17.38 c1.44-0.35,2.18-1.39,2.38-2.31c0.33-1.43-0.96-2.83-0.09-5.09c0.33,1.87,3.27,3.04,3.27,5.08C15.84,17.59,13.1,19.76,10.2,17.38z"></path>
                    </g>
                </svg>
                <span>Most popular</span>
            </h2>
            <a href="<?php tiny::homeURL('/browse?sort=downloads'); ?>" class="link mt-3">
                View all ›
            </a>
        </header>
        <div class="divide-y border-t px-0.5 gap-2 [&>div]:flex [&>div]:items-start [&>div]:justify-between [&>div]:py-4 &>div]:my-0">
            <?php foreach (tiny::data()->formations['popular'] as $formation): ?>
                <?php tiny::components()->FormationCard(formation: $formation, withUsername: true); ?>
            <?php endforeach; ?>
        </div>


    </div>

    <!-- sidebar -->
    <div class="col-span-4 h-full">
        <!-- active publishers -->
        <section>
            <h2 class="text-xl font-semibold mb-4">Active publishers</h2>

            <div class="flex flex-wrap gap-2">
                <?php foreach (tiny::data()->activeUsers as $user): ?>
                    <a href="<?php tiny::homeURL('/@' . $user['registry_username']); ?>" class="block relative w-fit" data-tooltip="@<?php echo htmlspecialchars($user['registry_username']); ?>">
                        <?php if ($user['github_avatar']): ?>
                            <img
                                loading="lazy"
                                src="<?php echo htmlspecialchars($user['github_avatar']); ?>"
                                alt="<?php echo htmlspecialchars($user['registry_username']); ?>"
                                class="size-10 rounded-full" />
                        <?php else: ?>
                            <div class="size-10 rounded-full bg-gray-200 dark:bg-gray-500 flex items-center justify-center text-4xl">
                                <?php echo strtoupper(substr($user['registry_username'], 0, 1)); ?>
                            </div>
                        <?php endif; ?>
                        <span class="absolute -bottom-1 -right-1 badge rounded-full h-4 min-w-4 px-1 text-[9px] subpixel-antialiased"><?php echo ($user['formations_count'] > 99) ? $user['formations_count'] . '+' : $user['formations_count']; ?></span>
                    </a>
                <?php endforeach; ?>
            </div>

        </section>

        <!-- Getting Started -->
        <section class="mt-12">
            <h2 class="text-xl font-semibold mb-4">Getting started</h2>

            <div class="card p-5 shadow-xs">
                <h3 class="text-base font-semibold -mb-2! flex items-center gap-x-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" class="size-4 text-sky-500">
                        <path d="M3.2 18C1.88484 17.235 1 15.8051 1 14.1674C1 12.1053 2.40285 10.3727 4.30122 9.88197C4.30041 9.83571 4.3 9.78935 4.3 9.7429C4.3 5.46661 7.74741 2 12 2C15.6211 2 18.6584 4.51348 19.4806 7.90009C21.5395 8.69955 23 10.7089 23 13.0613C23 14.8707 22.1359 16.4772 20.8 17.4862M12 13V22M12 22L15.5 18.5M12 22L8.5 18.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                    </svg>
                    <span>Pulling a formation</span>
                </h3>
                <p class="-mt-2! leading-[1.8] text-[13px] opacity-80">Install any formation with a single command.</p>
                <pre class="-my-6!"><code class="leading-[1.7]">$ muxi <span class="text-green-600 dark:text-green-500">pull</span> <span class="opacity-80">@muxi/hello-muxi</span></code></pre>
                <p class="text-xs text-neutral-500 dark:text-neutral-400 antialiased leading-normal mb-0!">Copy &amp; paste this command into your terminal to install it *</p>
            </div>

            <div class="card p-5 shadow-xs my-4">
                <h3 class="text-base font-semibold -mb-2! flex items-center gap-x-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" class="size-4 text-amber-500">
                        <path d="M3.2 18C1.88484 17.235 1 15.8051 1 14.1674C1 12.1053 2.40285 10.3727 4.30122 9.88197C4.30041 9.83571 4.3 9.78935 4.3 9.7429C4.3 5.46661 7.74741 2 12 2C15.6211 2 18.6584 4.51348 19.4806 7.90009C21.5395 8.69955 23 10.7089 23 13.0613C23 14.8707 22.1359 16.4772 20.8 17.4862M12 22V13M12 13L8.5 16.5M12 13L15.5 16.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                    </svg>
                    <span>Publishing a formation</span>
                </h3>
                <p class="-mt-2! leading-[1.8] text-[13px] opacity-80">Share your formations with the community.</p>
                <pre class="-my-6!"><code class="leading-[1.7]">$ muxi <span class="text-green-600 dark:text-green-500">login</span
                ><br>$ cd path/to/your/formation<br
                >$ muxi <span class="text-green-600 dark:text-green-500">push</span></code></pre>
                <p class="text-xs text-neutral-500 dark:text-neutral-500 antialiased leading-normal mb-0!">Copy &amp; paste these commands into your terminal to push publish a formation *</p>
            </div>
            <p class="text-sm text-neutral-500 dark:text-neutral-400 antialiased leading-normal">* Make sure you have the <a href="https://muxi.org/docs/cli/" target="_blank" class="link">MUXI CLI Tool</a> installed.</p>
        </section>


        <!-- Stats -->
        <section class="mt-12 mx-0.5">
            <ul class="text-sm mt-8! [&>li]:pl-px [&>li]:flex [&>li]:items-center [&>li]:space-x-2 [&>li>svg]:size-4 [&>li>strong]:mr-2 [&>li]:opacity-80 [&>li:hover]:opacity-100">
                <li>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none">
                        <path d="M7.49988 9.5L16.5 4M12 12.5L21 7M12 12.5L3 7M12 12.5V22.5M2 9.71771V14.2823C2 15.2733 2 15.7688 2.14219 16.2141C2.26802 16.6081 2.47396 16.9718 2.74708 17.2824C3.05572 17.6334 3.48062 17.8884 4.33042 18.3983L9.53042 21.5183C10.4283 22.057 10.8773 22.3264 11.3565 22.4316C11.7805 22.5247 12.2195 22.5247 12.6435 22.4316C13.1227 22.3264 13.5717 22.057 14.4696 21.5183L19.6696 18.3983C20.5194 17.8884 20.9443 17.6334 21.2529 17.2824C21.526 16.9718 21.732 16.6081 21.8578 16.2141C22 15.7688 22 15.2733 22 14.2823V9.71771C22 8.72669 22 8.23117 21.8578 7.78593C21.732 7.39192 21.526 7.02818 21.2529 6.71757C20.9443 6.36657 20.5194 6.11163 19.6696 5.60175L14.4696 2.48175C13.5717 1.94301 13.1227 1.67364 12.6435 1.56839C12.2195 1.4753 11.7805 1.4753 11.3565 1.56839C10.8773 1.67364 10.4283 1.94301 9.53042 2.48175L4.33042 5.60175C3.48062 6.11163 3.05572 6.36657 2.74708 6.71757C2.47396 7.02818 2.26802 7.39192 2.14219 7.78593C2 8.23117 2 8.72669 2 9.71771Z" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"></path>
                    </svg>
                    <span><strong><?php echo number_format(tiny::data()->stats['total_formations']); ?></strong> Formation<?php echo tiny::data()->stats['total_formations'] != 1 ? 's' : ''; ?> published</span>
                </li>
                <li>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="px-px">
                        <path d="M3.2 18C1.88484 17.235 1 15.8051 1 14.1674C1 12.1053 2.40285 10.3727 4.30122 9.88197C4.30041 9.83571 4.3 9.78935 4.3 9.7429C4.3 5.46661 7.74741 2 12 2C15.6211 2 18.6584 4.51348 19.4806 7.90009C21.5395 8.69955 23 10.7089 23 13.0613C23 14.8707 22.1359 16.4772 20.8 17.4862M12 13V22M12 22L15.5 18.5M12 22L8.5 18.5" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round"></path>
                    </svg>
                    <span><strong><?php echo number_format(tiny::data()->stats['total_downloads']); ?></strong> Formation pull<?php echo tiny::data()->stats['total_downloads'] != 1 ? 's' : ''; ?></span>
                </li>
                <li>
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-users">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                        <path d="M9 7m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0"></path>
                        <path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        <path d="M21 21v-2a4 4 0 0 0 -3 -3.85"></path>
                    </svg>
                    <span><strong><?php echo number_format(tiny::data()->stats['total_users']); ?></strong> Publisher<?php echo tiny::data()->stats['total_users'] != 1 ? 's' : ''; ?></span>
                </li>
            </ul>

        </section>
    </div>
</div>




<?php tiny::layout()->default('/'); ?>
