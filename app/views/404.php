<?php tiny::layout()->default(title: 'Page not found', emptyLayout: false, robots: 'noindex, follow'); ?>

<div class="p-5 mt-32 mb-16 min-h-96 flex flex-col justify-center items-center text-center">
    <div class="max-w-md mx-auto">
        <h1 class="text-4xl font-bold">404</h1>
        <?php if (@tiny::data()->error): ?>
            <div class="font-mono text-sm text-muted my-6">
                <?php echo tiny::data()->error; ?>
            </div>
        <?php else: ?>
            <div class="text-sm mt-6 mb-4">This page could not be found.</div>
        <?php endif; ?>

        <a href="<?php tiny::homeURL('/'); ?>" class="font-medium text-sm block w-fit mx-auto text-blue-500 hover:text-blue-500/80" role="button">
            <span>Back to homepage ›</span>
        </a>

        <br>&nbsp;<br>&nbsp;<br>&nbsp;

    </div>
</div>

<?php tiny::layout()->default('/'); ?>
