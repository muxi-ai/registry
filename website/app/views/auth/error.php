<?php tiny::layout()->default(title: 'Authentication Error', emptyLayout: false); ?>


<div class="card w-full max-w-md mx-auto">
  <header class="space-y-2">
    <h2 class="text-xl mt-2 font-bold">Authentication Error</h2>
    <p>There was an error during authentication. Please try again.</p>
  </header>
  <section class="space-y-4 text-sm">
    <p><?php echo tiny::data()->message; ?> (Error code: <?php echo tiny::data()->error_code ?? 'Z01'; ?>)</p>
  </section>
  <footer class="flex flex-col items-center gap-4">
    <a href="<?php tiny::homeURL('/auth'); ?>" class="btn w-full">Try again</a>
    <a href="<?php tiny::homeURL('/'); ?>" class="btn-ghost w-full">Skip for now</a>
    <p class="text-center text-xs text-gray-500">
      You can still pull formations without authenticating.<br>
      Authenticate later to push formations.
    </p>
  </footer>
</div>

<?php tiny::layout()->default('/'); ?>
