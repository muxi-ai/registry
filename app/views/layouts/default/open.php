<?php
echo '<!-- v. ' . $_SERVER['APP_VERSION'] . " -->\n";

$titleAppend = ' › MUXI Registry';
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

    <link rel="stylesheet" type="text/css" href="<?php tiny::staticURL((@$_SERVER['ENV'] == 'local' ? '/css/style.css?' . time() : '/css/style.min.css?v='. @$_SERVER['APP_VERSION'])); ?>" media="all">
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
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 3060.31 990.81" class="h-11" id="muxi-wordmark">
                            <g class="text-[#d69a54] dark:text-[#e8a450]" fill="currentColor">
                                <path d="m687.32 438.13c-.76 36.37 3.12 72.29-13.62 104.64-17.01 31.02-51.34 52.23-74.45 40.63-48.02-24.77-1.91-146.67-94.64-98.14-20.13 11.78-38.15 29.93-50.77 50-25.67 40.44-23.17 86.93-50.19 124.45-13.75 18.84-36.87 28.21-59.99 28.27-23.12-.06-46.25-9.43-59.99-28.27-27.01-37.52-24.52-84.01-50.19-124.45-12.61-20.06-30.64-38.22-50.76-50-92.73-48.52-46.62 73.37-94.64 98.14-23.11 11.59-57.45-9.62-74.45-40.63-16.75-32.35-12.87-68.27-13.63-104.64v-249.62c4.14-46.94 46.88-76.34 86.87-99.52 31.27-18.09 90.19-52.1 90.19-52.1 53.31-32.55 107.24-37.07 166.61-36.89 59.36-.19 113.3 4.33 166.61 36.88 0 0 58.92 34.01 90.19 52.1 39.99 23.17 82.72 52.58 86.87 99.52v249.62h-.02z" />
                            </g>
                            <g class="text-[#302621] dark:text-[#f5e4d1]" fill="currentColor">
                                <path d="m905.6 138.06h93.96l102.6 271.62h4.32l102.6-271.62h94.5v386.64h-72.36v-190.62l4.32-64.26h-4.32l-98.28 254.88h-56.7l-98.82-254.88h-4.32l4.32 64.26v190.62h-71.82z" />
                                <path d="m1442.09 513.9c-22.5-12.96-39.96-31.67-52.38-56.16-12.42-24.48-18.63-53.46-18.63-86.94v-232.74h72.36v237.06c0 26.65 6.75 48.15 20.25 64.53 13.5 16.39 32.67 24.57 57.51 24.57s44.01-8.18 57.51-24.57c13.5-16.38 20.25-37.88 20.25-64.53v-237.06h72.36v232.74c0 32.05-6.13 60.3-18.36 84.78-12.24 24.49-29.7 43.56-52.38 57.24s-49.14 20.52-79.38 20.52-56.62-6.48-79.11-19.44z" />
                                <path d="m1831.69 322.74-116.64-184.68h90.18l75.06 126.36h4.32l74.52-126.36h90.72l-117.18 184.68 126.9 201.96h-90.72l-84.24-139.32h-4.32l-84.24 139.32h-90.72z" />
                                <path d="m2100.61 138.06h72.9v386.64h-72.9z" />
                            </g>
                        </svg>
                        <span class="font-medium text-xs uppercase text-[#c88b45] dark:text-[#e8a550] -ml-8">/ Registry</span>
                    </a>

                    <form action="<?php tiny::homeURL('/search'); ?>" method="GET" class="w-sm max-w-[calc(100vw-2rem)] z-100 group fixed left-1/2 transform -translate-x-1/2 xl:-ml-2">
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
