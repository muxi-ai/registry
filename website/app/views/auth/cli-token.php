<?php tiny::layout()->default(title: 'Token Generated', emptyLayout: false); ?>

<div class="card w-full max-w-2xl mx-auto">
  <header class="space-y-2">
    <h2 class="text-xl mt-2 font-bold">✅ CLI Token Generated</h2>
    <p class="text-gray-600">Copy this token and paste it into your terminal when running <code class="bg-gray-100 px-2 py-1 rounded text-sm">muxi login</code></p>
  </header>
  
  <section class="space-y-4">
    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
      <label class="text-xs font-semibold text-gray-600 uppercase mb-2 block">Your CLI Token</label>
      <div class="flex items-center gap-2">
        <input 
          type="text" 
          value="<?php echo htmlspecialchars(tiny::data()->token); ?>" 
          readonly 
          class="font-mono text-sm flex-1 p-3 border border-gray-300 rounded bg-white focus:outline-none focus:ring-2 focus:ring-blue-500"
          id="cliToken"
        >
        <button 
          onclick="copyToken()"
          class="btn btn-primary px-6 whitespace-nowrap"
          id="copyButton"
        >
          Copy
        </button>
      </div>
    </div>

    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
      <div class="flex items-start gap-3">
        <svg class="w-5 h-5 text-yellow-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
        </svg>
        <div class="flex-1">
          <p class="text-sm font-semibold text-yellow-800 mb-1">Keep this token secure!</p>
          <p class="text-xs text-yellow-700">This token gives full access to your account. Don't share it publicly or commit it to version control.</p>
        </div>
      </div>
    </div>
  </section>

  <footer class="flex flex-col items-center gap-2 mt-6">
    <a href="/account" class="btn-secondary w-full">Go to Dashboard</a>
  </footer>
</div>

<script>
function copyToken() {
  const input = document.getElementById('cliToken');
  const button = document.getElementById('copyButton');
  
  // Copy to clipboard
  navigator.clipboard.writeText(input.value).then(function() {
    // Show success feedback
    button.textContent = '✓ Copied!';
    button.classList.remove('btn-primary');
    button.classList.add('btn-success', 'bg-green-600', 'text-white');
    
    // Reset button after 2 seconds
    setTimeout(function() {
      button.textContent = 'Copy';
      button.classList.remove('btn-success', 'bg-green-600', 'text-white');
      button.classList.add('btn-primary');
    }, 2000);
  }).catch(function(err) {
    // Fallback for older browsers
    input.select();
    document.execCommand('copy');
    button.textContent = '✓ Copied!';
  });
}
</script>

<?php tiny::layout()->default('/'); ?>
