<?php

return [
    'default_per_page' => (int) env('SEARCH_DEFAULT_PER_PAGE', 50),
    'sort_keyword' => 'sort',
    'search_keywords' => [
        'sort',
        'from',
        'to',
    ],
    'default_sort' => 'desc',
    'sort_asc_default_character' => 'asc',
    'sort_desc_default_character' => 'desc',
    'search_string_keyword' => 'searchString',
];
