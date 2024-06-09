<?php

namespace App\Repositories\TextractReader;

use App\ModelsExtended\Interfaces\IBookingModelInterface;

interface IDocumentReader
{
    /**
     * A unique identifier for the reader
     *
     * @return string
     */
    public function name():string;

    /**
     * @param array $jsonArray
     * @return array|IBookingModelInterface[]
     */
    public function read( array $jsonArray): array;

    /**
     * @param array $jsonArray
     * @return bool
     */
    public function canRead( array $jsonArray): bool;
}
