<?php
echo '<!-- v. ' . $_SERVER['APP_VERSION'] . " -->\n";
?>
<!DOCTYPE html>
<html lang="en" class="relative min-h-full overscroll-none" style="scroll-behavior: smooth">

<head>
    <?php if (@$_SERVER['ENV'] != 'local'): ?>
        <!-- <script async src="https://www.googletagmanager.com/gtag/js?id=G-7NKW2LYNSQ"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag() { dataLayer.push(arguments); }
        gtag("js", new Date());
        gtag('config', 'G-7NKW2LYNSQ', { 'anonymize_ip': true });
    </script> -->
        <?php if (@$_SERVER['SENTRY_FRONTEND']): ?>
            <script src="https://js.sentry-cdn.com/<?php echo $_SERVER['SENTRY_FRONTEND']; ?>.min.js" crossorigin="anonymous"></script>
        <?php endif; ?>
    <?php endif; ?>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="format-detection" content="telephone=no">

    <meta name="robots" content="<?php echo tiny::layout()->props('robots') ? strip_tags(tiny::layout()->props('robots')) : 'index, follow' ?>">

    <title>MUXI Registry: <?php echo tiny::layout()->props('title') ? strip_tags(tiny::layout()->props('title')) : 'Publish and discover AI agent formations' ?></title>
    <meta name="description" content="MUXI Registry is Docker Hub for AI formations - a platform where developers share, discover, and deploy complete, AI agent formations instantly. Stop building from scratch. Start with battle-tested formations from the community.">

    <link rel="stylesheet" type="text/css" href="<?php tiny::staticURL('/css/style.css'); ?>" media="all">

    <link href="<?php tiny::staticURL('/favicon.ico'); ?>" rel="icon" type="image/x-icon">
    <link href="<?php tiny::staticURL('/favicon.png'); ?>" rel="icon" type="image/png">
    <link rel="apple-touch-icon" sizes="512x512" href="<?php tiny::staticURL('/apple-touch-icon.png'); ?>">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="MUXI Registry:">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="theme-color" content="#070914">

    <!-- Open Graph Meta Tags -->
    <meta property="og:url" content="<?php echo tiny::router()->permalink; ?>" />
    <meta property="og:type" content="website" />
    <meta property="og:title" content="MUXI Registry: <?php echo tiny::layout()->props('title') ? strip_tags(tiny::layout()->props('title')) : 'Publish and discover AI agent formations' ?>" />
    <meta property="og:description" content="MUXI Registry is Docker Hub for AI formations - a platform where developers share, discover, and deploy complete, AI agent formations instantly. Stop building from scratch. Start with battle-tested formations from the community." />
    <meta property="og:image" content="<?php tiny::layout()->props('ogImage') ?? tiny::staticURL('img/card.webp'); ?>" />

    <!-- Twitter Meta Tags -->
    <meta name="twitter:card" content="summary_large_image" />
    <meta property="twitter:domain" content="registry.muxi.org" />
    <meta property="twitter:site" content="@muxi_ai" />
    <meta property="twitter:url" content="<?php echo tiny::router()->permalink; ?>" />
    <meta name="twitter:title" content="MUXI Registry: <?php echo tiny::layout()->props('title') ? strip_tags(tiny::layout()->props('title')) : 'Publish and discover AI agent formations' ?>" />
    <meta name="twitter:description" content="MUXI Registry is Docker Hub for AI formations - a platform where developers share, discover, and deploy complete, AI agent formations instantly. Stop building from scratch. Start with battle-tested formations from the community." />
    <meta name="twitter:image" content="<?php tiny::layout()->props('ogImage') ?? tiny::staticURL('img/card.webp'); ?>" />

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <script type="speculationrules">
        {
        "prerender": [{ "where": { "href_matches": "/*" }, "eagerness": "moderate" }],
        "prefetch": [{ "where": { "href_matches": "/*" }, "eagerness": "moderate" }]
    }
    </script>
    <script>
        if (!HTMLScriptElement.supports || !HTMLScriptElement.supports('speculationrules')) {
            const preloadedUrls = {};

            function pointerenterHandler() {
                if (!preloadedUrls[this.href]) {
                    preloadedUrls[this.href] = true;

                    const prefetcher = document.createElement('link');

                    prefetcher.as = prefetcher.relList.supports('prefetch') ? 'document' : 'fetch';
                    prefetcher.rel = prefetcher.relList.supports('prefetch') ? 'prefetch' : 'preload';
                    prefetcher.href = this.href;

                    document.head.appendChild(prefetcher);
                }
            }

            document.querySelectorAll('a[href^="/"]').forEach(item => {
                item.addEventListener('pointerenter', pointerenterHandler);
            });
        }
    </script>

    <script defer src="<?php tiny::staticURL('/js/alpine.combo.min.js'); ?>"></script>
    <style>[x-cloak] { display: none !important; }</style>

    <?php if (tiny::layout()->props('emptyLayout') === false): ?>
        <!-- <script src="<?php tiny::staticURL('/js/htmx.min.js'); ?>"></script> -->
    <?php endif; ?>

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

