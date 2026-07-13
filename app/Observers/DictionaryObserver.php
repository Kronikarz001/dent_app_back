<?php

namespace App\Observers;

use App\Models\Dictionary;

/**
 * Summary of DictionaryObserver
 */
class DictionaryObserver
{
    /**
     * @param Dictionary $dictionary
     * @return void
     */
    public function creating(Dictionary $dictionary): void
    {
        $dictionary->type = $dictionary::class;
    }

    /**
     * @param Dictionary $dictionary
     * @return void
     */
    public function retrieved(Dictionary $dictionary): void
    {
        if (is_string($dictionary->additional)) {
            $dictionary->additional = json_decode($dictionary->additional);
        }
    }

    /**
     * @param Dictionary $dictionary
     * @return void
     */
    public function saving(Dictionary $dictionary): void
    {
        if ($dictionary->additional === null || is_string($dictionary->additional)) {
            return;
        }

        $dictionary->additional = json_encode($dictionary->additional);
    }
}
