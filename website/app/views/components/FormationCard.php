<?php
tiny::components()->register('FormationCard', function (
    array $formation,
    bool $showStats = true
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
    $downloads = $formation['total_downloads'];

    // Truncate long descriptions so cards maintain a uniform height.
    $shortDesc = strlen($description) > 100 
        ? substr($description, 0, 100) . '...' 
        : $description;

    // Build stats section if requested; used on most pages but can be hidden.
    $statsHtml = '';
    if ($showStats) {
        $statsHtml = "
        <div class='flex items-center gap-4 text-sm text-gray-600'>
            <span class='flex items-center gap-1'>
                <svg class='w-4 h-4' fill='none' stroke='currentColor' viewBox='0 0 24 24'>
                    <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10'></path>
                </svg>
                " . number_format($downloads) . " pulls
            </span>
            <span class='text-gray-500'>v" . htmlspecialchars($version) . "</span>
        </div>
        ";
    }

    // Return the card HTML; this markup will be injected into the calling view.
    return <<<EOF
    <a href="{$homeURL}@{$username}/{$name}" class="block group">
        <div class="bg-white border border-gray-200 rounded-lg p-5 hover:border-blue-400 hover:shadow-md transition-all duration-200">
            <h3 class="text-lg font-semibold text-gray-900 group-hover:text-blue-600 mb-2">
                @{$username}/{$name}
            </h3>
            <p class="text-gray-600 text-sm mb-4 line-clamp-2">
                {$shortDesc}
            </p>
            {$statsHtml}
        </div>
    </a>
    EOF;
});
