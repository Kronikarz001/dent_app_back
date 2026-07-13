<?php

namespace App\Services;

use App\Models\Dictionary;
use App\Repositories\DictionaryRepositoryInterface;

/**
 * Summary of DictionaryServiceInterface
 */
interface DictionaryServiceInterface
{
    /**
     * @param DictionaryRepositoryInterface $repository
     * @param array $data
     * @param string $type
     * @return Dictionary
     */
    public function createDictionary(DictionaryRepositoryInterface $repository, array $data, string $type): Dictionary;

    /**
     * @param DictionaryRepositoryInterface $repository
     * @param Dictionary $dictionary
     * @param array $data
     * @return Dictionary
     */
    public function updateDictionary(DictionaryRepositoryInterface $repository, Dictionary $dictionary, array $data): Dictionary;
}
