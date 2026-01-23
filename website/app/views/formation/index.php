<?php
$formation = tiny::data()->formation;
$latestVersion = tiny::data()->latestVersion;
$stats = tiny::data()->stats;
$versions = tiny::data()->versions;
$downloadChart = tiny::data()->downloadChart;
$downloadsThisWeek = tiny::data()->downloadsThisWeek;
$weeklyChart = tiny::data()->weeklyChart;
$canDelete = tiny::data()->canDelete ?? false;
$homeURL = tiny::getHomeURL('/');

tiny::layout()->default(
    title: '@' . $formation['registry_username'] . '/' . $formation['name'],
    description: $formation['description'] ?? 'A formation on the Muxi Registry by @' . $formation['registry_username'],
    emptyLayout: false
);
?>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>


<div class="flex items-center justify-between space-x-4 mt-2 mb-3">
    <div class="flex items-center space-x-2">
        <a href="<?php echo $homeURL; ?>@<?= $formation['registry_username'] ?>"><img src="<?php echo $formation['github_avatar']; ?>" class="size-9 rounded-lg"></a>
        <h1 class="text-2xl font-sans! leading-none ml-1.5">
            <a href="<?php echo $homeURL; ?>@<?php echo $formation['registry_username']; ?>" class="opacity-75 hover:opacity-100 text-gold-on-hover">
                @<?php echo htmlspecialchars($formation['registry_username']); ?>
            </a>
            <span class="opacity-40 px-1">/</span>
            <span class="font-semibold"><?php echo htmlspecialchars($formation['name']); ?></span>
        </h1>
        <span class="border rounded-full text-[10px]! subpixel-antialiased bg-white/50 dark:bg-white/5 leading-none pt-[3px] pb-1 px-1.5 ml-2 mt-0.5">v <?php echo htmlspecialchars($formation['latest_version']); ?></span>
    </div>
    <div class="flex items-center space-x-2 pt-px">
        <button class="btn-sm-ghost pointer-events-none">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="px-px">
                <path d="M3.2 18C1.88484 17.235 1 15.8051 1 14.1674C1 12.1053 2.40285 10.3727 4.30122 9.88197C4.30041 9.83571 4.3 9.78935 4.3 9.7429C4.3 5.46661 7.74741 2 12 2C15.6211 2 18.6584 4.51348 19.4806 7.90009C21.5395 8.69955 23 10.7089 23 13.0613C23 14.8707 22.1359 16.4772 20.8 17.4862M12 13V22M12 22L15.5 18.5M12 22L8.5 18.5" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round"></path>
            </svg>
            Pulls <span class="badge-secondary rounded-full leading-none bg-white/50 dark:bg-white/20  pt-[3px]! pb-[2px]!"><?php echo number_format($formation['total_downloads']); ?></span>
            <?php if ($downloadsThisWeek > 0): ?>
                <span class="badge-secondary bg-green-200/50 dark:bg-green-800 leading-none pt-[3px]! pb-[2.5px]! pl-1.5 pr-2">
                    +<?php echo number_format($downloadsThisWeek); ?> this week
                </span>
            <?php endif; ?>
        </button>
    </div>
</div>
<hr>


