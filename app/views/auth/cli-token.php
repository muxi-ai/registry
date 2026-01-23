<?php tiny::layout()->default(title: 'Token Generated', emptyLayout: false, robots: 'noindex, follow'); ?>

<?php $token = htmlspecialchars(tiny::data()->token ?? '*****'); ?>

<div class="flex items-center min-h-128">
  <div class="card shadow-xs w-full max-w-xl mx-auto">
    <header class="space-y-2 ">
      <h1 class="text-3xl mt-3 font-bold">Token Generated</h1>
      <p class="my-0! subpixel-antialiased text-inherit opacity-80">Copy this token and paste it into your terminal when running <code class="bg-gray-100 px-2 py-1 rounded text-sm">muxi login</code></p>
    </header>

    <section>

      <label class="text-xs font-semibold uppercase mb-2 block">Your CLI Token ↓</label>
      <div class="mb-6 flex items-center space-x-1 justify-between">
        <div class="text-xs border w-full inset-shadow-xs font-mono rounded-sm py-2 px-3 cursor-pointer bg-black/2 dark:bg-black/5" onclick="this.select();">
          <?php echo $token; ?>
        </div>
        <button class="btn-ghost hover:scale-105" onmousedown="this.classList.add('scale-90!')" onmouseup="this.classList.remove('scale-90!')" onclick="navigator.clipboard.writeText('<?php echo $token ?>')">
          <svg focusable="false" viewBox="0 0 16 16" fill="currentColor" class="size-4">
            <path d="M0 6.75C0 5.784.784 5 1.75 5h1.5a.75.75 0 0 1 0 1.5h-1.5a.25.25 0 0 0-.25.25v7.5c0 .138.112.25.25.25h7.5a.25.25 0 0 0 .25-.25v-1.5a.75.75 0 0 1 1.5 0v1.5A1.75 1.75 0 0 1 9.25 16h-7.5A1.75 1.75 0 0 1 0 14.25Z"></path>
            <path d="M5 1.75C5 .784 5.784 0 6.75 0h7.5C15.216 0 16 .784 16 1.75v7.5A1.75 1.75 0 0 1 14.25 11h-7.5A1.75 1.75 0 0 1 5 9.25Zm1.75-.25a.25.25 0 0 0-.25.25v7.5c0 .138.112.25.25.25h7.5a.25.25 0 0 0 .25-.25v-7.5a.25.25 0 0 0-.25-.25Z"></path>
          </svg>
        </button>
      </div>


      <div class="alert-destructive">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="12" cy="12" r="10" />
          <line x1="12" x2="12" y1="8" y2="12" />
          <line x1="12" x2="12.01" y1="16" y2="16" />
        </svg>
        <h2 class="font-sans! text-base! -mt-0.5!">Keep this token secure!</h2>
        <section class="leading-normal block text-sm!">This token gives full access to your account. <strong>DO NOT</strong> share it publicly or commit it to version control.</section>
      </div>
    </section>

    <footer class="flex flex-col items-center gap-4 mb-4">
      <a href="/account" class="btn-secondary w-full">
        Go to Account Settings
      </a>
    </footer>
  </div>
</div>

<?php tiny::layout()->default('/'); ?>
