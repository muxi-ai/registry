<?php tiny::layout()->default(title: '', emptyLayout: false); ?>

<h1 class="text-3xl font-bold">Welcome to the MUXI Registry</h1>
<p>The MUXI Registry is a platform for hosting and sharing MUXI formations.</p>
<?php if (tiny::user()): ?>
  <p>You are logged in as <a href="<?php tiny::homeURL('/@' . tiny::user()->registry_username); ?>"><?php echo tiny::user()->registry_username; ?></a>.</p>
<?php else: ?>
  <p>To get started, please login with GitHub.</p>
  <p>If you don't have a GitHub account, please create one <a href="https://github.com/join">here</a>.</p>
  <p>If you have any questions, please contact us at <a href="mailto:support@muxi.ai">support@muxi.ai</a>.</p>
  <p>Thank you for using the MUXI Registry!</p>
<?php endif; ?>



<?php tiny::layout()->default('/'); ?>
