<?php

use Illuminate\Support\Collection;

if (! function_exists('collect_merge')) {
    /**
     * Create a collection from the given collections.
     *
     * @param array $collections collections to merge
     *
     * @return Collection
     */
    function collect_merge(...$collections)
    {
        $merged = collect();
        foreach ($collections as $collection) {
            foreach ($collection as $item) {
                $merged->push($item);
            }
        }
        return $merged;
    }
}

if (! function_exists('sanitizeFileName')) {
    function sanitizeFileName($dangerous_filename, $platform = 'Unix')
    {
        if (in_array(strtolower($platform), ['unix', 'linux'])) {
            // our list of "dangerous characters", add/remove characters if necessary
            $dangerous_characters = ['"', "'", "&", "/", "\\", "?", "#"];
        } else {
            // no OS matched? return the original filename then...
            return $dangerous_filename;
        }

        // every forbidden character is replace by an underscore
        return str_replace($dangerous_characters, '-', $dangerous_filename);
    }
}