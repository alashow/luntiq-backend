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