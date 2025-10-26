<?php
echo '<!-- v. '. $_SERVER['APP_VERSION'] ." -->\n";
?>
<!DOCTYPE html>
<html lang="en" class="relative min-h-full overscroll-none"  style="scroll-behavior: smooth">
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

        function pointerenterHandler () {
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

<body class="text-pretty antialiased <?php echo tiny::layout()->props('isHome') === true ? 'home' : ''; ?>">

<?php if (tiny::layout()->props('emptyLayout') === false) { ?>
<header class="main-nav">
    <nav hx-boost="true" hx-target="body" hx-swap="outerHTML">
        <!-- navigation will be added here -->
    </nav>
</header>
<?php } ?>
