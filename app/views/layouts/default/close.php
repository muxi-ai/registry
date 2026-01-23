</div>
<?php if (!tiny::layout()->props('emptyLayout')): ?>

<?php
tiny::components()->require('Footer');
tiny::components()->Footer();
?>

<?php tiny::render('_cookie-consent'); ?>

<script>
  // --------- tiny load bump ---------
  // Nudge page by 1px down+up on load (helps hide mobile address bar, etc.)
  window.addEventListener('load', () => {
    setTimeout(() => {
      window.scrollBy(0, 1);
      window.scrollBy(0, -1);

      window.addEventListener('scroll', () => {
        if (window.scrollY > 60) {
          document.body.classList.add('scrolled');
        } else {
          document.body.classList.remove('scrolled');
        }
      });
    }, 100);
  });

  // window.mobileMenu = window.mobileMenu || {
  //   scrollPosition: 0,
  //   open: () => {
  //     mobileMenu.scrollPosition = window.scrollY;
  //     document.getElementById('footer').classList.add('min-h-screen');
  //     document.getElementById('footer').scrollIntoView({
  //       behavior: "instant"
  //     });
  //     document.getElementById('mobile-menu-close').classList.remove('hidden');
  //     document.body.classList.add('overflow-hidden');
  //   },
  //   close: () => {
  //     window.scrollTo({
  //       top: mobileMenu.scrollPosition,
  //       behavior: "instant"
  //     });
  //     document.getElementById('mobile-menu-close').classList.add('hidden');
  //     document.body.classList.remove('overflow-hidden');
  //     document.getElementById('footer').classList.remove('min-h-screen');
  //   }
  // }
</script>

<?php endif; ?>
</body>
</html>
