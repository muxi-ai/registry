<?php
if (tiny::layout()->props('emptyLayout') === false) {
  // tiny::components()->require('Footer');
  // tiny::components()->Footer();
}
?>

</div>


<section class="w-full bg-white">
    <div class="px-8 py-12 mx-auto max-w-7xl">
        <div class="flex flex-col items-start justify-between pt-10 mt-10 border-t border-gray-100 md:flex-row md:items-center">
            <p class="mb-6 text-sm text-left text-gray-600 md:mb-0">&copy; VarOps LLC. All Rights Reserved.</p>
            <div class="flex items-start justify-start space-x-6 md:items-center md:justify-center">
                <a href="#_" class="text-sm text-gray-600 transition">Terms</a>
                <a href="#_" class="text-sm text-gray-600 transition">Privacy</a>
            </div>
        </div>
    </div>
</section>

<!-- content end -->
<?php /* if (isset(tiny::data()->CSRFError)): ?>
  <script>
    showToast([{
      level: 'error',
      title: 'Request check failed',
      message: 'Your request included an invalid or missing CSRF token. Please refresh the page and try again.',
      id: '<?php echo tiny::data()->CSRFError; ?>'
    }]);
  </script>
<?php endif; */ ?>

<?php
// $toast = tiny::flash('toast')->get();
// if ($toast) {
//   $toast['id'] = $toast['id'] ?? '';
//   $toast['message'] = addslashes($toast['message']);
//   echo <<<TOAST
//     <script>
//       showToast([{
//         "level": "{$toast['level']}",
//         "title": "{$toast['title']}",
//         "message": "{$toast['message']}",
//         "id": "{$toast['id']}"
//       }]);
//     </script>
//     TOAST;
// }
?>

<?php
// tiny::components()->require('Toast');
// tiny::components()->Toast();

// tiny::components()->require('TinyJS');
// tiny::components()->TinyJS();
?>

<script>
  // --------- tiny load bump ---------
  // Nudge page by 1px down+up on load (helps hide mobile address bar, etc.)
  window.addEventListener('load', () => {
    setTimeout(() => {
      window.scrollBy(0, 1);
      window.scrollBy(0, -1);
    }, 100);
  });

  window.mobileMenu = window.mobileMenu || {
    scrollPosition: 0,
    open: () => {
      mobileMenu.scrollPosition = window.scrollY;
      document.getElementById('footer').classList.add('min-h-screen');
      document.getElementById('footer').scrollIntoView({ behavior: "instant"});
      document.getElementById('mobile-menu-close').classList.remove('hidden');
      document.body.classList.add('overflow-hidden');
    },
    close: () => {
      window.scrollTo({ top: mobileMenu.scrollPosition, behavior: "instant"});
      document.getElementById('mobile-menu-close').classList.add('hidden');
      document.body.classList.remove('overflow-hidden');
      document.getElementById('footer').classList.remove('min-h-screen');
    }
  }
</script>

</body>
</html>
