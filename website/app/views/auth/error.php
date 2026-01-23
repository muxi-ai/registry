<?php tiny::layout()->default(title: 'Authentication Error', emptyLayout: false, robots: 'noindex, follow'); ?>


<div class="flex items-center min-h-128">
  <div class="card shadow-xs w-full max-w-md mx-auto">
    <header class="space-y-2 text-center">
      <h1 class="text-3xl mt-3 font-bold">Authentication Error</h1>
      <p class="my-0! subpixel-antialiased text-inherit opacity-80">There was an error during authentication. Please try again.</p>
    </header>
    <section class="-mt-6! text-center">
      <pre><code><?php echo tiny::data()->message; ?> (Error code: <?php echo tiny::data()->error_code ?? 'Z01'; ?>)</code></pre>
      <hr>
    </section>
    <footer class="flex flex-col items-center gap-4">
      <a href="<?php tiny::homeURL('/auth'); ?>" class="btn w-full">Try again</a>
      <a href="<?php tiny::homeURL('/'); ?>" class="btn-ghost w-full">Skip for now</a>
      <p class="text-center text-xs text-gray-500 mt-0!">
        You can still pull formations without authenticating.<br>
        Authenticate later to push formations.
      </p>
    </footer>
  </div>
</div>

<?php tiny::layout()->default('/'); ?>
