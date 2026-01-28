<?php
echo '<!-- v. ' . $_SERVER['APP_VERSION'] . " -->\n";

$titleAppend = ' | MUXI Registry';
$defaultTitle = 'Publish and discover AI agent formations';
$defaultDescription = 'MUXI Registry is Docker Hub for AI formations - a platform where developers share, discover, and deploy complete, AI agent formations instantly. Stop building from scratch. Start with battle-tested formations from the community.';
$defaultRobots = 'noindex, nofollow';
?>
<!DOCTYPE html>
<html lang="en" x-data="{showMenu: false, showAnnouncement: <?php
    echo isset($_COOKIE['hide_announcement']) ? 'false' : 'true';
?>}">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no">

    <script src="<?php tiny::homeURL('/analytics.js'); ?>"></script>
    <?php if (@$_SERVER['ENV'] != 'local' && @$_SERVER['SENTRY_FRONTEND']): ?>
        <script src="https://js.sentry-cdn.com/<?php echo $_SERVER['SENTRY_FRONTEND']; ?>.min.js" crossorigin="anonymous"></script>
    <?php endif; ?>

    <meta name="robots" content="<?php echo tiny::layout()->props('robots') ? strip_tags(tiny::layout()->props('robots')) : $defaultRobots ?>">
    <link rel="llm-context" href="<?php tiny::homeURL('llms.txt'); ?>" type="text/plain" />
    <?php if (@tiny::layout()->props('alternate')): ?>
        <link rel="alternate" type="text/markdown" href="<?php echo tiny::layout()->props('alternate'); ?>">
    <?php endif; ?>

    <title><?php echo tiny::layout()->props('title') ? htmlspecialchars(strip_tags(tiny::layout()->props('title'))) : $defaultTitle; ?><?php echo $titleAppend; ?></title>
    <meta name="description" content="<?php echo tiny::layout()->props('description') ? htmlspecialchars(strip_tags(tiny::layout()->props('description'))) : $defaultDescription; ?>">

    <link rel="stylesheet" type="text/css" href="<?php tiny::staticURL((@$_SERVER['ENV'] == 'local' ? '/css/style.css?' . time() : '/css/style.min.css')); ?>" media="all">
    <link rel="preload" href="<?php tiny::staticURL('/css/fonts.min.css'); ?>" as="style" onload="this.rel='stylesheet'">

    <!-- favicon for light and dark mode -->
    <link href="<?php tiny::staticURL('/favicon-dark.png'); ?>" rel="icon" type="image/png" media="(prefers-color-scheme: light)">
    <link href="<?php tiny::staticURL('/favicon-light.png'); ?>" rel="icon" type="image/png" media="(prefers-color-scheme: dark)">

    <!-- apple touch icon for light and dark mode -->
    <link rel="apple-touch-icon" sizes="512x512" href="<?php tiny::staticURL('/apple-touch-icon.png'); ?>"> <!-- fallback -->
    <link rel="apple-touch-icon" sizes="512x512" href="<?php tiny::staticURL('/apple-touch-icon-light.png'); ?>" media="(prefers-color-scheme: light)">
    <link rel="apple-touch-icon" sizes="512x512" href="<?php tiny::staticURL('/apple-touch-icon-dark.png'); ?>" media="(prefers-color-scheme: dark)">

    <meta name="format-detection" content="telephone=no">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="MUXI">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="theme-color" content="#faf9f5" media="(prefers-color-scheme: light)">
    <meta name="theme-color" content="#131215" media="(prefers-color-scheme: dark)">

    <!-- Open Graph Meta Tags -->
    <meta property="og:url" content="<?php echo tiny::router()->permalink; ?>" />
    <meta property="og:type" content="website" />
    <meta property="og:title" content="<?php echo tiny::layout()->props('title') ? htmlspecialchars(strip_tags(tiny::layout()->props('title'))) : $defaultTitle; ?><?php echo $titleAppend; ?>" />
    <meta property="og:description" content="<?php echo tiny::layout()->props('description') ? htmlspecialchars(strip_tags(tiny::layout()->props('description'))) : $defaultDescription; ?>" />
    <meta property="og:image" content="<?php tiny::layout()->props('ogImage') ?: tiny::staticURL('img/ogcard.webp'); ?>" />

    <!-- Twitter Meta Tags -->
    <meta name="twitter:card" content="summary_large_image" />
    <meta property="twitter:domain" content="muxi.org" />
    <meta property="twitter:site" content="@muxi_ai" />
    <meta property="twitter:url" content="<?php echo tiny::router()->permalink; ?>" />
    <meta name="twitter:title" content="<?php echo tiny::layout()->props('title') ? htmlspecialchars(strip_tags(tiny::layout()->props('title'))) : 'Open-source infrastructure for AI agents' ?><?php echo $titleAppend; ?>" />
    <meta name="twitter:description" content="<?php echo tiny::layout()->props('description') ? htmlspecialchars(strip_tags(tiny::layout()->props('description'))) : $defaultDescription; ?>" />
    <meta name="twitter:image" content="<?php tiny::layout()->props('ogImage') ?: tiny::staticURL('img/ogcard.webp'); ?>" />

    <script type="speculationrules">{ "prefetch": [{ "where": { "href_matches": "/*" }, "eagerness": "moderate" }] }</script>
    <script defer src="<?php tiny::staticURL('/js/alpine.combo.min.js'); ?>"></script>
    <script defer src="<?php tiny::staticURL('/js/htmx.min.js'); ?>"></script>
    <script src="<?php tiny::staticURL('/js/theme.min.js'); ?>"></script>
    <?php /* <script src="<?php tiny::staticURL('/js/app.min.js'); ?>"></script> */ ?>

    <script>window.Prism = window.Prism || {}; window.Prism.manual = true;</script>
    <script defer src="<?php tiny::staticURL('/js/prism.min.js'); ?>"></script>
    <?php if (tiny::layout()->props('scripts')):
        foreach (tiny::layout()->props('scripts') as $script): ?>
            <script src="<?php tiny::staticURL('/js/' . $script); ?>.min.js"></script>
    <?php endforeach;
    endif; ?>
    <?php if (tiny::layout()->props('styles')):
        foreach (tiny::layout()->props('styles') as $style): ?>
            <link rel="stylesheet" href="<?php tiny::staticURL('/css/' . $style); ?>.min.css">
    <?php endforeach;
    endif; ?>
