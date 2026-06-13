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
    <link rel="llm-context" href="<?php tiny::homeURL('/llms.txt'); ?>" type="text/plain" />
    <?php if (@tiny::layout()->props('alternate')): ?>
        <link rel="alternate" type="text/markdown" href="<?php echo tiny::layout()->props('alternate'); ?>">
    <?php endif; ?>

    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "@id": "https://muxi.org/#software",
        "name": "MUXI",
        "url": "https://muxi.org",
        "applicationCategory": "DeveloperApplication",
        "description": "Open-source infrastructure for production AI agents",
        "author": {
            "@type": "Person",
            "@id": "https://aroussi.com/#person",
            "name": "Ran Aroussi",
            "url": "https://aroussi.com"
        }
    }
    </script>

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
    <meta property="og:image" content="<?php echo tiny::layout()->props('ogImage') ?: tiny::getStaticURL('img/ogcard.webp'); ?>" />

    <!-- Twitter Meta Tags -->
    <meta name="twitter:card" content="summary_large_image" />
    <meta property="twitter:domain" content="muxi.org" />
    <meta property="twitter:site" content="@muxi_ai" />
    <meta property="twitter:url" content="<?php echo tiny::router()->permalink; ?>" />
    <meta name="twitter:title" content="<?php echo tiny::layout()->props('title') ? htmlspecialchars(strip_tags(tiny::layout()->props('title'))) : 'Open-source infrastructure for AI agents' ?><?php echo $titleAppend; ?>" />
    <meta name="twitter:description" content="<?php echo tiny::layout()->props('description') ? htmlspecialchars(strip_tags(tiny::layout()->props('description'))) : $defaultDescription; ?>" />
    <meta name="twitter:image" content="<?php echo tiny::layout()->props('ogImage') ?: tiny::getStaticURL('img/ogcard.webp'); ?>" />

    <script type="speculationrules">{ "prefetch": [{ "where": { "href_matches": "/*" }, "eagerness": "moderate" }] }</script>
    <script defer src="<?php tiny::staticURL('/js/alpine.combo.min.js'); ?>"></script>
    <script defer src="<?php tiny::staticURL('/js/htmx.min.js'); ?>"></script>
    <script src="<?php tiny::staticURL('/js/theme.min.js'); ?>"></script>
    <?php /* <script src="<?php tiny::staticURL('/js/app.min.js'); ?>"></script> */ ?>

    <script>window.Prism = window.Prism || {}; // window.Prism.manual = true;</script>
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
        <?php tiny::render('_announcement'); ?>
        <div class="bg-black/10 backdrop-blur-xs fixed top-20 inset-0 z-10" :class="showMenu ? 'block lg:hidden' : 'hidden'"></div>
        <header id="header">
            <nav aria-label="Global">
                <div class="lg:flex items-center justify-between gap-6 w-full">
                    <a href="<?php tiny::homeURL(); ?>" @contextmenu="logoContextMenu.toggle(event)" @click.outside="logoContextMenu.hide()" class="flex items-center space-x-2 max-w-10 lg:max-w-none overflow-hidden">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 3060.31 990.81" class="scale-110 origin-top-left lg:scale-100 h-13 lg:h-12 shrink-0" id="muxi-wordmark">
                            <path class="text-[#d69a54] dark:text-[#e8a450]" fill="currentColor" d="m687.3 438.1c-.8 36.4 3.1 72.3-13.6 104.6-17 31-51.3 52.2-74.5 40.6-48-24.8-1.9-146.7-94.6-98.1-20.1 11.8-38.1 29.9-50.8 50-25.7 40.4-23.2 86.9-50.2 124.5-13.8 18.8-36.9 28.2-60 28.3-23.1 0-46.2-9.4-60-28.3-27-37.5-24.5-84-50.2-124.5-12.6-20.1-30.6-38.2-50.8-50-92.7-48.5-46.6 73.4-94.6 98.1-23.1 11.6-57.5-9.6-74.4-40.6-16.7-32.3-12.8-68.2-13.6-104.6v-249.6c4.1-46.9 46.9-76.3 86.9-99.5 31.3-18.1 90.2-52.1 90.2-52.1 53.3-32.6 107.2-37.1 166.6-36.9 59.4-.2 113.3 4.3 166.6 36.9 0 0 58.9 34 90.2 52.1 40 23.2 82.7 52.6 86.9 99.5v249.6z" />
                            <g class="text-[#302621] dark:text-[#f5e4d1]" fill="currentColor">
                                <path d="m1139.8 372.7c-1.8-7.9-47-175.1-58.7-218.2-1.6-5.9-7-10-13.1-10h-131.8c-7.5 0-13.6 6.1-13.6 13.6v344.1c0 7.5 6.1 13.6 13.6 13.6h74.3c7.5 0 13.7-6.1 13.6-13.7-.6-52.5-2.7-238.5-3-251 3.3 12.9 60.4 208.7 73.8 255 1.7 5.8 7 9.8 13 9.8h63.3c6.1 0 11.4-4 13-9.8 13.8-48.7 73.8-260.6 73.8-261 0 .2-2.5 202-3.2 257.1 0 7.5 6 13.8 13.6 13.8h74.3c7.5 0 13.6-6.1 13.6-13.6v-344.1c0-7.5-6.1-13.6-13.6-13.6h-132.2c-6.1 0-11.6 4.1-13.1 10.1-11.5 43.1-56 210.2-57.8 218.1q0 0 0 0z" />
                                <path d="m1624.7 375c0 11.6-2.5 21.9-7.6 30.7s-12 15.8-20.9 20.9c-8.9 5.2-19.2 7.7-30.7 7.7s-21.9-2.5-30.7-7.7-15.8-12.1-20.9-21.1c-5.2-8.9-7.7-19.1-7.7-30.5v-216.9c0-7.5-6.1-13.6-13.6-13.6h-73.5c-7.5 0-13.6 6.1-13.6 13.6v225.4c0 27.9 6.6 52.2 19.8 72.9s31.8 36.7 55.6 48.1c23.9 11.4 52 17 84.5 17s60.3-5.7 84.3-17c24-11.4 42.6-27.4 55.8-48.1s19.8-45 19.8-72.9v-225.4c0-7.5-6.1-13.6-13.6-13.6h-73.5c-7.5 0-13.6 6.1-13.6 13.6v216.9z" />
                                <path d="m2078.4 144.6h-81.1c-4.8 0-9.2 2.5-11.6 6.6-10.2 17-37.4 62.2-49.7 82.9-2.6 4.4-9.1 4.4-11.7 0-12-20.7-38.8-65.8-48.8-82.8-2.5-4.1-6.9-6.6-11.6-6.6h-84c-10.8 0-17.3 12-11.3 21l102.6 156.5c1.6 2.3 1.5 5.3-.2 7.6l-116.1 164.7c-6.3 9 0 21.4 11.1 21.4h83.6c4.6 0 8.9-2.3 11.4-6.1 12-18.6 47.9-73.9 62-95.9 2.7-4.2 8.9-4.1 11.5 0 13.6 21.9 47.6 76.8 59.3 95.6 2.5 4 6.8 6.4 11.6 6.4h87.2c10.9 0 17.4-12.2 11.2-21.2l-114.1-167.8c-1.6-2.3-1.6-5.2 0-7.5l100.4-153.8c5.9-9-.6-21-11.4-21z" />
                                <rect height="371.3" rx="13.6" width="102.9" x="2140.7" y="144.6" />
                            </g>
                        </svg>
                        <span class="font-medium text-xs uppercase text-[#c88b45] dark:text-[#e8a550] -ml-8">/ Registry</span>
                    </a>

                    <form action="<?php tiny::homeURL('/search'); ?>" method="GET" class="w-60 lg:w-sm max-w-[calc(100vw-2rem)] z-100 group absolute left-1/2 transform -translate-x-1/2 xl:-ml-2">
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
                    <div classs="relative z-10 inline-flex items-center space-x-3 lg:justify-end">
                        <?php if (isset(tiny::user()->github_username)): ?>
                            <a href="<?php echo tiny::homeURL('/account'); ?>" class="flex items-center text-sm" data-side="bottom" data-align="end" data-tooltip="Account settings">
                                <img src="<?php echo tiny::user()->github_avatar; ?>" loading="lazy" class="shrink-0 min-w-7.5 size-7.5 rounded-full" />
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
