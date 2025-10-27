<?php tiny::layout()->default(title: 'Install the GitHub App', emptyLayout: false); ?>

<div class="card w-full max-w-md mx-auto">
  <header class="space-y-2">
    <h2 class="text-xl mt-2 font-bold">Install the GitHub App</h2>
    <p>The MUXI Registry GitHub App is required to publish <a href="https://muxi.org/docs/formations">formations</a>.</p>
  </header>
  <section class="space-y-4 text-sm">
    <p>
      MUXI Registry uses a GitHub App for secure authentication.
      You'll be asked to:
    </p>

    <ol class="list-decimal pl-6 space-y-2">
      <li><strong>Select repositories</strong> - We only read public metadata
        (repo names, stars). Your code stays private.</li>
      <li><strong>Grant permissions</strong> - To create NEW repositories
        when you publish formations.</li>
    </ol>
  </section>
  <footer class="flex flex-col items-center gap-2">
    <a href="<?php echo tiny::data()->install_url; ?>" class="btn w-full">Install App</a>
    <a href="/dashboard" class="btn-secondary w-full">Skip for now</a>
    <p class="mt-3 text-center text-xs text-gray-500">
      You can still pull formations without installing.<br>
      Install later to push formations.
    </p>
  </footer>
</div>

<?php tiny::layout()->default('/'); ?>
