<?php
tiny::components()->register('FormationCard', function (...$props) {
    $formation = $props['formation'] ?? null;
    if (!$formation) return '';

    $showStats = $props['showStats'] ?? true;
    $homeURL = tiny::getHomeURL('/');

    // Handle both object and array formats
    $username = is_object($formation) ? $formation->registry_username : $formation['registry_username'];
    $name = is_object($formation) ? $formation->name : $formation['name'];
    $description = is_object($formation) ? $formation->description : $formation['description'];
    $version = is_object($formation) ? $formation->latest_version : $formation['latest_version'];
    $downloads = is_object($formation) ? $formation->total_downloads : $formation['total_downloads'];
    $stars = is_object($formation) ? $formation->github_stars : $formation['github_stars'];

    // Truncate description
    $shortDesc = strlen($description) > 100 ? substr($description, 0, 100) . '...' : $description;

    $statsHtml = '';
    if ($showStats) {
        $statsHtml = "
        <div class='flex items-center gap-4 text-sm text-gray-600'>
            <span class='flex items-center gap-1'>
                <svg class='w-4 h-4' fill='none' stroke='currentColor' viewBox='0 0 24 24'>
                    <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10'></path>
                </svg>
                " . number_format($downloads) . "
            </span>
            <span class='flex items-center gap-1'>
                <svg class='w-4 h-4' fill='none' stroke='currentColor' viewBox='0 0 24 24'>
                    <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z'></path>
                </svg>
                " . number_format($stars) . "
            </span>
            <span class='text-gray-500'>v" . htmlspecialchars($version) . "</span>
        </div>
        ";
    }

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