<div class="grid grid-cols-10 gap-12 my-7">
    <div class="col-span-7 h-full">

        <div class="card dark:bg-black/5! shadow-xs dark:shadow-xl rounded-md plain p-0 overflow-hidden" id="readme">
            <div class="bg-black/2 dark:bg-white/4 py-2 px-5 border-b">
                <header class="flex items-center justify-between">
                    <ul class="divide-x text-sm flex items-center divider-x gap-x-2 p-0! my-0! -mx-px! [&>li]:m-0 [&>li]:pr-2 [&>li]:flex [&>li]:items-center [&>li]:space-x-2 [&>li>svg]:size-3.5 [&>li>span>strong]:pr-0.5 [&>li>span>strong]:font-semibold">
                        <?php if (@$stats['agents_count'] > 0): ?>
                            <li>
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 14 14">
                                    <path d="M 6.25 3.875 C 6.25 1.735 7.985 0 10.125 0 C 12.265 0 14 1.735 14 3.875 C 14 6.015 12.265 7.75 10.125 7.75 L 10 7.748 L 10 7.75 L 3.875 7.75 C 2.563 7.75 1.5 8.813 1.5 10.125 C 1.5 11.437 2.563 12.5 3.875 12.5 C 5.187 12.5 6.25 11.437 6.25 10.125 L 6.25 10 C 6.25 9.586 6.586 9.25 7 9.25 C 7.414 9.25 7.75 9.586 7.75 10 L 7.749 10.038 L 7.75 10.125 C 7.75 12.265 6.015 14 3.875 14 C 1.735 14 0 12.265 0 10.125 C 0 7.985 1.735 6.25 3.875 6.25 L 4 6.252 L 4 6.25 L 10.125 6.25 C 11.437 6.25 12.5 5.187 12.5 3.875 C 12.5 2.563 11.437 1.5 10.125 1.5 C 8.813 1.5 7.75 2.563 7.75 3.875 L 7.75 4 C 7.75 4.414 7.414 4.75 7 4.75 C 6.586 4.75 6.25 4.414 6.25 4 L 6.251 3.962 Z" fill="currentColor"></path>
                                </svg>
                                <span><strong><?php echo number_format($stats['agents_count']); ?></strong> Agent<?php echo $stats['agents_count'] != 1 ? 's' : ''; ?></span>
                            </li>
                        <?php endif; ?>
                        <?php if (@$stats['mcps_count'] > 0): ?>
                            <li>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="size-4.5!">
                                    <g stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5">
                                        <path d="m3.49994 11.7501 8.17176-8.17155c1.1045-1.10457 2.8955-1.10457 4 0s1.1045 2.89543 0 4m0 0-6.17176 6.17155m6.17176-6.17155c1.1045-1.10457 2.8955-1.10457 4 0s1.1045 2.89545 0 3.99995l-6.9645 6.9645c-.3905.3905-.3905 1.0237 0 1.4142l1.2927 1.2927"></path>
                                        <path d="m17.4999 9.74921-6.1717 6.17179c-1.1045 1.1045-2.89548 1.1045-3.99997 0-1.1045-1.1046-1.1045-2.8955 0-4l6.17167-6.17161"></path>
                                    </g>
                                </svg>
                                <span><strong><?php echo number_format($stats['mcps_count']); ?></strong> MCP<?php echo $stats['mcps_count'] != 1 ? 's' : ''; ?></span>
                            </li>
                        <?php endif; ?>
                        <?php if (@$stats['knowledge_count'] > 0): ?>
                            <li>
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                    <path d="M 12 19.714 C 13.183 19.714 14.143 20.674 14.143 21.857 C 14.143 23.041 13.183 24 12 24 C 10.817 24 9.857 23.041 9.857 21.857 C 9.857 20.674 10.817 19.714 12 19.714 Z M 12 0 C 13.183 0 14.143 0.959 14.143 2.143 C 14.143 3.326 13.183 4.286 12 4.286 C 10.817 4.286 9.857 3.326 9.857 2.143 C 9.857 0.959 10.817 0 12 0 Z M 4.286 12 C 4.286 13.183 3.326 14.143 2.143 14.143 C 0.959 14.143 0 13.183 0 12 C 0 10.817 0.959 9.857 2.143 9.857 C 3.326 9.857 4.286 10.817 4.286 12 Z M 24 12 C 24 13.183 23.041 14.143 21.857 14.143 C 20.674 14.143 19.714 13.183 19.714 12 C 19.714 10.817 20.674 9.857 21.857 9.857 C 23.041 9.857 24 10.817 24 12 Z M 6.545 17.455 C 7.382 18.293 7.381 19.65 6.543 20.487 C 5.706 21.324 4.348 21.324 3.511 20.486 C 2.674 19.648 2.675 18.29 3.513 17.453 C 4.35 16.616 5.708 16.617 6.545 17.455 Z M 20.486 3.514 C 21.323 4.352 21.322 5.71 20.484 6.547 C 19.646 7.384 18.288 7.383 17.451 6.545 C 16.614 5.707 16.615 4.35 17.453 3.513 C 18.291 2.676 19.649 2.676 20.486 3.514 Z M 17.455 17.455 C 18.293 16.618 19.65 16.619 20.487 17.457 C 21.324 18.294 21.324 19.652 20.486 20.489 C 19.648 21.326 18.29 21.325 17.453 20.487 C 16.616 19.65 16.617 18.292 17.455 17.455 Z M 3.514 3.514 C 4.352 2.677 5.71 2.678 6.547 3.516 C 7.384 4.354 7.383 5.712 6.545 6.549 C 5.707 7.386 4.35 7.385 3.513 6.547 C 2.676 5.709 2.676 4.351 3.514 3.514 Z" fill="currentColor"></path>
                                    <path d="M 12 6.857 C 12.947 6.857 13.714 7.625 13.714 8.571 C 13.714 9.518 12.947 10.286 12 10.286 C 11.053 10.286 10.286 9.518 10.286 8.571 C 10.286 7.625 11.053 6.857 12 6.857 Z M 12 13.714 C 12.947 13.714 13.714 14.482 13.714 15.429 C 13.714 16.375 12.947 17.143 12 17.143 C 11.053 17.143 10.286 16.375 10.286 15.429 C 10.286 14.482 11.053 13.714 12 13.714 Z M 17.143 12 C 17.143 12.947 16.375 13.714 15.429 13.714 C 14.482 13.714 13.714 12.947 13.714 12 C 13.714 11.053 14.482 10.286 15.429 10.286 C 16.375 10.286 17.143 11.053 17.143 12 Z M 10.286 12 C 10.286 12.947 9.518 13.714 8.571 13.714 C 7.625 13.714 6.857 12.947 6.857 12 C 6.857 11.053 7.625 10.286 8.571 10.286 C 9.518 10.286 10.286 11.053 10.286 12 Z" fill="currentColor" opacity="0.6"></path>
                                </svg>
                                <span><strong><?php echo number_format($stats['knowledge_count']); ?></strong> KB Source<?php echo $stats['knowledge_count'] != 1 ? 's' : ''; ?></span>
                            </li>
                        <?php endif; ?>
                        <?php if (@$stats['triggers_count'] > 0): ?>
                            <li>
                                <svg xmlns="http://www.w3.org/2000/svg" enable-background="new 0 0 24 24" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="m5.7 15.4 3.3-4.9c-.6-.6-1.1-1.2-1.4-2-1.2-2.8.1-6 2.9-7.2s6 .1 7.2 2.9c.6 1.3.6 2.8.1 4.1-.2.4-.7.7-1.1.4l-.2-.1c-.3-.2-.5-.6-.4-.9.3-.9.2-1.8-.1-2.7-.6-1.9-2.7-3-4.7-2.3-1.9.6-3 2.7-2.3 4.7.1.2.1.3.2.5.3.8.9 1.4 1.6 1.8.3.1.3.5.2.7l-4.3 6.4m.6.3c0 1-.8 1.8-1.8 1.8s-1.8-.8-1.8-1.8.8-1.8 1.8-1.8 1.8.8 1.8 1.8zm5.9-11.4 3.6 6.3c.8-.3 1.6-.3 2.4-.2 3 .4 5.1 3.1 4.8 6.1-.4 3-3.1 5.1-6.1 4.8-1.4-.2-2.7-.9-3.6-1.9-.3-.4-.2-.9.2-1.2l.2-.1c.3-.2.7-.1 1 .1.6.7 1.5 1.1 2.4 1.2 2 .4 3.9-.9 4.3-2.9s-.9-3.9-2.9-4.3c-.2 0-.4-.1-.5-.1-.8-.1-1.7.1-2.4.5-.2.2-.6.1-.7-.2l-4.1-7.1m6.4 11.6h-7.3c-.2.8-.5 1.5-1 2.2-1.8 2.4-5.3 2.9-7.7 1.1-2.4-1.8-2.9-5.3-1.1-7.7.9-1.1 2.1-1.9 3.5-2.2.5-.1.9.3.9.7v.2c0 .4-.2.7-.6.8-.9.2-1.7.7-2.3 1.5-1.4 1.5-1.2 3.9.3 5.2 1.5 1.4 3.9 1.2 5.2-.3.1-.1.2-.3.3-.4.6-.7.8-1.5.8-2.4 0-.3.2-.5.5-.5l7.6-.1m3.1.8c0 1-.8 1.8-1.8 1.8s-1.8-.8-1.8-1.8.8-1.8 1.8-1.8 1.8.8 1.8 1.8zm-6-11.1c0 1-.8 1.8-1.8 1.8s-1.8-.8-1.8-1.8.8-1.8 1.8-1.8 1.8.8 1.8 1.8z"></path>
                                </svg>
                                <span><strong><?php echo number_format($stats['triggers_count']); ?></strong> Trigger<?php echo $stats['triggers_count'] != 1 ? 's' : ''; ?></span>
                            </li>
                        <?php endif; ?>
                        <?php if (@$stats['sops_count'] > 0): ?>
                            <li>
                                <svg viewBox="0 0 22 22" xmlns="http://www.w3.org/2000/svg">
                                    <path d="m8 16.6c0 1.8-1.4 3.2-3.2 3.2s-3.2-1.4-3.2-3.2 1.4-3.2 3.2-3.2 3.2 1.4 3.2 3.2z" fill="none" stroke="currentColor" stroke-width="1.7" />
                                    <path d="m11.4 3.7c-.4 0-.7.4-.7.7s.4.7.7.7h8.3c.4 0 .7-.4.7-.7s-.4-.7-.7-.7z" fill="currentColor" stroke="currentColor" stroke-width="0.5" />
                                    <path d="m11.4 6.6c-.4 0-.7.4-.7.7s.4.7.7.7h4.4c.4 0 .7-.4.7-.7s-.4-.7-.7-.7z" fill="currentColor" stroke="currentColor" stroke-width="0.25" />
                                    <path d="m4.8 8.6c-.4 0-.9.4-.9.9v3.1c0 .4.4.9.9.9s.9-.4.9-.9v-3.1c0-.5-.4-.9-.9-.9z" fill="currentColor" stroke="currentColor" stroke-width="0.25" />
                                    <path d="m10.7 15.1c0-.4.4-.7.7-.7h8.3c.4 0 .7.4.7.7s-.4.7-.7.7h-8.3c-.4 0-.7-.3-.7-.7z" fill="currentColor" stroke="currentColor" stroke-width="0.25" />
                                    <path d="m11.4 17.4c-.4 0-.7.4-.7.7s.4.7.7.7h4.4c.4 0 .7-.4.7-.7s-.4-.7-.7-.7z" fill="currentColor" stroke="currentColor" stroke-width="0.25" />
                                    <path d="m8 5.4c0 1.8-1.4 3.2-3.2 3.2s-3.2-1.4-3.2-3.2 1.4-3.2 3.2-3.2 3.2 1.4 3.2 3.2z" fill="none" stroke="currentColor" stroke-width="1.7" />
                                </svg>
                                <span><strong><?php echo number_format($stats['sops_count']); ?></strong> SOP<?php echo $stats['sops_count'] != 1 ? 's' : ''; ?></span>
                            </li>
                        <?php endif; ?>
                    </ul>

                    <div class="relative -mr-1.5" x-data="{installModal: false}" @click.away="installModal=false">
                        <button class="btn-sm-outline border-green-700 bg-green-700 hover:bg-green-800 text-white!" @click.prevent="installModal=true;">↓ Install&nbsp;</button>
                        <div
                            x-show="installModal"
                            @click.away="installModal=false"
                            x-transition:enter="ease-out duration-200"
                            x-transition:enter-start="-translate-y-2"
                            x-transition:enter-end="translate-y-0"
                            class="absolute right-0 z-50 mt-1"
                            x-cloak>
                            <div class="dropmenu min-w-sm px-4! shadow-none! lg:shadow-xl! text-left">
                                <h3 class="text-base font-semibold mt-4">Install this formation:</h3>
                                <div class="mt-2 flex items-center space-x-1 justify-between">
                                    <div class="text-xs border w-full inset-shadow-xs font-mono rounded-sm py-2 px-3 cursor-pointer bg-black/2 dark:bg-black/5" onclick="this.select();">
                                        <span class="text-green-600">muxi</span> pull <span class="text-blue-900 dark:text-blue-300">@<?php echo htmlspecialchars($formation['registry_username']); ?>/<?php echo htmlspecialchars($formation['name']); ?></span>
                                    </div>
                                    <button class="btn-ghost hover:scale-105" onmousedown="this.classList.add('scale-90!')" onmouseup="this.classList.remove('scale-90!')" onclick="navigator.clipboard.writeText('muxi pull @<?php echo htmlspecialchars($formation['registry_username']); ?>/<?php echo htmlspecialchars($formation['name']); ?>')">
                                        <svg focusable="false" viewBox="0 0 16 16" fill="currentColor" class="size-4">
                                            <path d="M0 6.75C0 5.784.784 5 1.75 5h1.5a.75.75 0 0 1 0 1.5h-1.5a.25.25 0 0 0-.25.25v7.5c0 .138.112.25.25.25h7.5a.25.25 0 0 0 .25-.25v-1.5a.75.75 0 0 1 1.5 0v1.5A1.75 1.75 0 0 1 9.25 16h-7.5A1.75 1.75 0 0 1 0 14.25Z"></path>
                                            <path d="M5 1.75C5 .784 5.784 0 6.75 0h7.5C15.216 0 16 .784 16 1.75v7.5A1.75 1.75 0 0 1 14.25 11h-7.5A1.75 1.75 0 0 1 5 9.25Zm1.75-.25a.25.25 0 0 0-.25.25v7.5c0 .138.112.25.25.25h7.5a.25.25 0 0 0 .25-.25v-7.5a.25.25 0 0 0-.25-.25Z"></path>
                                        </svg>
                                    </button>
                                </div>
                                <p class="text-xs text-neutral-500 dark:text-neutral-400 antialiased leading-normal">Copy &amp; paste this commans into your terminal to install it.<br />* Make sure you have the <a href="https://muxi.org/docs/cli/" target="_blank" class="link">MUXI CLI Tool</a> installed.</p>
                            </div>
                        </div>
                    </div>
                </header>
            </div>

            <!-- README -->
            <article class="p-5 pt-2 pb-8 [&>h1]:text-3xl!">
                <?php echo tiny::markdown()->transform($formation['readme_md']); ?>
            </article>
        </div>


        <?php if (!empty($downloadChart) && is_array($downloadChart) && count($downloadChart) > 0): ?>
            <?php
            // Calculate total for display
            $totalRecent = 0;
            foreach ($downloadChart as $day) {
                if (isset($day['downloads'])) {
                    $totalRecent += $day['downloads'];
                }
            }
            ?>
            <!-- Download Chart -->
            <div class="mt-6 mb-12 ml-1 mr-1">
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-semibold my-4 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 256" class="size-4">
                            <polyline points="24 128 56 128 96 40 160 208 200 128 232 128" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></polyline>
                        </svg>
                        Downloads (Last 30 Days)
                    </h3>
                    <div class="text-sm flex items-center gap-2">
                        <span class="font-semibold"><?php echo number_format($totalRecent); ?></span> total pulls
                    </div>
                </div>
                <div style="height: 200px;">
                    <canvas id="downloadChart"></canvas>
                </div>
            </div>
            <hr>
        <?php endif; ?>

        <!-- install -->
        <div class="mt-8 ml-1 mr-1">
            <h3 class="text-base font-semibold my-4 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="size-4">
                    <path d="M3.2 18C1.88484 17.235 1 15.8051 1 14.1674C1 12.1053 2.40285 10.3727 4.30122 9.88197C4.30041 9.83571 4.3 9.78935 4.3 9.7429C4.3 5.46661 7.74741 2 12 2C15.6211 2 18.6584 4.51348 19.4806 7.90009C21.5395 8.69955 23 10.7089 23 13.0613C23 14.8707 22.1359 16.4772 20.8 17.4862M12 13V22M12 22L15.5 18.5M12 22L8.5 18.5" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
                Install this formation
            </h3>
            <div class="mt-2 flex items-center space-x-1 justify-between">
                <div class="text-xs border w-full inset-shadow-xs font-mono rounded-sm py-2 px-3 cursor-pointer bg-black/2 dark:bg-black/5" onclick="this.select();">
                    <span class="text-green-600">muxi</span> pull <span class="text-blue-900 dark:text-blue-300">@<?php echo htmlspecialchars($formation['registry_username']); ?>/<?php echo htmlspecialchars($formation['name']); ?></span>
                </div>
                <button class="btn-ghost hover:scale-105" onmousedown="this.classList.add('scale-90!')" onmouseup="this.classList.remove('scale-90!')" onclick="navigator.clipboard.writeText('muxi pull @<?php echo htmlspecialchars($formation['registry_username']); ?>/<?php echo htmlspecialchars($formation['name']); ?>')">
                    <svg focusable="false" viewBox="0 0 16 16" fill="currentColor" class="size-4">
                        <path d="M0 6.75C0 5.784.784 5 1.75 5h1.5a.75.75 0 0 1 0 1.5h-1.5a.25.25 0 0 0-.25.25v7.5c0 .138.112.25.25.25h7.5a.25.25 0 0 0 .25-.25v-1.5a.75.75 0 0 1 1.5 0v1.5A1.75 1.75 0 0 1 9.25 16h-7.5A1.75 1.75 0 0 1 0 14.25Z"></path>
                        <path d="M5 1.75C5 .784 5.784 0 6.75 0h7.5C15.216 0 16 .784 16 1.75v7.5A1.75 1.75 0 0 1 14.25 11h-7.5A1.75 1.75 0 0 1 5 9.25Zm1.75-.25a.25.25 0 0 0-.25.25v7.5c0 .138.112.25.25.25h7.5a.25.25 0 0 0 .25-.25v-7.5a.25.25 0 0 0-.25-.25Z"></path>
                    </svg>
                </button>
            </div>
            <p class="text-xs text-neutral-500 dark:text-neutral-400 antialiased leading-normal">Copy &amp; paste this commans into your terminal to install it.<br />* Make sure you have the <a href="https://muxi.org/docs/cli/" target="_blank" class="link">MUXI CLI Tool</a> installed.</p>
        </div>

    </div>

    <div class="col-span-3">
        <h3 class="text-base font-semibold">About</h3>
        <p><?php echo htmlspecialchars($formation['description']); ?></p>

        <hr class="my-6">

        <ul class="[&>li]:pl-px [&>li]:flex [&>li]:items-center [&>li]:space-x-3 [&>li>a]:flex [&>li>a]:items-center [&>li>a]:space-x-3 mt-4 [&>li>svg]:size-4 [&>li>a>svg]:size-4 [&>li>strong]:font-semibold text-sm [&>li]:opacity-80 [&>li:hover]:opacity-100">

            <li>
                <a href="<?php echo $homeURL; ?>@<?php echo $formation['registry_username']; ?>">
                    <img src="<?php echo $formation['github_avatar']; ?>" class="size-4.5 -ml-[1.5px] rounded-full">
                    <span class="-ml-[1px]">By @<?php echo htmlspecialchars($formation['registry_username']); ?></span>
                </a>
            </li>

            <li><a href="#readme" class="text-gold-on-hover">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none">
                        <path d="M14 2.5V4.80003C14 5.92013 14 6.48019 14.218 6.90801C14.4097 7.28433 14.7157 7.5903 15.092 7.78204C15.5198 8.00003 16.0799 8.00003 17.2 8.00003H19.5M9 12H15M9 16H13M20 10.3137V14C20 16.8003 20 18.2004 19.455 19.27C18.9757 20.2108 18.2108 20.9757 17.27 21.455C16.2004 22 14.8003 22 12 22V22C9.19974 22 7.79961 22 6.73005 21.455C5.78924 20.9757 5.02433 20.2108 4.54497 19.27C4 18.2004 4 16.8003 4 14V9.77817C4 7.18697 4 5.89136 4.46859 4.88663C4.96536 3.82147 5.82147 2.96536 6.88663 2.46859C7.89136 2 9.18697 2 11.7782 2V2C12.9105 2 13.4766 2 14.0113 2.11855C14.5806 2.24479 15.1235 2.46965 15.6153 2.78296C16.0772 3.07721 16.4775 3.47753 17.2782 4.27817L17.6569 4.65685C18.5216 5.52161 18.954 5.95399 19.2632 6.45858C19.5373 6.90594 19.7394 7.39366 19.8618 7.90384C20 8.47928 20 9.09076 20 10.3137Z" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"></path>
                    </svg>
                    <span>Readme</span>
                </a></li>

            <?php if (!$formation['license']): ?>
                <li><a target="_blank" href="https://github.com/<?php echo htmlspecialchars($formation['github_repo']);  ?>/blob/main/LICENSE" class="text-gold-on-hover">
                        <svg viewBox="0 0 16 16" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                            <path d="M8.75.75V2h.985c.304 0 .603.08.867.231l1.29.736c.038.022.08.033.124.033h2.234a.75.75 0 0 1 0 1.5h-.427l2.111 4.692a.75.75 0 0 1-.154.838l-.53-.53.529.531-.001.002-.002.002-.006.006-.006.005-.01.01-.045.04c-.21.176-.441.327-.686.45C14.556 10.78 13.88 11 13 11a4.498 4.498 0 0 1-2.023-.454 3.544 3.544 0 0 1-.686-.45l-.045-.04-.016-.015-.006-.006-.004-.004v-.001a.75.75 0 0 1-.154-.838L12.178 4.5h-.162c-.305 0-.604-.079-.868-.231l-1.29-.736a.245.245 0 0 0-.124-.033H8.75V13h2.5a.75.75 0 0 1 0 1.5h-6.5a.75.75 0 0 1 0-1.5h2.5V3.5h-.984a.245.245 0 0 0-.124.033l-1.289.737c-.265.15-.564.23-.869.23h-.162l2.112 4.692a.75.75 0 0 1-.154.838l-.53-.53.529.531-.001.002-.002.002-.006.006-.016.015-.045.04c-.21.176-.441.327-.686.45C4.556 10.78 3.88 11 3 11a4.498 4.498 0 0 1-2.023-.454 3.544 3.544 0 0 1-.686-.45l-.045-.04-.016-.015-.006-.006-.004-.004v-.001a.75.75 0 0 1-.154-.838L2.178 4.5H1.75a.75.75 0 0 1 0-1.5h2.234a.249.249 0 0 0 .125-.033l1.288-.737c.265-.15.564-.23.869-.23h.984V.75a.75.75 0 0 1 1.5 0Zm2.945 8.477c.285.135.718.273 1.305.273s1.02-.138 1.305-.273L13 6.327Zm-10 0c.285.135.718.273 1.305.273s1.02-.138 1.305-.273L3 6.327Z"></path>
                        </svg>
                        <span>MIT License</span>
                    </a></li>
            <?php endif; ?>

            <li><a href="https://github.com/<?php echo htmlspecialchars($formation['github_repo']);  ?>" target="_blank" class="text-gold-on-hover">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor" class="size-6">
                        <path d="M14.5094 20.9056C14.5198 20.402 14.5349 19.6585 14.5349 19C14.5349 18 13.8548 17.0818 13.8548 17.0818C16.1129 16.834 18.4919 15.5037 18.4919 11.5393C18.4919 10.383 18.0887 9.84616 17.4435 9.10284L17.4579 9.0525C17.5588 8.7032 17.8583 7.66631 17.3226 6.29474C16.4758 6.00567 14.5403 7.40972 14.5403 7.40972C13.7339 7.16195 12.8871 7.07936 12 7.07936C11.1532 7.07936 10.3065 7.16195 9.5 7.40972C9.5 7.40972 7.52419 6.04697 6.71774 6.29474C6.15323 7.74009 6.47581 8.81377 6.59677 9.10284C5.95161 9.84616 5.62903 10.383 5.62903 11.5393C5.62903 15.5037 7.92742 16.834 10.1855 17.0818C10.1855 17.0818 9.5 17.8624 9.5 18.9312V20.9082V22.4578C4.76861 21.3309 1.25 17.0764 1.25 12C1.25 6.06294 6.06294 1.25 12 1.25C17.9371 1.25 22.75 6.06294 22.75 12C22.75 17.073 19.2361 21.3252 14.5094 22.4555V20.9056Z"></path>
                    </svg>
                    <span>View on GitHub</span>
                </a></li>

            <li><a href="mailto:dmca@varops.com?subject=MUXI%20Registry%20abuse%20or%20spam%20-%20@<?php echo htmlspecialchars($formation['registry_username']); ?>/<?php echo urlencode($formation['name']); ?>&body=Please%20provide%20as%20much%20detail%20as%20possible%20about%20the%20formation%20you're%20reporting%20(@<?php echo htmlspecialchars($formation['registry_username']); ?>/<?php echo urlencode($formation['name']); ?>).%20It's%20especially%20helpful%20to%20include%20specific%20examples%20or%20screenshots." class="hover:text-red-600">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 256">
                        <path d="M142.41,40.22l87.46,151.87C236,202.79,228.08,216,215.46,216H40.54C27.92,216,20,202.79,26.13,192.09L113.59,40.22C119.89,29.26,136.11,29.26,142.41,40.22Z" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="20"></path>
                        <line x1="128" y1="144" x2="128" y2="104" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="20"></line>
                        <circle cx="128" cy="180" r="12" fill="currentColor"></circle>
                    </svg>
                    <span>Report formation</span>
                </a></li>
        </ul>

        <hr class="my-8">

        <?php if (!empty($weeklyChart) && is_array($weeklyChart) && count($weeklyChart) > 0): ?>
            <div>
                <h3 class="text-md font-semibold -mt-4 -mb-2 flex items-center space-x-2">Last 7 days activity</h3>
                <div style="height: 40px;">
                    <canvas id="weeklyChart"></canvas>
                </div>
            </div>
            <hr class="mt-5 mb-8">
        <?php endif; ?>

        <?php if (!empty($versions)): ?>
            <?php $version = $versions[0]; ?>
            <h3 class="text-base font-semibold mt-6 flex items-center space-x-2"><span>Releases</span> <span class="border rounded-full text-[10px]! subpixel-antialiased bg-neutral-200 dark:bg-neutral-800 leading-none size-4.5 flex items-center justify-center"><?php echo count($versions); ?></span></h3>
            <div class="mt-4">
                <div class="text-sm flex items-center justify-between">
                    <div class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" class="text-green-600 size-4.5 mr-1">
                            <path d="M3 10.349C3 11.3274 3 11.8166 3.11052 12.2769C3.20851 12.6851 3.37012 13.0753 3.58944 13.4331C3.83681 13.8368 4.18271 14.1827 4.87451 14.8745L8.47452 18.4745C10.0586 20.0586 10.8506 20.8506 11.7639 21.1474C12.5673 21.4084 13.4327 21.4084 14.2361 21.1474C15.1494 20.8506 15.9414 20.0586 17.5255 18.4745L18.4745 17.5255C20.0586 15.9414 20.8506 15.1494 21.1474 14.2361C21.4084 13.4327 21.4084 12.5673 21.1474 11.7639C20.8506 10.8506 20.0586 10.0586 18.4745 8.47452L14.8745 4.87452C14.1827 4.18271 13.8368 3.83681 13.4331 3.58944C13.0753 3.37012 12.6851 3.20851 12.2769 3.11052C11.8166 3 11.3274 3 10.349 3H9.4C7.15979 3 6.03968 3 5.18404 3.43597C4.43139 3.81947 3.81947 4.43139 3.43597 5.18404C3 6.03968 3 7.15979 3 9.4V10.349Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                            <path d="M12 10C12 11.1046 11.1046 12 10 12C8.89543 12 8 11.1046 8 10C8 8.89543 8.89543 8 10 8C11.1046 8 12 8.89543 12 10Z" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"></path>
                        </svg>
                        <span class="font-semibold">v<?php echo htmlspecialchars($version['version']); ?></span>
                        <span class="border border-green-600 text-green-700 rounded-full text-[11px]! subpixel-antialiased leading-none py-[3px] px-1.5 ml-2">Latest</span>
                    </div>
                    <span class="text-xs opacity-50">
                        <?php echo date('M j, Y', strtotime($version['published_at'])); ?>
                    </span>
                </div>
                <?php if ($version['release_notes']): ?>
                    <p class="text-xs opacity-80 mt-2! text-pretty">
                        <?php echo htmlspecialchars(substr($version['release_notes'], 0, 60)) ?><?php echo strlen($version['release_notes']) > 60 ? '...' : ''; ?>
                    </p>
                <?php endif; ?>
            </div>


            <a href="https://github.com/<?php echo htmlspecialchars($formation['github_repo']); ?>/releases" target="_blank"
                class="text-sm text-blue-500 opacity-80 hover:opacity-100 font-medium">
                <?php if (count($versions) == 1): ?>View release → <?php else: ?>+ <?php echo count($versions) - 1; ?> releases →
            <?php endif; ?>
            </a>
        <?php endif; ?>


        <?php if ($canDelete): ?>
            <hr class="my-8">
            <!-- Danger Zone -->
            <div x-data="{ showDeleteModal: false, deleteGithub: false, confirmName: '', deleting: false }">
                <h3 class="text-base font-semibold mt-6 text-red-600 dark:text-red-500">Danger Zone</h3>
                <p class="mt-2! text-red-500 dark:text-red-400">
                    Permanently delete this formation from the registry, and optionally from GitHub.
                </p>
                <button @click="showDeleteModal = true" class="btn-destructive">Delete Formation</button>

                <!-- Delete Modal -->
                <div
                    x-show="showDeleteModal"
                    x-cloak
                    class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
                    @click.self="showDeleteModal = false">
                    <div class="card shadow-2xl! max-w-md w-full p-6 pb-4" @click.stop>
                        <h3 class="text-lg font-semibold">Delete confirmation</h3>

                        <div class="alert-destructive">
                            <h2 class="text-base font-sans! flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class=" mr-1 size-4">
                                    <circle cx="12" cy="12" r="10" />
                                    <line x1="12" x2="12" y1="8" y2="12" />
                                    <line x1="12" x2="12.01" y1="16" y2="16" />
                                </svg>
                                Warning: This action cannot be undone.
                            </h2>
                            <section class="block leading-normal opacity-90 pt-1 pl-px">
                                <span class="subpixel-antialiased whitespace-nowrap font-mono">@<?php echo htmlspecialchars($formation['registry_username']); ?>/<?php echo htmlspecialchars($formation['name']); ?></span>
                                will be permanently deleted from the registry.
                            </section>
                        </div>

                        <div class="">
                            <label>
                                Type <strong class="font-mono"><?= htmlspecialchars($formation['name']) ?></strong> to confirm:
                            </label>
                            <input
                                type="text"
                                x-model="confirmName"
                                class="input mt-1"
                                autocomplete="off"
                                placeholder="<?php echo htmlspecialchars($formation['name']); ?>" />
                        </div>

                        <label class="label gap-2 -my-2!">
                            <input type="checkbox" x-model="deleteGithub" class="input">
                            Also delete the GitHub repository
                        </label>
                        <div class="-mt-3 text-xs opacity-80 pl-6">(<?php echo htmlspecialchars($formation['github_repo']); ?>)</div>

                        <hr>
                        <div class="flex flex-col space-y-2">
                            <button
                                @click="
                                if (confirmName !== '<?= htmlspecialchars($formation['name']) ?>') {
                                    alert('Please type the formation name to confirm');
                                    return;
                                }
                                deleting = true;
                                fetch('/api/formations/@<?= htmlspecialchars($formation['registry_username']) ?>/<?= htmlspecialchars($formation['name']) ?>' + (deleteGithub ? '?delete_github=true' : ''), {
                                    method: 'DELETE',
                                    credentials: 'same-origin'
                                })
                                .then(async r => {
                                    const text = await r.text();
                                    try {
                                        return JSON.parse(text);
                                    } catch (e) {
                                        console.error('Response:', text);
                                        throw new Error('Server error: ' + text.substring(0, 200));
                                    }
                                })
                                .then(data => {
                                    console.log('Delete response:', data);
                                    if (data.error) {
                                        alert('Error: ' + data.message);
                                        deleting = false;
                                    } else {
                                        if (data.github_delete_requested && !data.github_deleted) {
                                            alert('Formation deleted but GitHub repo deletion failed: ' + (data.github_error || 'Unknown error'));
                                        }
                                        window.location.href = '/account';
                                    }
                                })
                                .catch(err => {
                                    console.error('Delete error:', err);
                                    alert('Error: ' + err.message);
                                    deleting = false;
                                });
                            "
                                :disabled="confirmName !== '<?php echo htmlspecialchars($formation['name']); ?>' || deleting"
                                class="btn-destructive">
                                <span x-show="!deleting">Delete Formation</span>
                                <span x-show="deleting">Deleting...</span>
                            </button>
                            <button class="btn-ghost" @click="showDeleteModal = false">Cancel</button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>





