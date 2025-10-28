<?php tiny::layout()->default(title: 'Generate CLI Token', emptyLayout: false); ?>

<div class="card w-full max-w-xl mx-auto">
  <header class="space-y-2">
    <h2 class="text-xl mt-2 font-bold">Generate CLI Token</h2>
    <p>Click the button below to generate a CLI token for your account.</p>
  </header>
  <footer class="flex flex-col items-center gap-2">
    <a href="/auth/signin" class="btn w-full">Sign in with GitHub to Generate Token</a>
  </footer>
</div>

<?php tiny::layout()->default('/'); ?>
