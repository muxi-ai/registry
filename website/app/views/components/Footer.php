<?php
tiny::components()->register('Footer', function (...$props) {
    $year = date('Y');
    return '
    <footer id="footer" class="w-full container @container">
        <div class="mt-20 pt-8 lg:border-t dark:border-white/5">
            <div class="lg:flex gap-4 mb-3 lg:gap-20">
                <div class="col-span-full lg:w-5/12 mt-4">
                    <a href="https://muxi.org/" class="text-xl font-black leading-none text-gray-900 select-none logo">
                        <svg viewBox="0 0 460 600" xmlns="http://www.w3.org/2000/svg" class="h-10" id="muxi-logo">
                            <g fill="currentColor" class="text-[#c88b45] dark:text-[#e8a550]">
                                <path d="m439.7 71.99-62.27 35.88-58.06 137.03-80.57 46.53v61.4l118.29-68.1 28.59-55.85v103.3h-19.9v19.9h19.9v19.9h-19.9v19.9h19.9v34.57l54.02-31.17v-146.5h-19.9v-19.9h19.9v-19.9h-19.9v-19.9h19.9v-19.9h-19.9v-19.9h19.9z" opacity=".1" />
                                <path d="m286.05 241.18-125.06-95.96-68.12 39.51v110.49h-19.9v19.9h19.9v19.9h-19.9v19.9h19.9v19.9h-19.9v19.9h19.9l.47 176.45 52.59-30.33v-328.8l82.92 62.14z" opacity=".1" />
                                <path d="m297.45 43.44-58.46 138.95-77.82-60.27-79.04-44.59-82.13 47.42v269.77h19.9v19.9h-19.9v19.9h19.9v19.9h-19.9v19.9h19.9v20.85h-19.9v56.99l83.14 47.84 82.69-47.69s.19-213.5.57-213.5c.57 0 62.45 42.83 62.45 42.83l63.96-36.94v12.11s0 19.9 0 19.9v30.22s82.96 48.22 82.96 48.22l83.82-48.41v-157.96s-19.9 0-19.9 0v-19.9h19.9v-19.9s-19.9 0-19.9 0v-19.9h19.9v-19.9s-19.9 0-19.9 0v-19.9h19.9v-99.27s-86.61-50.01-86.61-50.01zm131.56 11.74-51.44 29.7-55.88-32.26 51.31-29.62zm-68.85 42.71-56.57 134.08-48.53-37.25 52.7-127.09zm79.54 51.39h-19.9v19.9h19.9v19.9h-19.9v19.9h19.9v19.9h-19.9v19.9h19.9v146.49l-54.01 31.18v-34.58h-19.9v-19.9h19.9v-19.9h-19.9v-19.9h19.9v-103.29l-28.6 55.85-118.28 68.1v-61.41l80.57-46.53 58.06-137.03 62.27-35.88v77.29zm-297.73-16.21-58.83 34.25-58.32-33.72 58.87-34.17 58.28 33.65zm144.08 108.11-57.2 33-82.92-62.14v328.8s-52.59 30.32-52.59 30.32l-.47-176.45h-19.9v-19.9h19.9v-19.9h-19.9v-19.9h19.9v-19.9h-19.9v-19.9h19.9v-110.49s68.13-39.51 68.13-39.51l125.06 95.96zm-213.08-56.81v110.85h-19.9v19.9h19.9v19.9h-19.9v19.9h19.9v19.9h-19.9v19.9h19.9s0 176.45 0 176.45l-53.07-30.5v-45.5h19.9v-20.85h-19.9v-19.9h19.9v-19.9h-19.9v-19.9h19.9v-19.9h-19.9v-240.99zm145.93 107.06v61.41s-53.07-37.24-53.07-37.24v-63.09m199.95 79.67h-19.9v19.9h19.9v19.9h-19.9v19.9h19.9v33.34l-53.07-30.04v-61.97s53.07-30.64 53.07-30.64v29.6z" />
                            </g>
                        </svg>
                    </a>
                    <p class="my-6 text-sm text-black opacity-70">
                        MUXI is open-source infrastructure for running AI agents.
                        Licensed under <a class="text-bright hover:border-b-2 font-medium" target="_blank" href="https://muxi.org/licensing">Elastic License 2.0 and Apache 2.0</a>.
                    </p>
                    <p class="pt-2 flex items-center space-x-4 [&>a]:hover:scale-125 [&>a]:transform-scale [&>a]:duration-300 [&>a]:text-black [&>a]:dark:text-[#fcf0df] [&>a]:opacity-50 [&>a]:hover:opacity-100">
                        <a target="_blank" href="https://muxi.org/github" target="github">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor" class="size-6">
                                <path d="M14.5094 20.9056C14.5198 20.402 14.5349 19.6585 14.5349 19C14.5349 18 13.8548 17.0818 13.8548 17.0818C16.1129 16.834 18.4919 15.5037 18.4919 11.5393C18.4919 10.383 18.0887 9.84616 17.4435 9.10284L17.4579 9.0525C17.5588 8.7032 17.8583 7.66631 17.3226 6.29474C16.4758 6.00567 14.5403 7.40972 14.5403 7.40972C13.7339 7.16195 12.8871 7.07936 12 7.07936C11.1532 7.07936 10.3065 7.16195 9.5 7.40972C9.5 7.40972 7.52419 6.04697 6.71774 6.29474C6.15323 7.74009 6.47581 8.81377 6.59677 9.10284C5.95161 9.84616 5.62903 10.383 5.62903 11.5393C5.62903 15.5037 7.92742 16.834 10.1855 17.0818C10.1855 17.0818 9.5 17.8624 9.5 18.9312V20.9082V22.4578C4.76861 21.3309 1.25 17.0764 1.25 12C1.25 6.06294 6.06294 1.25 12 1.25C17.9371 1.25 22.75 6.06294 22.75 12C22.75 17.073 19.2361 21.3252 14.5094 22.4555V20.9056Z"></path>
                            </svg>
                        </a>
                        <a target="_blank" href="https://muxi.org/x" target="twitter">
                            <svg xmlns="http://www.w3.org/2000/svg" enable-background="new 0 0 24 24" viewBox="0 0 24 24" class="size-5.5">
                                <circle cx="12" cy="12" fill="currentColor" r="12"></circle>
                                <path d="m-47.8 30.1 5.7 7.7-5.8 6.2h1.3l5.1-5.5 4.1 5.5h4.4l-6.1-8.1 5.4-5.8h-1.3l-4.7 5-3.8-5zm1.9 1h2l9 12h-2z" fill="currentColor" stroke="currentColor" stroke-width="0.5" transform="translate(52.390088 -25.058597)" class="text-[#faf9f5] dark:text-[#131215]"></path>
                            </svg>
                        </a>
                        <a target="_blank" href="https://muxi.org/linkedin" target="linkedin">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" class="size-6">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M11.9428 1.75H12.0572C14.2479 1.74999 15.9686 1.74998 17.312 1.93059C18.6886 2.11568 19.7809 2.50271 20.6391 3.36091C21.4973 4.21911 21.8843 5.31137 22.0694 6.68802C22.25 8.03144 22.25 9.75214 22.25 11.9428V12.0572C22.25 14.2479 22.25 15.9686 22.0694 17.312C21.8843 18.6886 21.4973 19.7809 20.6391 20.6391C19.7809 21.4973 18.6886 21.8843 17.312 22.0694C15.9686 22.25 14.2479 22.25 12.0572 22.25H11.9428C9.7521 22.25 8.03144 22.25 6.68802 22.0694C5.31137 21.8843 4.21911 21.4973 3.36091 20.6391C2.50272 19.7809 2.11568 18.6886 1.93059 17.312C1.74998 15.9686 1.74999 14.2479 1.75 12.0572V12.0572V11.9428V11.9428C1.74999 9.75211 1.74998 8.03144 1.93059 6.68802C2.11568 5.31137 2.50272 4.21911 3.36091 3.36091C4.21911 2.50271 5.31137 2.11568 6.68802 1.93059C8.03143 1.74998 9.75214 1.74999 11.9428 1.75ZM8.00195 10.5C8.00195 9.94771 7.55424 9.5 7.00195 9.5C6.44967 9.5 6.00195 9.94771 6.00195 10.5L6.00195 17C6.00195 17.5523 6.44967 18 7.00195 18C7.55424 18 8.00195 17.5523 8.00195 17L8.00195 10.5ZM11.002 9C11.4073 9 11.7564 9.2412 11.9134 9.58791C12.5213 9.215 13.2365 9 14.002 9C16.2111 9 18.002 10.7909 18.002 13V17C18.002 17.5523 17.5542 18 17.002 18C16.4497 18 16.002 17.5523 16.002 17V13C16.002 11.8954 15.1065 11 14.002 11C12.8974 11 12.002 11.8954 12.002 13L12.002 17C12.002 17.5523 11.5542 18 11.002 18C10.4497 18 10.002 17.5523 10.002 17L10.002 10C10.002 9.44771 10.4497 9 11.002 9ZM8.25977 7C8.25977 7.69036 7.70012 8.25 7.00977 8.25H7.00078C6.31043 8.25 5.75078 7.69036 5.75078 7C5.75078 6.30964 6.31043 5.75 7.00078 5.75H7.00977C7.70012 5.75 8.25977 6.30964 8.25977 7Z" fill="currentColor"></path>
                            </svg>
                        </a>
                        <!--
                        <a target="_blank" href="https://muxi.org/youtube" target="youtube">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" class="size-6.25">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M6.52147 2.54078C7.94527 2.31166 9.74786 2.25 12 2.25C14.2521 2.25 16.0547 2.31166 17.4785 2.54078C18.9034 2.77007 20.0377 3.17979 20.8795 3.94505C21.7307 4.71887 22.1881 5.76906 22.4396 7.07713C22.6888 8.3728 22.75 9.99883 22.75 12C22.75 14.0012 22.6888 15.6273 22.4396 16.9229C22.1881 18.231 21.7307 19.2812 20.8795 20.055C20.0377 20.8203 18.9034 21.23 17.4785 21.4593C16.0547 21.6884 14.2521 21.75 12 21.75C9.74786 21.75 7.94527 21.6884 6.52147 21.4593C5.09658 21.23 3.96228 20.8203 3.1205 20.055C2.26929 19.2812 1.81192 18.231 1.56037 16.9229C1.3112 15.6273 1.25 14.0012 1.25 12C1.25 9.99883 1.3112 8.3728 1.56037 7.07713C1.81192 5.76906 2.26929 4.71887 3.12049 3.94505C3.96228 3.17979 5.09658 2.77007 6.52147 2.54078ZM9.63048 8.34735C9.86561 8.21422 10.1542 8.21786 10.3859 8.35688L15.3859 11.3569C15.6118 11.4924 15.75 11.7366 15.75 12C15.75 12.2634 15.6118 12.5076 15.3859 12.6431L10.3859 15.6431C10.1542 15.7821 9.86561 15.7858 9.63048 15.6526C9.39534 15.5195 9.25 15.2702 9.25 15V9C9.25 8.7298 9.39534 8.48048 9.63048 8.34735Z" fill="currentColor"></path>
                            </svg>
                        </a>
                        -->
                        <a target="_blank" href="https://muxi.org/luma" target="luma" class="-ml-0.5" data-side="right" data-tooltip="Attend a live workshop">
                            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" class="size-6 -mr-4">
                                <path fill="currentColor" d="m24 12c-6.63 0-12-5.37-12-12 0 6.63-5.37 12-12 12 6.63 0 12 5.37 12 12 0-6.63 5.37-12 12-12z"/>
                            </svg>
                        </a>
                    </p>
                    <p class="text-xs leading-normal">
                        <a href="https://muxi.org/llms.txt" class="opacity-50 hover:opacity-100">Are you an AI? Here\'s <code>llms.txt</code></a><br>
                        <a href="https://muxi.org/llm-status" class="flex items-center space-x-1.5 pt-1"><span class="opacity-60 hover:opacity-100">LLM Status </span><img src="https://muxi.org/llm-status/badge" alt="status" class="inline size-2.5 mt-px"></a>
                    </p>
                </div>
                <div class="flex gap-6 lg:w-7/12 items-start">
                    <nav class="w-1/3 [&>a]:flex [&>a]:mb-2 [&>a]:text-xs lg:[&>a]:text-sm [&>a]:font-normal [&>a]:text-inherit [&>a]:transition-all [&>a]:duration-300 [&>a]:opacity-70 [&>a]:hover:opacity-100 [&>a]:hover:font-medium [&>a]:hover:after:content-[\'→\'] [&>a]:hover:after:translate-x-1 [&>a]:hover:after:transition-transform [&>a]:hover:after:duration-300">
                        <p class="mb-3 text-xs tracking-wider text-bright opacity-70 uppercase font-semibold">Stack</p>
                        <a href="https://muxi.org/docs/server/">MUXI Server</a>
                        <a href="https://muxi.org/docs/runtime/">MUXI Runtime</a>
                        <a href="https://muxi.org/docs/registry/">MUXI Registry</a>
                        <a href="https://muxi.org/docs/cli/">MUXI CLI</a>
                        <a href="https://muxi.org/docs/sdks/">MUXI SDKs</a>
                    </nav>
                    <nav class="w-1/3 [&>a]:flex [&>a]:mb-2 [&>a]:text-xs lg:[&>a]:text-sm [&>a]:font-normal [&>a]:text-inherit [&>a]:transition-all [&>a]:duration-300 [&>a]:opacity-70 [&>a]:hover:opacity-100 [&>a]:hover:font-medium [&>a]:hover:after:content-[\'↗\'] [&>a]:hover:after:translate-x-1 [&>a]:hover:after:transition-transform [&>a]:hover:after:duration-300">
                        <p class="mb-3 text-xs tracking-wider text-bright opacity-70 uppercase font-semibold">Resources</p>
                        <a target="_blank" href="https://muxi.org/luma">Workshops</a>
                        <a target="_blank" href="https://muxi.org/community">Discussions</a>
                        <a target="_blank" href="https://muxi.org/changelog">Changelog</a>
                        <a target="_blank" href="https://muxi.org/roadmap">Roadmap</a>
                        <a target="_blank" href="https://muxi.org/contributing">Contributing</a>
                        <a target="_blank" href="https://muxi.org/sponsors" class="whitespace-nowrap opacity-90! hover:opacity-100!">GitHub <span class="animate-rainbow-text ml-1">Sponsors</span></a>
                    </nav>
                    <nav class="w-1/3 [&>a]:flex [&>a]:mb-2 [&>a]:text-xs lg:[&>a]:text-sm [&>a]:font-normal [&>a]:text-inherit [&>a]:transition-all [&>a]:duration-300 [&>a]:opacity-70 [&>a]:hover:opacity-100 [&>a]:hover:font-medium [&>a]:hover:after:content-[\'›\'] [&>a]:hover:after:translate-x-1 [&>a]:hover:after:transition-transform [&>a]:hover:after:duration-300">
                        <p class="mb-3 text-xs tracking-wider text-bright opacity-70 uppercase font-semibold">Get Started</p>
                        <a href="https://muxi.org/docs/quickstart">Quickstart</a>
                        <a href="https://muxi.org/docs/how-it-works">How MUXI Works</a>
                        <a href="https://muxi.org/docs/examples/">Examples</a>
                        <a href="/">Recipes</a>
                        <a href="https://muxi.org/docs">Docs</a>
                    </nav>
                </div>
            </div>
        </div>

        <div class="pt-10 mt-10 pb-12 mx-auto max-w-7xl text-[13px] text-center leading-relaxed! border-t dark:border-white/5 group">
            <span class="opacity-60">Copyright &copy; '. $year .' </span><a href="https://varops.com" target="_blank" class="transition-opacity duration-300 opacity-60 hover:opacity-100">VarOps LLC</a>
            <span class="hidden md:inline opacity-60">&nbsp; &bull; &nbsp;</span>
            <br class="block md:hidden">
            <a href="https://muxi.org/privacy" class="transition-opacity duration-300 opacity-60 hover:opacity-100">Privacy</a>
            <span class="opacity-60">&nbsp;/&nbsp; </span>
            <a href="https://muxi.org/terms" class="transition-opacity duration-300 opacity-60 hover:opacity-100">Terms</a>
            <span class="opacity-60">&nbsp;/&nbsp; </span>
            <a href="https://muxi.org/support" class="transition-opacity duration-300 opacity-60 hover:opacity-100">Support</a>
        </div>
    </footer>';
});
/* <p class="text-xs opacity-50">* Company names shown based on anonymized signup domains.<br>Their appearance does not imply endorsement or partnership.</p> */
