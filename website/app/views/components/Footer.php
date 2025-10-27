<?php
tiny::components()->register('Footer', function (...$props) {
    $props['year'] = $props['year'] ?? date('Y');
    $props['rootPath'] = tiny::getHomeURL('/');
    return <<<EOF
    <section class="w-full bg-white">
        <div class="px-8 py-12 mx-auto max-w-7xl">
            <div class="flex flex-col items-start justify-between pt-10 mt-10 border-t border-gray-100 md:flex-row md:items-center">
                <p class="mb-6 text-sm text-left text-gray-600 md:mb-0">&copy; VarOps LLC. All Rights Reserved.</p>
                <div class="flex items-start justify-start space-x-6 md:items-center md:justify-center">
                    <a href="#_" class="text-sm text-gray-600 transition">Terms</a>
                    <a href="#_" class="text-sm text-gray-600 transition">Privacy</a>
                </div>
            </div>
        </div>
    </section>
    EOF;
});