</head>

<body class="mx-10 <?php echo tiny::layout()->props('bodyClass') ? strip_tags(tiny::layout()->props('bodyClass')) : ''; ?>" :class="showMenu ? 'show-menu' : ''">
    <?php if (!tiny::layout()->props('emptyLayout')): ?>
        <template x-if="showAnnouncement">
            <?php tiny::render('_announcement'); ?>
        </template>
        <div class="bg-black/10 backdrop-blur-xs fixed top-20 inset-0 z-10" :class="showMenu ? 'block lg:hidden' : 'hidden'"></div>
        <header id="header">
            <nav aria-label="Global">
                <div class="lg:flex items-center justify-between gap-6">
                    <a href="<?php tiny::homeURL(); ?>" @contextmenu="logoContextMenu.toggle(event)" @click.outside="logoContextMenu.hide()" class="flex items-center space-x-2">
                        <svg viewBox="0 0 1314.79 600" xmlns="http://www.w3.org/2000/svg" class="h-11" id="muxi-wordmark">
                            <g fill="currentColor" class="text-[#281e19] dark:text-[#f5e4d1]">
                                <path d="m760.73 381.32h-31.14l-56.3-166.19v166.19h-35.64v-215.01h52.7l55.4 164.7 55.4-164.7h51.51v215.01h-35.64v-166.19l-56.3 166.19z" />
                                <path d="m966.64 384.32c-16.97 0-31.99-3.24-45.07-9.73-13.08-6.48-23.26-16.27-30.54-29.35-7.29-13.07-10.93-29.5-10.93-49.26v-129.66h38.33v128.46c0 12.98 1.99 23.61 5.99 31.89 3.99 8.29 9.63 14.33 16.92 18.12s15.72 5.69 25.3 5.69 18.27-1.89 25.45-5.69c7.19-3.79 12.83-9.83 16.92-18.12 4.09-8.28 6.14-18.91 6.14-31.89v-128.46h38.33v129.66c0 19.76-3.65 36.19-10.93 49.26-7.29 13.08-17.47 22.86-30.54 29.35-13.08 6.49-28.2 9.73-45.37 9.73z" />
                                <path d="m1260.8 166.32-72.17 105.71 75.16 109.3h-43.12l-54.8-80.85-55.1 80.85h-45.92l79.16-109.3-72.47-105.71h43.12l52.1 76.96 52.11-76.96h41.92z" />
                                <path d="m1314.79 166.32v215.01h-38.33v-215.01z" />
                            </g>
                            <g fill="currentColor" class="text-[#c88b45] dark:text-[#e8a550]">
                                <path d="m439.7 71.99-62.27 35.88-58.06 137.03-80.57 46.53v61.4l118.29-68.1 28.59-55.85v103.3h-19.9v19.9h19.9v19.9h-19.9v19.9h19.9v34.57l54.02-31.17v-146.5h-19.9v-19.9h19.9v-19.9h-19.9v-19.9h19.9v-19.9h-19.9v-19.9h19.9z" opacity=".2" />
                                <path d="m286.05 241.18-125.06-95.96-68.12 39.51v110.49h-19.9v19.9h19.9v19.9h-19.9v19.9h19.9v19.9h-19.9v19.9h19.9l.47 176.45 52.59-30.33v-328.8l82.92 62.14z" opacity=".2" />
                                <path d="m297.45 43.44-58.46 138.95-77.82-60.27-79.04-44.59-82.13 47.42v269.77h19.9v19.9h-19.9v19.9h19.9v19.9h-19.9v19.9h19.9v20.85h-19.9v56.99l83.14 47.84 82.69-47.69s.19-213.5.57-213.5c.57 0 62.45 42.83 62.45 42.83l63.96-36.94v12.11s0 19.9 0 19.9v30.22s82.96 48.22 82.96 48.22l83.82-48.41v-157.96s-19.9 0-19.9 0v-19.9h19.9v-19.9s-19.9 0-19.9 0v-19.9h19.9v-19.9s-19.9 0-19.9 0v-19.9h19.9v-99.27s-86.61-50.01-86.61-50.01zm131.56 11.74-51.44 29.7-55.88-32.26 51.31-29.62zm-68.85 42.71-56.57 134.08-48.53-37.25 52.7-127.09zm79.54 51.39h-19.9v19.9h19.9v19.9h-19.9v19.9h19.9v19.9h-19.9v19.9h19.9v146.49l-54.01 31.18v-34.58h-19.9v-19.9h19.9v-19.9h-19.9v-19.9h19.9v-103.29l-28.6 55.85-118.28 68.1v-61.41l80.57-46.53 58.06-137.03 62.27-35.88v77.29zm-297.73-16.21-58.83 34.25-58.32-33.72 58.87-34.17 58.28 33.65zm144.08 108.11-57.2 33-82.92-62.14v328.8s-52.59 30.32-52.59 30.32l-.47-176.45h-19.9v-19.9h19.9v-19.9h-19.9v-19.9h19.9v-19.9h-19.9v-19.9h19.9v-110.49s68.13-39.51 68.13-39.51l125.06 95.96zm-213.08-56.81v110.85h-19.9v19.9h19.9v19.9h-19.9v19.9h19.9v19.9h-19.9v19.9h19.9s0 176.45 0 176.45l-53.07-30.5v-45.5h19.9v-20.85h-19.9v-19.9h19.9v-19.9h-19.9v-19.9h19.9v-19.9h-19.9v-240.99zm145.93 107.06v61.41s-53.07-37.24-53.07-37.24v-63.09m199.95 79.67h-19.9v19.9h19.9v19.9h-19.9v19.9h19.9v33.34l-53.07-30.04v-61.97s53.07-30.64 53.07-30.64v29.6z" />
                            </g>
                        </svg>
                        <span class="font-medium text-xs uppercase text-[#c88b45] dark:text-[#e8a550]">/ Registry</span>
                    </a>

                    <form action="<?php tiny::homeURL('/search'); ?>" method="GET" class="w-sm max-w-[calc(100vw-2rem)] top-5 z-100 group fixed left-1/2 transform -translate-x-1/2 xl:-ml-2">
                        <button class="absolute left-0 top-0 h-full px-2.5">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" class="size-5 opacity-80 hover:opacity-100 group:has-focus-within:opacity-100">
                                <path d="M15 15L21 21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                <path d="M17 10C17 13.866 13.866 17 10 17C6.13401 17 3 13.866 3 10C3 6.13401 6.13401 3 10 3C13.866 3 17 6.13401 17 10Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                            </svg>
                        </button>
                        <input class="input w-full bg-white dark:bg-black pl-10" type="search" placeholder="Search formations..." name="q" value="<?php echo urldecode(tiny::router()->query['q'] ?? ''); ?>">
                    </form>
                </div>

                <div class="flex items-center justify-between gap-4">
                    <ul class="hidden lg:block">
                        <li><a href="https://muxi.org/docs/registry" target="_blank">Docs</a></li>
                        <?php if (isset(tiny::user()->id)): ?>
                            <li><a href="https://muxi.org/docs/registry/publish-formations" target="_blank">Publish</a></li>
                        <?php endif; ?>
                        <li class="lg:border-x px-4 py-4 lg:py-0">
                            <div class="theme-switcher">
                                <button type="button" @click="window.theme.set('system')" class="theme-system">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M4 2H20V19H19V22H5V19H4V2ZM19 18V3H5V18H19ZM7 5H17V13H7V5ZM13 15H17V16H13V15Z"></path>
                                    </svg>
                                </button>
                                <button type="button" @click="window.theme.set('light')" class="theme-light">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                        <path d="M12 20L12 22M12 2L12 4M20 12H22M2 12H4M18 17.9995L19.5 19.4995M4.5 4.49949L6.00002 5.99953M18 6L19.5 4.5M4.5 19.5L6 18M17 12C17 14.7614 14.7614 17 12 17C9.23858 17 7 14.7614 7 12C7 9.23858 9.23858 7 12 7C14.7614 7 17 9.23858 17 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                    </svg>
                                </button>
                                <button type="button" @click="window.theme.set('dark')" class="theme-dark">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                        <path d="M3 11.4489C3 16.7238 7.16904 21 12.3118 21C16.2709 21 19.6529 18.4657 21 14.8925C19.9331 15.4065 18.7418 15.6938 17.485 15.6938C12.9137 15.6938 9.20787 11.8928 9.20787 7.20396C9.20787 5.24299 9.85605 3.4373 10.9446 2C6.45002 2.6783 3 6.65034 3 11.4489Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                    </svg>
                                </button>
                            </div>
                        </li>
                    </ul>
                    <div class="relative z-10 inline-flex items-center space-x-3 lg:justify-end">
                        <?php if (isset(tiny::user()->id)): ?>
                            <a href="<?php echo tiny::homeURL('/account'); ?>" class="flex items-center text-sm" xclass="btn btn-outline pr-5 pl-2 h-11s"  data-side="bottom" data-align="end" data-tooltip="Account settings">
                                <img src="<?php echo tiny::user()->github_avatar; ?>" loading="lazy" class="shrink-0 size-7.5 rounded-full" />
                            </a>
                        <?php else: ?>
                            <a href="<?php echo tiny::homeURL('/auth/signin'); ?>" class="btn btn-primary px-2.5 lg:pr-3.5">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor" class="size-6">
                                    <path d="M14.5094 20.9056C14.5198 20.402 14.5349 19.6585 14.5349 19C14.5349 18 13.8548 17.0818 13.8548 17.0818C16.1129 16.834 18.4919 15.5037 18.4919 11.5393C18.4919 10.383 18.0887 9.84616 17.4435 9.10284L17.4579 9.0525C17.5588 8.7032 17.8583 7.66631 17.3226 6.29474C16.4758 6.00567 14.5403 7.40972 14.5403 7.40972C13.7339 7.16195 12.8871 7.07936 12 7.07936C11.1532 7.07936 10.3065 7.16195 9.5 7.40972C9.5 7.40972 7.52419 6.04697 6.71774 6.29474C6.15323 7.74009 6.47581 8.81377 6.59677 9.10284C5.95161 9.84616 5.62903 10.383 5.62903 11.5393C5.62903 15.5037 7.92742 16.834 10.1855 17.0818C10.1855 17.0818 9.5 17.8624 9.5 18.9312V20.9082V22.4578C4.76861 21.3309 1.25 17.0764 1.25 12C1.25 6.06294 6.06294 1.25 12 1.25C17.9371 1.25 22.75 6.06294 22.75 12C22.75 17.073 19.2361 21.3252 14.5094 22.4555V20.9056Z"></path>
                                </svg>
                                <span class="hidden lg:block">Sign in ›</span>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </nav>
            <?php tiny::render('_context-menu'); ?>
        </header>
    <?php endif; ?>

    <div class="container relative pt-26">