<body class="text-primary antialiased <?php echo tiny::layout()->props('isHome') === true ? 'home' : ''; ?>">

    <?php if (tiny::layout()->props('emptyLayout') === false) { ?>
        <section class="relative w-full px-8 text-gray-700 bg-white body-font">
            <div class="container flex flex-col flex-wrap items-center justify-between py-5 px-8 mx-auto md:flex-row max-w-7xl">
                <a href="<?php echo tiny::homeURL('/'); ?>" class="relative z-10 flex items-center w-auto text-2xl font-extrabold leading-none text-black select-none">MUXI Registry</a>

                <nav class="top-0 left-0 z-0 flex items-center justify-center w-full h-full py-5 space-x-5 text-base md:-ml-5 md:py-0 md:absolute">
                    <form action="<?php tiny::homeURL('/search'); ?>" method="GET" class="relative w-lg">
                        <input class="input w-full" type="search" placeholder="Search formations..." name="q" value="<?php echo urldecode(tiny::router()->query['q'] ?? ''); ?>">
                        <button class="absolute right-0 top-0 h-full px-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" class="size-6">
                                <path d="M15 15L21 21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                <path d="M17 10C17 13.866 13.866 17 10 17C6.13401 17 3 13.866 3 10C3 6.13401 6.13401 3 10 3C13.866 3 17 6.13401 17 10Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                            </svg>
                        </button>
                    </form>
                </nav>

                <div class="relative z-10 inline-flex items-center space-x-3 md:ml-5 lg:justify-end">
                    <?php if (isset(tiny::user()->id)): ?>
                        <a href="<?php echo tiny::homeURL('/account'); ?>" class="btn btn-outline pr-5 pl-2 h-11">
                            <img src="<?php echo tiny::user()->github_avatar; ?>" loading="lazy" class="object-cover size-7.5 rounded-full" />
                            <span class="flex flex-col items-start leading-none pt-0.5 text-neutral-700">
                                <span><?php echo tiny::user()->first_name . ' ' . tiny::user()->last_name; ?></span>
                                <span class="text-xs font-normal text-neutral-400">@<?php echo tiny::user()->registry_username; ?></span>
                            </span>
                        </a>
                    <?php else: ?>
                        <a href="<?php echo tiny::homeURL('/auth/signin'); ?>" class="btn btn-primary pr-4 pl-3 h-11">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" class="size-7.5">
                                <path d="M14.5094 20.9056C14.5198 20.402 14.5349 19.6585 14.5349 19C14.5349 18 13.8548 17.0818 13.8548 17.0818C16.1129 16.834 18.4919 15.5037 18.4919 11.5393C18.4919 10.383 18.0887 9.84616 17.4435 9.10284L17.4579 9.0525C17.5588 8.7032 17.8583 7.66631 17.3226 6.29474C16.4758 6.00567 14.5403 7.40972 14.5403 7.40972C13.7339 7.16195 12.8871 7.07936 12 7.07936C11.1532 7.07936 10.3065 7.16195 9.5 7.40972C9.5 7.40972 7.52419 6.04697 6.71774 6.29474C6.15323 7.74009 6.47581 8.81377 6.59677 9.10284C5.95161 9.84616 5.62903 10.383 5.62903 11.5393C5.62903 15.5037 7.92742 16.834 10.1855 17.0818C10.1855 17.0818 9.5 17.8624 9.5 18.9312V20.9082V22.4578C4.76861 21.3309 1.25 17.0764 1.25 12C1.25 6.06294 6.06294 1.25 12 1.25C17.9371 1.25 22.75 6.06294 22.75 12C22.75 17.073 19.2361 21.3252 14.5094 22.4555V20.9056Z" fill="#fff"></path>
                            </svg>
                            <span>Sign in</span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    <?php } ?>

    <div class="py-12 px-8 mx-auto md:flex-row max-w-7xl">
