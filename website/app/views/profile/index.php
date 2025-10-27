<?php tiny::layout()->default(title: tiny::data()->username, emptyLayout: false); ?>

<h1 class="text-2xl font-bold">Profile: @<?php echo tiny::data()->username; ?></h1>

<?php tiny::layout()->default('/'); ?>
