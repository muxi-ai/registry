<?php
tiny::components()->register('FormationCard', function (
    array $formation,
    bool $withUsername = true
) {
    // Validate required formation data exists.
    if (empty($formation)) {
        return '';
    }
    $homeURL = tiny::getHomeURL('/');

    // Extract formation properties; formations are stored as associative arrays.
    $username = $formation['registry_username'];
    $name = $formation['name'];
    $description = $formation['description'] ?? '';
    $version = $formation['latest_version'];
    $downloads = number_format($formation['total_downloads'] ?? 0);
    $download_plural = $formation['total_downloads'] > 9 ? 's' : 's';
    $github_stars = number_format($formation['github_stars'] ?? 0);
    $stars_plural = $formation['github_stars'] > 9 ? 's' : 's';
    $published = date('F j, Y', strtotime($formation['published_at']));
    $license = htmlspecialchars($formation['license'] ?? '');

    $display = $withUsername ? "@{$username}/{$name}" : $name;

    // Return the card HTML; this markup will be injected into the calling view.
    return <<<EOF
    <div class="@container">
        <div>
            <a href="{$homeURL}@{$username}/{$name}" class="text-lg link-on-hover font-medium hover:font-semibold!">{$display} ›</a>
            <div class="text-sm opacity-60 dark:opacity-50">{$description}</div>
            <div class="flex items-center opacity-60 dark:opacity-50 mt-2">
                <span class="border rounded-full text-[10px]! subpixel-antialiased bg-white/50 dark:bg-white/5 leading-none pt-[3px] pb-1 px-1.5 mr-4">v {$version}</span>
                <span class="text-xs">{$published}</span>
            </div>
        </div>

        <div class="text-right -mt-1.5">
            <ul class="divide-x text-sm flex items-center divider-x gap-x-2 p-0! my-0! -mx-px! [&>li]:m-0 [&>li]:pr-2 [&>li]:flex [&>li]:items-center [&>li]:space-x-2 [&>li>svg]:size-3.5 [&>li>span>strong]:pr-0.5 [&>li>span>strong]:font-semibold">
                <li class="whitespace-nowrap">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="px-px">
                        <path d="M3.2 18C1.88484 17.235 1 15.8051 1 14.1674C1 12.1053 2.40285 10.3727 4.30122 9.88197C4.30041 9.83571 4.3 9.78935 4.3 9.7429C4.3 5.46661 7.74741 2 12 2C15.6211 2 18.6584 4.51348 19.4806 7.90009C21.5395 8.69955 23 10.7089 23 13.0613C23 14.8707 22.1359 16.4772 20.8 17.4862M12 13V22M12 22L15.5 18.5M12 22L8.5 18.5" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round"></path>
                    </svg>
                    <span><strong>{$downloads}</strong><span class="hidden @xl:inline"> Pull{$download_plural}</span></span>
                </li>
                <li class="whitespace-nowrap">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                        <path fill="currentColor" d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"></path>
                    </svg>
                    <span><strong>{$github_stars}</strong><span class="hidden @xl:inline"> Star{$stars_plural}</span></span>
                </li>
            </ul>
            <div class="text-xs mr-2 opacity-60 dark:opacity-50 -mt-1!">{$license}</div>
        </div>
    </div>
    EOF;
});
