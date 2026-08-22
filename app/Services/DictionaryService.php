<?php

namespace App\Services;

use App\Models\Dictionary;
use App\Repositories\DictionaryRepositoryInterface;

/**
 * Summary of DictionaryService
 */
class DictionaryService implements DictionaryServiceInterface
{
    /**
     * @param DictionaryRepositoryInterface $repository
     * @param array $data
     * @param string $type
     * @return Dictionary
     */
    public function createDictionary(DictionaryRepositoryInterface $repository, array $data, string $type): Dictionary
    {
        return $repository->create($data + [
            'type' => $type,
        ]);
    }

    /**
     * @param DictionaryRepositoryInterface $repository
     * @param Dictionary $dictionary
     * @param array $data
     * @return Dictionary
     */
    public function updateDictionary(DictionaryRepositoryInterface $repository, Dictionary $dictionary, array $data): Dictionary
    {
        return $repository->update($dictionary, $data);
    }
}
