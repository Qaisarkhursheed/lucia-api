<?php


namespace App\Repositories;

use App\Exceptions\APIInvocationException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Str;
use stdClass;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * This class helps to communicate with the api
 * It uses Authorization Bearer Token for Authentication
 * Class ApiInvoker
 */
class ApiInvoker
{
    /**
     * @var ?string
     */
    protected $access_token;
    /**
     * @var ?int
     */
    protected $expires_in;
    /**
     * @var int
     */
    private int $responseCode;

    /**
     * @var string
     */
    private string $responseStatus;

    /**
     * @var string|null
     */
    private ?string $responseContent;

    /**
     * @var stdClass|null|array
     */
    private $responseData;
    private string $queriedURL;


    /**
     * @throws GuzzleException
     */
    public function jsonRequest(string $resource, array $data, string $method = 'GET'): bool
    {
        return $this->executeRequest( $resource, [  'json' => $data ], $method );
    }

    /**
     * https://docs.guzzlephp.org/en/stable/request-options.html#multipart
     *
     * @param string $resource
     * @param array $data
     * @return bool
     * @throws GuzzleException
     */
    public function multiPartRequest( string $resource, array $data ): bool
    {
        return $this->executeRequest( $resource, [  'multipart' => $data ], 'POST' );
    }

    /**
     * https://docs.guzzlephp.org/en/stable/request-options.html#multipart
     *
     * @param string $resource
     * @param array $data
     * @return bool
     * @throws GuzzleException
     */
    public function multiPartAllRequest( string $resource ): bool
    {
        return $this->executeRequest( $resource, [  'multipart' => array_map( function ( string $key ){
            if( request()->hasFile( $key ) ) return [
                'name'     => $key,
                'contents' => request(  )->file( $key )->getContent(),
                'filename' => request(  )->file( $key )->getClientOriginalName(),
            ];

            return [
                'name'     => $key,
                'contents' => request( $key )
            ];
        }, request()->keys() ) ], 'POST' );
    }

    /**
     * @throws GuzzleException
     */
    public function formParamsRequest(string $resource, array $data = [], string $method = 'GET'): bool
    {
        return $this->executeRequest( $resource, [  'form_params' => $data ], $method );
    }

    /**
     * @throws GuzzleException
     */
    public function queryStringRequest(string $resource, array $data = [] ): bool
    {
        return $this->executeRequest( $resource, [  'query' => $data ] );
    }

    /**
     * @param string $link
     * @return string
     */
    protected function toUrl( string $link ): string
    {
        return Str::of( env( "API_URL" )  )->rtrim('/')
            . Str::of( $link )->start("/" );
    }

    /**
     * @param string $resource
     * @param array $options
     * @param string $method
     * @param array $headers
     * @return bool
     * @throws GuzzleException
     */
    public function executeRequest(string $resource, array $options, string $method = 'GET', array $headers = []): bool
    {
        $this->responseContent = null;

        if( $this->access_token )
        {
            $headers = array_merge([
                'Authorization' => 'Bearer ' . $this->access_token,
            ], $headers);
        }

        try {

            $client = new Client();
            $this->queriedURL = $this->toUrl( $resource );

            $response = $client->request( $method, $this->queriedURL,
                array_merge(
                    [
                        'headers' => $headers
                    ],
                    $options
                )
            );

            $this->responseCode = $response->getStatusCode();
            $this->responseStatus = $response->getReasonPhrase();
            $this->responseContent = $response->getBody()->getContents();

            return true;

        }catch (ClientException $exception)
        {
            $this->responseCode =  $exception->getResponse()->getStatusCode();
            $this->responseStatus =  $exception->getResponse()->getReasonPhrase();
            $this->responseContent =  $exception->getResponse()->getBody()->getContents();

            return false;
        } finally {
            if( $this->responseContent ) $this->responseData =  json_decode( $this->responseContent );
        }
    }

    /**
     * @return array|stdClass|null
     */
    public function getData()
    {
        return $this->responseData;
    }

    /**
     * @return bool
     */
    public function getIsSuccessful(): bool
    {
        return $this->responseCode === ResponseAlias::HTTP_OK;
    }

    /**
     * @return int
     */
    public function getResponseCode(): int
    {
        return $this->responseCode ;
    }

    /**
     * @return string
     */
    public function getResponseStatus(): string
    {
        return $this->responseStatus ;
    }

    /**
     * @return string
     */
    public function getQueriedURL(): string
    {
        return $this->queriedURL ;
    }

    /**
     * @return string|null
     */
    public function getResponseContent(): ?string
    {
        return $this->responseContent;
    }

    /**
     * @throws APIInvocationException
     */
    protected function throwException()
    {
        throw new APIInvocationException( $this );
    }
}
