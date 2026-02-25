<template x-if="showAnnouncement">
<div id="announcement">
    <div>
        🚧 MUXI is in early public preview. Expect rough edges, rapid changes, and
        <a href="https://github.com/muxi-ai/muxi/discussions/7" target="_blank">please report bugs</a>
    </div>
    <button @click="showAnnouncement=false; document.cookie = 'hide_announcement=1; path=/';">🆇</button>
</div>
</template>
<?php /*
<div id="announcement"><div>

Join our launch event on January 20th!
<br class="block md:hidden">
<a href="#">Register now</a>

</div><button @click="showAnnouncement=false; document.cookie = 'hide_announcement=1; path=/';">🆇</button></div>
*/ ?>
