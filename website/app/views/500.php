<?php tiny::layout()->default(title: 'Application Error', emptyLayout: true, robots: 'noindex, follow'); ?>

<div class="flex flex-col items-center justify-center h-screen">
    <h2 class="text-2xl font-bold"><?php echo tiny::layout()->props('code') ?: 501; ?></h2>

    <?php if (@tiny::data()->error): ?>
        <div class="font-mono text-sm text-muted my-6">
            <?php echo tiny::data()->error; ?>
        </div>
    <?php else: ?>
        <div class="text-sm text-muted mt-6 mb-4">Application error occurred</div>
    <?php endif; ?>

    <a href="<?php tiny::homeURL('/'); ?>" class="font-medium text-sm block w-fit mx-auto text-blue-500 hover:text-blue-500/80" role="button">
        <span>Back to homepage ›</span>
    </a>

    <a href="<?php tiny::homeURL('/auth/signin'); ?>" class="font-medium text-sm block w-fit mx-auto text-indigo-500 hover:text-indigo-500/80" role="button">
        <span>Please login to continue ›</span>
    </a>

    <br>&nbsp;<br>&nbsp;<br>&nbsp;

</div>

<?php tiny::layout()->default('/'); ?>
