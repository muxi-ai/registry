<!-- content end -->
<div id="logo-context-menu" class="dropmenu-wrapper w-80 z-50 -ml-1 <?php echo tiny::router()->controller == 'docs' ? 'fixed left-4 top-16' : 'absolute top-16'; ?>">
    <div class="dropmenu">
        <button @click="logoContextMenu.copyToClipboard(document.getElementById('muxi-logo').outerHTML)">
            <svg viewBox="0 0 1000 1050" xmlns="http://www.w3.org/2000/svg" class="scale-105 -mt-px mb-px mr-1.25!">
                <path fill="currentColor" d="M941.05,236.38L558.9,15.75C541,5.45,520.63,0,500,0s-41,5.44-58.95,15.77L142.65,188.05l-19.06,11-64.65,37.33C22.58,257.37,0,296.52,0,338.54v441.16c0,41.95,22.57,81.09,58.94,102.17l382.07,220.58c17.84,10.33,38.24,15.8,58.99,15.8s41.15-5.47,58.94-15.78l382.16-220.63c36.33-21.05,58.9-60.19,58.9-102.14v-441.16c0-42.02-22.59-81.17-58.95-102.16ZM929.16,779.7c0,16.77-9.03,32.42-23.52,40.82l-382.17,220.63c-14.12,8.19-32.78,8.22-47-.03l-331.26-191.24-21.62-12.48-29.18-16.85c-14.54-8.43-23.58-24.08-23.58-40.85v-441.16c0-16.79,9.02-32.44,23.53-40.82l29.23-16.87,160.74-92.8,192.1-110.9c7.17-4.13,15.32-6.31,23.57-6.31s16.39,2.18,23.52,6.28l382.12,220.6c14.51,8.38,23.52,24.02,23.52,40.82v441.16Z"/>
                <path fill="currentColor" d="M773.02,633.88c-.6,28.89,2.48,57.43-10.82,83.13-13.51,24.64-40.79,41.49-59.15,32.28-38.15-19.68-1.52-116.52-75.19-77.97-15.99,9.36-30.31,23.78-40.33,39.72-20.39,32.13-18.41,69.06-39.87,98.87-10.92,14.97-29.29,22.41-47.66,22.46-18.37-.05-36.74-7.49-47.66-22.46-21.46-29.81-19.48-66.74-39.87-98.87-10.02-15.94-24.34-30.36-40.33-39.72-73.67-38.55-37.04,58.29-75.19,77.97-18.36,9.21-45.64-7.64-59.15-32.28-13.3-25.7-10.22-54.24-10.82-83.13v-198.31c3.29-37.29,37.24-60.65,69.01-79.06,24.84-14.37,71.65-41.39,71.65-41.39,42.35-25.86,85.2-29.45,132.36-29.3,47.16-.15,90.01,3.44,132.36,29.3,0,0,46.81,27.02,71.65,41.39,31.77,18.41,65.72,41.77,69.01,79.06v198.31Z"/>
            </svg>
            <span>Copy Logo as SVG</span>
        </button>
        <button @click="logoContextMenu.copyToClipboard(document.getElementById('muxi-wordmark').outerHTML)">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="25" viewBox="0 0 24 25" fill="none">
                <path d="M20 18.2136V6.21362M6 20.2136H18M18 4.21362H6M4 6.21362V18.2136" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                <path d="M7.99901 10.2136C7.70512 8.64491 8.73403 8.2731 11.9564 8.21362M11.9564 8.21362C14.9534 8.28097 16.1887 8.51896 15.9138 10.2136M11.9564 8.21362V16.2136M10.4724 16.2136H13.4405" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                <path d="M21 2.21362H19C18.4477 2.21362 18 2.66134 18 3.21362V5.21362C18 5.76591 18.4477 6.21362 19 6.21362H21C21.5523 6.21362 22 5.76591 22 5.21362V3.21362C22 2.66134 21.5523 2.21362 21 2.21362Z" stroke="currentColor" stroke-width="1.5"></path>
                <path d="M5 2.21362H3C2.44772 2.21362 2 2.66134 2 3.21362V5.21362C2 5.76591 2.44772 6.21362 3 6.21362H5C5.55228 6.21362 6 5.76591 6 5.21362V3.21362C6 2.66134 5.55228 2.21362 5 2.21362Z" stroke="currentColor" stroke-width="1.5"></path>
                <path d="M21 18.2136H19C18.4477 18.2136 18 18.6613 18 19.2136V21.2136C18 21.7659 18.4477 22.2136 19 22.2136H21C21.5523 22.2136 22 21.7659 22 21.2136V19.2136C22 18.6613 21.5523 18.2136 21 18.2136Z" stroke="currentColor" stroke-width="1.5"></path>
                <path d="M5 18.2136H3C2.44772 18.2136 2 18.6613 2 19.2136V21.2136C2 21.7659 2.44772 22.2136 3 22.2136H5C5.55228 22.2136 6 21.7659 6 21.2136V19.2136C6 18.6613 5.55228 18.2136 5 18.2136Z" stroke="currentColor" stroke-width="1.5"></path>
            </svg>
            <span>Copy Wordmark as SVG</span>
        </button>
        <hr>
        <a href="https://muxi.org" target="_blank" @click="logoContextMenu.hide()">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                <path d="M22 17V11.845C22 10.433 22 9.72701 21.8204 9.07517C21.6613 8.49771 21.3998 7.95353 21.0483 7.46857C20.6514 6.92115 20.1001 6.48011 18.9976 5.59805L16.9976 3.99805C15.214 2.57118 14.3222 1.85774 13.3332 1.58413C12.4608 1.34279 11.5392 1.34279 10.6668 1.58413C9.67783 1.85774 8.78603 2.57118 7.00244 3.99805L5.00244 5.59805C3.89986 6.48011 3.34857 6.92115 2.95174 7.46857C2.6002 7.95353 2.33865 8.49771 2.17957 9.07517C2 9.72701 2 10.433 2 11.845V17C2 19.7614 4.23858 22 7 22C8.10457 22 9 21.1046 9 20V15.9999C9 14.3431 10.3431 12.9999 12 12.9999C13.6569 12.9999 15 14.3431 15 15.9999V20C15 21.1046 15.8954 22 17 22C19.7614 22 22 19.7614 22 17Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"></path>
            </svg>
            <span>Home</span>
        </a>
        <a href="/" @click="logoContextMenu.hide()">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                <path d="M2.5 12C2.5 7.52166 2.5 5.28249 3.89124 3.89124C5.28249 2.5 7.52166 2.5 12 2.5C16.4783 2.5 18.7175 2.5 20.1088 3.89124C21.5 5.28249 21.5 7.52166 21.5 12C21.5 16.4783 21.5 18.7175 20.1088 20.1088C18.7175 21.5 16.4783 21.5 12 21.5C7.52166 21.5 5.28249 21.5 3.89124 20.1088C2.5 18.7175 2.5 16.4783 2.5 12Z" stroke="currentColor" stroke-width="1.5"></path>
                <path d="M2.5 12H21.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                <path d="M13 7L17 7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                <circle cx="8.25" cy="7" r="1.25" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></circle>
                <circle cx="8.25" cy="17" r="1.25" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></circle>
                <path d="M13 17L17 17" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
            </svg>
            <span>Registry</span>
        </a>
        <a href="https://muxi.org/docs" target="_blank" @click="logoContextMenu.hide()">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 256" class="scale-115 mr-1.25!">
                <rect width="256" height="256" fill="none"></rect>
                <rect x="48" y="40" width="64" height="176" rx="8" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"></rect>
                <path d="M217.67,205.77l-46.81,10a8,8,0,0,1-9.5-6.21L128.18,51.8a8.07,8.07,0,0,1,6.15-9.57l46.81-10a8,8,0,0,1,9.5,6.21L223.82,196.2A8.07,8.07,0,0,1,217.67,205.77Z" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"></path>
                <line x1="48" y1="72" x2="112" y2="72" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"></line>
                <line x1="48" y1="184" x2="112" y2="184" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"></line>
                <line x1="133.16" y1="75.48" x2="195.61" y2="62.06" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"></line>
                <line x1="139.79" y1="107.04" x2="202.25" y2="93.62" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"></line>
                <line x1="156.39" y1="185.94" x2="218.84" y2="172.52" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"></line>
            </svg>
            <span>Documentation</span>
        </a>
        <a href="https://muxi.org/github" target="_blank" @click="logoContextMenu.hide()">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor" class="scale-105">
                <path d="M14.5094 20.9056C14.5198 20.402 14.5349 19.6585 14.5349 19C14.5349 18 13.8548 17.0818 13.8548 17.0818C16.1129 16.834 18.4919 15.5037 18.4919 11.5393C18.4919 10.383 18.0887 9.84616 17.4435 9.10284L17.4579 9.0525C17.5588 8.7032 17.8583 7.66631 17.3226 6.29474C16.4758 6.00567 14.5403 7.40972 14.5403 7.40972C13.7339 7.16195 12.8871 7.07936 12 7.07936C11.1532 7.07936 10.3065 7.16195 9.5 7.40972C9.5 7.40972 7.52419 6.04697 6.71774 6.29474C6.15323 7.74009 6.47581 8.81377 6.59677 9.10284C5.95161 9.84616 5.62903 10.383 5.62903 11.5393C5.62903 15.5037 7.92742 16.834 10.1855 17.0818C10.1855 17.0818 9.5 17.8624 9.5 18.9312V20.9082V22.4578C4.76861 21.3309 1.25 17.0764 1.25 12C1.25 6.06294 6.06294 1.25 12 1.25C17.9371 1.25 22.75 6.06294 22.75 12C22.75 17.073 19.2361 21.3252 14.5094 22.4555V20.9056Z"></path>
            </svg>
            <span>Star on GitHub</span>
        </a>
        <hr>
        <a href="https://muxi.org/support" target="_blank" @click="logoContextMenu.hide()">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M12.0002 4C8.74885 4 6.31489 6.09486 6.02849 8.52063C6.15063 8.57303 6.26834 8.62684 6.37501 8.67566C6.73116 8.83478 7.40272 9.13482 7.6585 9.89191C7.75152 10.1672 7.75079 10.4616 7.75011 10.7355V15.2645C7.75079 15.5384 7.75152 15.8327 7.6585 16.1081C7.40272 16.8652 6.73116 17.1652 6.37501 17.3243C6.0121 17.4904 5.52133 17.7144 5.16362 17.7434C4.76625 17.7755 4.36396 17.6906 4.01491 17.4947C3.69791 17.3168 3.45992 17.0265 3.21887 16.7323C3.13171 16.6265 2.96671 16.4318 2.85082 16.2975L2.8508 16.2975L2.8508 16.2975L2.85075 16.2975C2.63889 16.052 2.39839 15.7733 2.20005 15.5137C1.8724 15.0849 1.54407 14.5711 1.38098 13.9741C1.20634 13.3348 1.20634 12.6652 1.38098 12.0259C1.49932 11.5927 1.71345 11.2104 1.99611 10.8091C2.26984 10.4205 2.73604 9.85098 3.19205 9.29711L3.19206 9.2971C3.26558 9.20386 3.36665 9.07569 3.44139 8.99009C3.57673 8.83505 3.76401 8.64608 4.01491 8.50526L4.0196 8.50263C4.31352 4.74952 7.91074 2 12.0002 2C16.0897 2 19.6869 4.74952 19.9809 8.50264L19.9855 8.50526C20.2364 8.64608 20.4237 8.83505 20.5591 8.99009C20.6338 9.07569 20.7349 9.20386 20.8084 9.2971L20.8084 9.29711C21.2644 9.85098 21.7306 10.4205 22.0044 10.8091C22.287 11.2104 22.5011 11.5927 22.6195 12.0259C22.7941 12.6652 22.7941 13.3348 22.6195 13.9741C22.4564 14.5711 22.1281 15.0849 21.8004 15.5137C21.6021 15.7733 21.3616 16.052 21.1497 16.2975L21.1497 16.2975L21.1496 16.2975C21.0338 16.4318 20.8688 16.6265 20.7816 16.7323L20.7816 16.7324C20.5443 17.0219 20.3099 17.3078 20.0002 17.4864V17.8C20.0002 20.3163 17.5419 22 15.0002 22H13.0002C12.4479 22 12.0002 21.5523 12.0002 21C12.0002 20.4477 12.4479 20 13.0002 20H15.0002C16.8768 20 18.0002 18.8183 18.0002 17.8V17.4914C17.868 17.4353 17.7403 17.3769 17.6255 17.3243C17.2693 17.1652 16.5977 16.8652 16.342 16.1081C16.2489 15.8327 16.2497 15.5384 16.2503 15.2645V10.7355C16.2497 10.4616 16.2489 10.1672 16.342 9.89191C16.5977 9.13482 17.2693 8.83478 17.6255 8.67566C17.7321 8.62684 17.8498 8.57303 17.972 8.52063C17.6856 6.09486 15.2516 4 12.0002 4Z" fill="currentColor"></path>
            </svg>
            <span>Get priority support</span>
        </a>
    </div>
</div>
<script>
    var logoContextMenu = logoContextMenu || {
        visible: false,
        copyToClipboard: (data) => {
            // remove all class="" and id="" from data
            data = data.replace(/class="[^"]*"/g, '').replace(/id="[^"]*"/g, '');
            navigator.clipboard.writeText(data);
            logoContextMenu.hide()
        },
        show: () => {
            document.getElementById('logo-context-menu').classList.add('showing');
            // document.getElementById('logo-context-menu').classList.add('reveal');
            logoContextMenu.visible = true;
        },
        hide: () => {
            // document.getElementById('logo-context-menu').classList.remove('reveal');
            document.getElementById('logo-context-menu').classList.remove('showing');
            logoContextMenu.visible = false;
        },
        toggle: (event) => {
            event.preventDefault();
            event.stopPropagation();
            event.stopImmediatePropagation();
            if (logoContextMenu.visible) {
                logoContextMenu.hide();
            } else {
                logoContextMenu.show();
            }
        }
    };
</script>
