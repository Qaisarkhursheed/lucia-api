<?php

namespace App\Repositories\TextractReader;

use App\Repositories\ApiInvoker;
use App\Repositories\TextractReader\Exceptions\ItemNotFoundException;
use App\Repositories\TextractReader\Exceptions\ProcessingErrorException;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class Mediator extends ApiInvoker
{
    /**
     * Get the status of a process
     *
     * @param string $hashKey
     * @return array|\stdClass|null
     * @throws ItemNotFoundException
     * @throws ProcessingErrorException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Exception
     */
    public function status(  string $hashKey )
    {
        if( $this->queryStringRequest( "status/$hashKey" ) ) return $this->getData();
        if( $this->getResponseCode() == Response::HTTP_BAD_REQUEST )
        {
            if( Str::contains( $this->getResponseContent(), "does not exist" ) ) throw new ItemNotFoundException();
            if( Str::contains( $this->getResponseContent(), "Error processing file" ) ) throw new ProcessingErrorException();
        }
        throw new \Exception( $this->getResponseContent());
    }

    /**
     * Inform processor, we are done with the hashKey, clean it up
     *
     * @param string $hashKey
     * @return array|\stdClass|null
     * @throws ItemNotFoundException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function acknowledged(  string $hashKey )
    {
        if( $this->jsonRequest( "acknowledged/$hashKey" , [], 'DELETE' ) ) return $this->getData();
        if( $this->getResponseCode() == Response::HTTP_BAD_REQUEST ) throw new ItemNotFoundException();

        throw new \Exception( $this->getResponseContent());
    }

    /**
     * This normally always returns hashKey
     *
     * @param string $S3ObjectPath
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Exception
     */
    public function recognize(  string $S3ObjectPath ):string
    {
        //{
        //    "status": "success",
        //    "filePath": "PDf 4.pdff",
        //    "key": "Fibu6H4PvVr7ExoSjfU2rx"
        //}
        if( $this->jsonRequest( "recognize" , [ "filePath" => $S3ObjectPath ], 'POST' ) ) return $this->getData()->key;
        throw new \Exception( $this->getResponseContent());
    }

    /**
     * list all pending acknowledged ocrs, as 2 dimensional array of key => value, hasKey=>s3ObjectPath
     *
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Exception
     */
    public function listAll( ):array
    {
        //{
        //    "status": "success",
        //    "data": {
        //        "Fibu6H4PvVr7ExoSjfU2rx": "PDf 4.pdff"
        //    }
        //}
        if( $this->queryStringRequest( "list" ) ) return (array)$this->getData()->data;
        throw new \Exception( $this->getResponseContent());
    }

    /**
     * @inheritDoc
     */
    protected function toUrl(string $link): string
    {
        return Str::of( env( "AWS_TEXTRACT_PROCESSOR_HOST" ) )->rtrim('/')
                ->append(":")->append(env( "AWS_TEXTRACT_PROCESSOR_PORT" ))
            . Str::of( $link )->start("/" );
    }
}
