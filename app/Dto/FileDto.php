<?php

namespace App\Dto;

use App\Enums\FileableType;

/**
 * Summary of FileDto
 */
class FileDto extends Dto
{
    /**
     * @param array|null $file
     * @param FileableType $type
     */
    public function __construct(
        private readonly array|null $file,
        private readonly FileableType $type
    ){}

    /**
     * @return FileableType
     */
    public function getType(): FileableType
    {
        return $this->type;
    }

    /**
     * @return array|null
     */
    public function getFile(): array|null
    {
        return $this->file;
    }
}
