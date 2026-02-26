<?php
tiny::components()->register('Footer', function (...$props) {
    $year = date('Y');

    $llm_prompt = "I want to understand what MUXI Registry (registry.muxi.org) is and what it can be used for.\n\n";
    $llm_prompt .= "Review https://muxi.org, https://registry.muxi.org, and https://registry.muxi.org/llms.txt to learn about the platform. Give me a concise overview of what MUXI includes: what it is, how it relates to Agent Formations, and the docker-hub analogy.\n\n";
    $llm_prompt .= "Explain it simply and concisely, as if you were describing the real experience of using the platform to a technical audience, and make sure to review MUXI's unique features and capabilities. At the end, suggest follow-up questions I can ask to go deeper into specific areas.";

    $llm_prompt = rawurlencode($llm_prompt);

    return '
    <footer id="footer" class="w-full container @container">
        <div class="mt-20 pt-8 lg:border-t dark:border-white/5">
            <div class="lg:flex gap-4 mb-3 lg:gap-20">
                <div class="col-span-full lg:w-5/12 mt-4">
                    <a href="https://muxi.org/" class="text-xl font-black leading-none text-gray-900 select-none logo block w-fit hover:scale-105 tranision-all duration-150">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1118.25" class="text-[#c88b45] dark:text-[#e8a550] size-10" id="muxi-logo">
                            <path fill="currentColor" d="M941.05,236.38L558.9,15.75C541,5.45,520.63,0,500,0s-41,5.44-58.95,15.77L142.65,188.05l-19.06,11-64.65,37.33C22.58,257.37,0,296.52,0,338.54v441.16c0,41.95,22.57,81.09,58.94,102.17l382.07,220.58c17.84,10.33,38.24,15.8,58.99,15.8s41.15-5.47,58.94-15.78l382.16-220.63c36.33-21.05,58.9-60.19,58.9-102.14v-441.16c0-42.02-22.59-81.17-58.95-102.16ZM929.16,779.7c0,16.77-9.03,32.42-23.52,40.82l-382.17,220.63c-14.12,8.19-32.78,8.22-47-.03l-331.26-191.24-21.62-12.48-29.18-16.85c-14.54-8.43-23.58-24.08-23.58-40.85v-441.16c0-16.79,9.02-32.44,23.53-40.82l29.23-16.87,160.74-92.8,192.1-110.9c7.17-4.13,15.32-6.31,23.57-6.31s16.39,2.18,23.52,6.28l382.12,220.6c14.51,8.38,23.52,24.02,23.52,40.82v441.16Z"/>
                            <path fill="currentColor" d="M773.02,633.88c-.6,28.89,2.48,57.43-10.82,83.13-13.51,24.64-40.79,41.49-59.15,32.28-38.15-19.68-1.52-116.52-75.19-77.97-15.99,9.36-30.31,23.78-40.33,39.72-20.39,32.13-18.41,69.06-39.87,98.87-10.92,14.97-29.29,22.41-47.66,22.46-18.37-.05-36.74-7.49-47.66-22.46-21.46-29.81-19.48-66.74-39.87-98.87-10.02-15.94-24.34-30.36-40.33-39.72-73.67-38.55-37.04,58.29-75.19,77.97-18.36,9.21-45.64-7.64-59.15-32.28-13.3-25.7-10.22-54.24-10.82-83.13v-198.31c3.29-37.29,37.24-60.65,69.01-79.06,24.84-14.37,71.65-41.39,71.65-41.39,42.35-25.86,85.2-29.45,132.36-29.3,47.16-.15,90.01,3.44,132.36,29.3,0,0,46.81,27.02,71.65,41.39,31.77,18.41,65.72,41.77,69.01,79.06v198.31Z"/>
                        </svg>
                    </a>
                    <p class="my-6 text-sm text-black opacity-70">
                        MUXI is open-source AI application server.
                        Licensed under <a class="text-bright hover:border-b-2 font-medium" target="_blank" href="https://muxi.org/licensing">Elastic License 2.0 and Apache 2.0</a>.
                    </p>
                    <p class="pt-2 flex items-center space-x-4 [&>a]:hover:scale-125 [&>a]:transform-scale [&>a]:duration-300 [&>a]:text-black [&>a]:dark:text-[#fcf0df] [&>a]:opacity-50 [&>a]:hover:opacity-100">
                        <a target="_blank" href="https://muxi.org/go/github" target="github">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor" class="size-6">
                                <path d="M14.5094 20.9056C14.5198 20.402 14.5349 19.6585 14.5349 19C14.5349 18 13.8548 17.0818 13.8548 17.0818C16.1129 16.834 18.4919 15.5037 18.4919 11.5393C18.4919 10.383 18.0887 9.84616 17.4435 9.10284L17.4579 9.0525C17.5588 8.7032 17.8583 7.66631 17.3226 6.29474C16.4758 6.00567 14.5403 7.40972 14.5403 7.40972C13.7339 7.16195 12.8871 7.07936 12 7.07936C11.1532 7.07936 10.3065 7.16195 9.5 7.40972C9.5 7.40972 7.52419 6.04697 6.71774 6.29474C6.15323 7.74009 6.47581 8.81377 6.59677 9.10284C5.95161 9.84616 5.62903 10.383 5.62903 11.5393C5.62903 15.5037 7.92742 16.834 10.1855 17.0818C10.1855 17.0818 9.5 17.8624 9.5 18.9312V20.9082V22.4578C4.76861 21.3309 1.25 17.0764 1.25 12C1.25 6.06294 6.06294 1.25 12 1.25C17.9371 1.25 22.75 6.06294 22.75 12C22.75 17.073 19.2361 21.3252 14.5094 22.4555V20.9056Z"></path>
                            </svg>
                        </a>
                        <a target="_blank" href="https://muxi.org/go/x" target="twitter">
                            <svg xmlns="http://www.w3.org/2000/svg" enable-background="new 0 0 24 24" viewBox="0 0 24 24" class="size-5.5">
                                <circle cx="12" cy="12" fill="currentColor" r="12"></circle>
                                <path d="m-47.8 30.1 5.7 7.7-5.8 6.2h1.3l5.1-5.5 4.1 5.5h4.4l-6.1-8.1 5.4-5.8h-1.3l-4.7 5-3.8-5zm1.9 1h2l9 12h-2z" fill="currentColor" stroke="currentColor" stroke-width="0.5" transform="translate(52.390088 -25.058597)" class="text-[#faf9f5] dark:text-[#131215]"></path>
                            </svg>
                        </a>
                        <a target="_blank" href="https://muxi.org/go/linkedin" target="linkedin">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" class="size-6">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M11.9428 1.75H12.0572C14.2479 1.74999 15.9686 1.74998 17.312 1.93059C18.6886 2.11568 19.7809 2.50271 20.6391 3.36091C21.4973 4.21911 21.8843 5.31137 22.0694 6.68802C22.25 8.03144 22.25 9.75214 22.25 11.9428V12.0572C22.25 14.2479 22.25 15.9686 22.0694 17.312C21.8843 18.6886 21.4973 19.7809 20.6391 20.6391C19.7809 21.4973 18.6886 21.8843 17.312 22.0694C15.9686 22.25 14.2479 22.25 12.0572 22.25H11.9428C9.7521 22.25 8.03144 22.25 6.68802 22.0694C5.31137 21.8843 4.21911 21.4973 3.36091 20.6391C2.50272 19.7809 2.11568 18.6886 1.93059 17.312C1.74998 15.9686 1.74999 14.2479 1.75 12.0572V12.0572V11.9428V11.9428C1.74999 9.75211 1.74998 8.03144 1.93059 6.68802C2.11568 5.31137 2.50272 4.21911 3.36091 3.36091C4.21911 2.50271 5.31137 2.11568 6.68802 1.93059C8.03143 1.74998 9.75214 1.74999 11.9428 1.75ZM8.00195 10.5C8.00195 9.94771 7.55424 9.5 7.00195 9.5C6.44967 9.5 6.00195 9.94771 6.00195 10.5L6.00195 17C6.00195 17.5523 6.44967 18 7.00195 18C7.55424 18 8.00195 17.5523 8.00195 17L8.00195 10.5ZM11.002 9C11.4073 9 11.7564 9.2412 11.9134 9.58791C12.5213 9.215 13.2365 9 14.002 9C16.2111 9 18.002 10.7909 18.002 13V17C18.002 17.5523 17.5542 18 17.002 18C16.4497 18 16.002 17.5523 16.002 17V13C16.002 11.8954 15.1065 11 14.002 11C12.8974 11 12.002 11.8954 12.002 13L12.002 17C12.002 17.5523 11.5542 18 11.002 18C10.4497 18 10.002 17.5523 10.002 17L10.002 10C10.002 9.44771 10.4497 9 11.002 9ZM8.25977 7C8.25977 7.69036 7.70012 8.25 7.00977 8.25H7.00078C6.31043 8.25 5.75078 7.69036 5.75078 7C5.75078 6.30964 6.31043 5.75 7.00078 5.75H7.00977C7.70012 5.75 8.25977 6.30964 8.25977 7Z" fill="currentColor"></path>
                            </svg>
                        </a>
                        <!--
                        <a target="_blank" href="https://muxi.org/go/youtube" target="youtube">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" class="size-6.25">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M6.52147 2.54078C7.94527 2.31166 9.74786 2.25 12 2.25C14.2521 2.25 16.0547 2.31166 17.4785 2.54078C18.9034 2.77007 20.0377 3.17979 20.8795 3.94505C21.7307 4.71887 22.1881 5.76906 22.4396 7.07713C22.6888 8.3728 22.75 9.99883 22.75 12C22.75 14.0012 22.6888 15.6273 22.4396 16.9229C22.1881 18.231 21.7307 19.2812 20.8795 20.055C20.0377 20.8203 18.9034 21.23 17.4785 21.4593C16.0547 21.6884 14.2521 21.75 12 21.75C9.74786 21.75 7.94527 21.6884 6.52147 21.4593C5.09658 21.23 3.96228 20.8203 3.1205 20.055C2.26929 19.2812 1.81192 18.231 1.56037 16.9229C1.3112 15.6273 1.25 14.0012 1.25 12C1.25 9.99883 1.3112 8.3728 1.56037 7.07713C1.81192 5.76906 2.26929 4.71887 3.12049 3.94505C3.96228 3.17979 5.09658 2.77007 6.52147 2.54078ZM9.63048 8.34735C9.86561 8.21422 10.1542 8.21786 10.3859 8.35688L15.3859 11.3569C15.6118 11.4924 15.75 11.7366 15.75 12C15.75 12.2634 15.6118 12.5076 15.3859 12.6431L10.3859 15.6431C10.1542 15.7821 9.86561 15.7858 9.63048 15.6526C9.39534 15.5195 9.25 15.2702 9.25 15V9C9.25 8.7298 9.39534 8.48048 9.63048 8.34735Z" fill="currentColor"></path>
                            </svg>
                        </a>
                        -->
                        <a target="_blank" href="https://muxi.org/go/luma" target="luma" class="-ml-0.5" data-side="right" data-tooltip="Attend a live workshop">
                            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" class="size-6 -mr-4">
                                <path fill="currentColor" d="m24 12c-6.63 0-12-5.37-12-12 0 6.63-5.37 12-12 12 6.63 0 12 5.37 12 12 0-6.63 5.37-12 12-12z"/>
                            </svg>
                        </a>
                    </p>
                    <p class="text-xs leading-normal">
                        <a href="https://muxi.org/llms.txt" class="opacity-50 hover:opacity-100">Are you an AI? Here\'s <code>llms.txt</code></a><br>
                        <a href="https://muxi.org/llm-status" class="flex items-center space-x-1.5 pt-1"><span class="opacity-60 hover:opacity-100">LLM Status </span><img src="https://muxi.org/llm-status/badge.svg" alt="status" class="inline size-2.5 mt-px"></a>
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
                        <a target="_blank" href="https://muxi.org/community">Discussions</a>
                        <a target="_blank" href="https://muxi.org/changelog">Changelog</a>
                        <a target="_blank" href="https://muxi.org/roadmap">Roadmap</a>
                        <a target="_blank" href="https://muxi.org/contributing">Contributing</a>
                        <a target="_blank" href="https://muxi.org/sponsors" class="whitespace-nowrap opacity-90! hover:opacity-100!">GitHub <span class="animate-rainbow-text ml-1">Sponsors</span></a>
                        <a href="https://muxi.org/brand">Brand Assets</a>
                    </nav>
                    <nav class="w-1/3 [&>a]:flex [&>a]:mb-2 [&>a]:text-xs lg:[&>a]:text-sm [&>a]:font-normal [&>a]:text-inherit [&>a]:transition-all [&>a]:duration-300 [&>a]:opacity-70 [&>a]:hover:opacity-100 [&>a]:hover:font-medium [&>a]:hover:after:content-[\'›\'] [&>a]:hover:after:translate-x-1 [&>a]:hover:after:transition-transform [&>a]:hover:after:duration-300">
                        <p class="mb-3 text-xs tracking-wider text-bright opacity-70 uppercase font-semibold">Get Started</p>
                        <a href="https://muxi.org/docs/quickstart">Quickstart</a>
                        <a href="https://muxi.org/docs/how-it-works">How MUXI Works</a>
                        <a target="_blank" href="https://muxi.org/workshops">Workshops</a>
                        <a href="https://muxi.org/docs/examples/">Examples</a>
                        <a href="/">Recipes</a>
                        <a href="https://muxi.org/docs">Docs</a>
                    </nav>
                </div>
            </div>
        </div>

        <div class="pt-10 mt-10 pb-12 mx-auto max-w-7xl text-[13px] text-center leading-relaxed! border-t dark:border-white/5 group">

            <p class="text-xs opacity-80">Request an AI summary of MUXI</p>
            <div class="grid grid-cols-5 justify-center gap-2 w-fit mx-auto mb-10">
                <a aria-label="ChatGPT" href="https://chatgpt.com/?q='. $llm_prompt .'" target="_blank" rel="noopener noreferrer" title="Ask ChatGPT about MUXI"><svg class="size-6 p-0.5 opacity-80 hover:opacity-100" fill="currentColor" fill-rule="evenodd" height="1em" style="flex:none;line-height:1" viewBox="0 0 24 24" width="1em" xmlns="http://www.w3.org/2000/svg"><title>OpenAI</title><path d="M9.205 8.658v-2.26c0-.19.072-.333.238-.428l4.543-2.616c.619-.357 1.356-.523 2.117-.523 2.854 0 4.662 2.212 4.662 4.566 0 .167 0 .357-.024.547l-4.71-2.759a.797.797 0 00-.856 0l-5.97 3.473zm10.609 8.8V12.06c0-.333-.143-.57-.429-.737l-5.97-3.473 1.95-1.118a.433.433 0 01.476 0l4.543 2.617c1.309.76 2.189 2.378 2.189 3.948 0 1.808-1.07 3.473-2.76 4.163zM7.802 12.703l-1.95-1.142c-.167-.095-.239-.238-.239-.428V5.899c0-2.545 1.95-4.472 4.591-4.472 1 0 1.927.333 2.712.928L8.23 5.067c-.285.166-.428.404-.428.737v6.898zM12 15.128l-2.795-1.57v-3.33L12 8.658l2.795 1.57v3.33L12 15.128zm1.796 7.23c-1 0-1.927-.332-2.712-.927l4.686-2.712c.285-.166.428-.404.428-.737v-6.898l1.974 1.142c.167.095.238.238.238.428v5.233c0 2.545-1.974 4.472-4.614 4.472zm-5.637-5.303l-4.544-2.617c-1.308-.761-2.188-2.378-2.188-3.948A4.482 4.482 0 014.21 6.327v5.423c0 .333.143.571.428.738l5.947 3.449-1.95 1.118a.432.432 0 01-.476 0zm-.262 3.9c-2.688 0-4.662-2.021-4.662-4.519 0-.19.024-.38.047-.57l4.686 2.71c.286.167.571.167.856 0l5.97-3.448v2.26c0 .19-.07.333-.237.428l-4.543 2.616c-.619.357-1.356.523-2.117.523zm5.899 2.83a5.947 5.947 0 005.827-4.756C22.287 18.339 24 15.84 24 13.296c0-1.665-.713-3.282-1.998-4.448.119-.5.19-.999.19-1.498 0-3.401-2.759-5.947-5.946-5.947-.642 0-1.26.095-1.88.31A5.962 5.962 0 0010.205 0a5.947 5.947 0 00-5.827 4.757C1.713 5.447 0 7.945 0 10.49c0 1.666.713 3.283 1.998 4.448-.119.5-.19 1-.19 1.499 0 3.401 2.759 5.946 5.946 5.946.642 0 1.26-.095 1.88-.309a5.96 5.96 0 004.162 1.713z"></path></svg></a>
                <a aria-label="Claude" href="https://claude.ai/new?q='. $llm_prompt .'" target="_blank" rel="noopener noreferrer" title="Ask Claude about MUXI"><svg class="size-6 p-0.5 opacity-80 hover:opacity-100" fill="currentColor" fill-rule="evenodd" height="1em" style="flex:none;line-height:1" viewBox="0 0 24 24" width="1em" xmlns="http://www.w3.org/2000/svg"><title>Claude</title><path d="M4.709 15.955l4.72-2.647.08-.23-.08-.128H9.2l-.79-.048-2.698-.073-2.339-.097-2.266-.122-.571-.121L0 11.784l.055-.352.48-.321.686.06 1.52.103 2.278.158 1.652.097 2.449.255h.389l.055-.157-.134-.098-.103-.097-2.358-1.596-2.552-1.688-1.336-.972-.724-.491-.364-.462-.158-1.008.656-.722.881.06.225.061.893.686 1.908 1.476 2.491 1.833.365.304.145-.103.019-.073-.164-.274-1.355-2.446-1.446-2.49-.644-1.032-.17-.619a2.97 2.97 0 01-.104-.729L6.283.134 6.696 0l.996.134.42.364.62 1.414 1.002 2.229 1.555 3.03.456.898.243.832.091.255h.158V9.01l.128-1.706.237-2.095.23-2.695.08-.76.376-.91.747-.492.584.28.48.685-.067.444-.286 1.851-.559 2.903-.364 1.942h.212l.243-.242.985-1.306 1.652-2.064.73-.82.85-.904.547-.431h1.033l.76 1.129-.34 1.166-1.064 1.347-.881 1.142-1.264 1.7-.79 1.36.073.11.188-.02 2.856-.606 1.543-.28 1.841-.315.833.388.091.395-.328.807-1.969.486-2.309.462-3.439.813-.042.03.049.061 1.549.146.662.036h1.622l3.02.225.79.522.474.638-.079.485-1.215.62-1.64-.389-3.829-.91-1.312-.329h-.182v.11l1.093 1.068 2.006 1.81 2.509 2.33.127.578-.322.455-.34-.049-2.205-1.657-.851-.747-1.926-1.62h-.128v.17l.444.649 2.345 3.521.122 1.08-.17.353-.608.213-.668-.122-1.374-1.925-1.415-2.167-1.143-1.943-.14.08-.674 7.254-.316.37-.729.28-.607-.461-.322-.747.322-1.476.389-1.924.315-1.53.286-1.9.17-.632-.012-.042-.14.018-1.434 1.967-2.18 2.945-1.726 1.845-.414.164-.717-.37.067-.662.401-.589 2.388-3.036 1.44-1.882.93-1.086-.006-.158h-.055L4.132 18.56l-1.13.146-.487-.456.061-.746.231-.243 1.908-1.312-.006.006z"></path></svg></a>
                <a aria-label="Gemini" href="https://www.google.com/search?udm=50&amp;source=searchlabs&amp;q='. $llm_prompt .'" target="_blank" rel="noopener noreferrer" title="Ask Gemini about MUXI"><svg class="size-6 p-0.5 opacity-80 hover:opacity-100" fill="currentColor" fill-rule="evenodd" height="1em" style="flex:none;line-height:1" viewBox="0 0 24 24" width="1em" xmlns="http://www.w3.org/2000/svg"><title>Gemini</title><path d="M20.616 10.835a14.147 14.147 0 01-4.45-3.001 14.111 14.111 0 01-3.678-6.452.503.503 0 00-.975 0 14.134 14.134 0 01-3.679 6.452 14.155 14.155 0 01-4.45 3.001c-.65.28-1.318.505-2.002.678a.502.502 0 000 .975c.684.172 1.35.397 2.002.677a14.147 14.147 0 014.45 3.001 14.112 14.112 0 013.679 6.453.502.502 0 00.975 0c.172-.685.397-1.351.677-2.003a14.145 14.145 0 013.001-4.45 14.113 14.113 0 016.453-3.678.503.503 0 000-.975 13.245 13.245 0 01-2.003-.678z"></path></svg></a>
                <a aria-label="Grok" href="https://grok.com/?q='. $llm_prompt .'" target="_blank" rel="noopener noreferrer" title="Ask Grok about MUXI"><svg class="size-6 p-0.5 opacity-80 hover:opacity-100" fill="currentColor" fill-rule="evenodd" height="1em" style="flex:none;line-height:1" viewBox="0 0 24 24" width="1em" xmlns="http://www.w3.org/2000/svg"><title>Grok</title><path d="M9.27 15.29l7.978-5.897c.391-.29.95-.177 1.137.272.98 2.369.542 5.215-1.41 7.169-1.951 1.954-4.667 2.382-7.149 1.406l-2.711 1.257c3.889 2.661 8.611 2.003 11.562-.953 2.341-2.344 3.066-5.539 2.388-8.42l.006.007c-.983-4.232.242-5.924 2.75-9.383.06-.082.12-.164.179-.248l-3.301 3.305v-.01L9.267 15.292M7.623 16.723c-2.792-2.67-2.31-6.801.071-9.184 1.761-1.763 4.647-2.483 7.166-1.425l2.705-1.25a7.808 7.808 0 00-1.829-1A8.975 8.975 0 005.984 5.83c-2.533 2.536-3.33 6.436-1.962 9.764 1.022 2.487-.653 4.246-2.34 6.022-.599.63-1.199 1.259-1.682 1.925l7.62-6.815"></path></svg></a>
                <a aria-label="Perplexity" href="https://www.perplexity.ai/search?q='. $llm_prompt .'" target="_blank" rel="noopener noreferrer" title="Ask Perplexity about MUXI"><svg class="size-6 p-0.5 opacity-80 hover:opacity-100" fill="currentColor" fill-rule="evenodd" height="1em" style="flex:none;line-height:1" viewBox="0 0 24 24" width="1em" xmlns="http://www.w3.org/2000/svg"><title>Perplexity</title><path d="M19.785 0v7.272H22.5V17.62h-2.935V24l-7.037-6.194v6.145h-1.091v-6.152L4.392 24v-6.465H1.5V7.188h2.884V0l7.053 6.494V.19h1.09v6.49L19.786 0zm-7.257 9.044v7.319l5.946 5.234V14.44l-5.946-5.397zm-1.099-.08l-5.946 5.398v7.235l5.946-5.234V8.965zm8.136 7.58h1.844V8.349H13.46l6.105 5.54v2.655zm-8.982-8.28H2.59v8.195h1.8v-2.576l6.192-5.62zM5.475 2.476v4.71h5.115l-5.115-4.71zm13.219 0l-5.115 4.71h5.115v-4.71z"></path></svg></a>
            </div>

            <p>
                <span class="opacity-60">&copy; '. $year .' MUXI </span>
                <span class="hidden md:inline opacity-60">&nbsp; &bull; &nbsp;</span>
                <br class="block md:hidden">

                <a href="https://muxi.org/privacy" class="transition-opacity duration-300 opacity-60 hover:opacity-100">Privacy</a>
                <span class="opacity-60">&nbsp;/&nbsp; </span>
                <a href="https://muxi.org/terms" class="transition-opacity duration-300 opacity-60 hover:opacity-100">Terms</a>
                <span class="opacity-60">&nbsp;/&nbsp; </span>
                <a href="https://muxi.org/support" class="transition-opacity duration-300 opacity-60 hover:opacity-100">Support</a>
            </p>
        </div>
    </footer>';
});
/* <p class="text-xs opacity-50">* Company names shown based on anonymized signup domains.<br>Their appearance does not imply endorsement or partnership.</p> */
