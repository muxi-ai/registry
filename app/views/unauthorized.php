<?php tiny::layout()->default(title: 'Unauthorized', emptyLayout: false, robots: 'noindex, follow'); ?>


<div class="p-5 mt-32 mb-16 min-h-96 flex flex-col justify-center items-center text-center">
    <div class="max-w-md mx-auto">
        <h1 class="text-4xl font-bold">Unauthorized</h1>
        <div class="text-sm mt-6 mb-4">If you're trying to access your account, please <a href="/auth/signin">log in</a>.</div>

        <a href="<?php tiny::homeURL('/'); ?>" class="font-medium text-sm block w-fit mx-auto text-blue-500 hover:text-blue-500/80" role="button">
            <span>Back to homepage ›</span>
        </a>

        <br>&nbsp;<br>&nbsp;<br>&nbsp;

    </div>
</div>

<?php tiny::layout()->default('/'); ?>
