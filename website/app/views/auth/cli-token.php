<?php tiny::layout()->default(title: 'Token Generated', emptyLayout: false); ?>

<div class="card w-full max-w-xl mx-auto">
  <header class="space-y-2">
    <h2 class="text-xl mt-2 font-bold">Token Generated</h2>
    <p>Your CLI token has been generated. Paste the following token into your CLI client:</p>
  </header>
  <section class="text-xs">
    <code><?php echo tiny::data()->token; ?></code>
  </section>
  <footer class="flex flex-col items-center gap-2">
    <a href="/dashboard" class="btn-secondary w-full">Go to Dashboard</a>
  </footer>
</div>

<?php tiny::layout()->default('/'); ?>