<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Theme-aware grid color
        const getGridColor = () => document.documentElement.classList.contains('dark') ? '#222222' : '#eeeeee';
        let gridColor = getGridColor();
        const charts = [];

        // Watch for theme changes
        const observer = new MutationObserver(() => {
            const newGridColor = getGridColor();
            if (newGridColor !== gridColor) {
                gridColor = newGridColor;
                charts.forEach(chart => {
                    if (chart.options.scales?.y?.grid) {
                        chart.options.scales.y.grid.color = gridColor;
                    }
                    chart.data.datasets.forEach(ds => {
                        if (ds.pointBorderColor) ds.pointBorderColor = gridColor;
                    });
                    chart.update('none');
                });
            }
        });
        observer.observe(document.documentElement, {
            attributes: true,
            attributeFilter: ['class']
        });

        // Weekly Chart Data
        <?php if (!empty($weeklyChart) && count($weeklyChart) > 0): ?>
            const weeklyData = <?= json_encode($weeklyChart) ?>;
            const weeklyLabels = weeklyData.map(d => {
                const date = new Date(d.day);
                return date.toLocaleDateString('en-US', {
                    weekday: 'short'
                });
            });
            const weeklyDownloads = weeklyData.map(d => d.downloads);

            charts.push(new Chart(document.getElementById('weeklyChart'), {
                type: 'line',
                data: {
                    labels: weeklyLabels,
                    datasets: [{
                        data: weeklyDownloads,
                        borderColor: '#c88b45',
                        borderWidth: 2,
                        fill: false,
                        tension: 0.4,
                        pointRadius: 3,
                        pointHoverRadius: 5,
                        pointBackgroundColor: '#c88b45',
                        pointBorderColor: gridColor,
                        pointBorderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            displayColors: false,
                            callbacks: {
                                title: function() {
                                    return '';
                                },
                                label: function(context) {
                                    const day = weeklyLabels[context.dataIndex];
                                    const count = context.parsed.y;
                                    return day + ': ' + count + ' pull' + (count !== 1 ? 's' : '');
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            suggestedMax: 1,
                            display: false
                        },
                        x: {
                            display: false
                        }
                    }
                }
            }));
        <?php endif; ?>

        // Main Download Chart Data
        <?php if (!empty($downloadChart) && count($downloadChart) > 0): ?>
            const downloadData = <?= json_encode($downloadChart) ?>;
            const downloadLabels = downloadData.map(d => {
                const date = new Date(d.day);
                return date.toLocaleDateString('en-US', {
                    month: 'short',
                    day: 'numeric'
                });
            });
            const downloads = downloadData.map(d => d.downloads);
            const hasData = downloads.some(d => d > 0);

            charts.push(new Chart(document.getElementById('downloadChart'), {
                type: hasData ? 'bar' : 'line',
                data: {
                    labels: downloadLabels,
                    datasets: [{
                        label: 'Downloads',
                        data: downloads,
                        backgroundColor: hasData ? '#c88b45' : 'transparent',
                        borderColor: '#c88b45',
                        borderWidth: hasData ? 0 : 2,
                        borderRadius: hasData ? 4 : 0,
                        tension: 0.1,
                        pointRadius: hasData ? 0 : 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.parsed.y + ' pull' + (context.parsed.y !== 1 ? 's' : '');
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            suggestedMax: 1,
                            ticks: {
                                font: {
                                    size: 11
                                },
                                callback: function(value) {
                                    return Number.isInteger(value) ? value : '';
                                }
                            },
                            grid: {
                                color: gridColor
                            }
                        },
                        x: {
                            ticks: {
                                font: {
                                    size: 10
                                },
                                maxRotation: 0,
                                autoSkip: true,
                                maxTicksLimit: 15
                            },
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            }));
        <?php endif; ?>
    });
</script>

<?php tiny::layout()->default('/'); ?>
