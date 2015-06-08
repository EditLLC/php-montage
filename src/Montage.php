<?php namespace Montage;

use Montage\Exceptions\MontageUnknownEndpointException;
use Montage\Exceptions\MontageAuthException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Client;

/**
 * Class Montage
 * @package Montage
 */
class Montage
{
    /**
     * @var string
     */
    var $domain = 'dev.montagehot.club';

    /**
     * @var string
     */
    var $token = '';

    /**
     * @param $subdomain
     * @param null $token
     * @param int $version
     */
    public function __construct($subdomain, $token = null, $version = 1)
    {
        $this->subdomain = $subdomain;
        $this->version = $version;
        $this->debug = false;
        $this->token = $token;

        return $this;
    }

    /**
     * Access a Schema directly as a method of the Montage API class.
     *
     * @param $name
     * @param $args
     * @return Schema
     */
    public function __call($name, $args)
    {
        return new Schema($name, $this);
    }

    /**
     * Enable / disable debug info when making api calls.
     *
     * @param bool $debug
     * @return $this
     */
    public function setDebug($debug = false)
    {
        $this->debug = $debug;
        return $this;
    }

    /**
     * Our main request method to Montage.  Uses Guzzle under
     * the hood to make the request, and will return the
     * json_decoded response from Montage.
     *
     * @param $type
     * @param $url
     * @param array $args
     * @return mixed
     */
    public function request($type, $url, $args = [])
    {
        $response = $this->getHTTPClient()
            ->$type($url, $args)
            ->getBody()
            ->getContents();

        return json_decode($response);
    }

    /**
     * Authenticate with Montage and set the local token.
     *
     * @param null $user
     * @param null $password
     * @return $this
     * @throws MontageAuthException
     */
    public function auth($user = null, $password = null)
    {
        if ($this->token) return $this;

        if (is_null($user) || is_null($password))
        {
            throw new MontageAuthException('Must provide a username and password.');
        }

        try {
            $response = $this->request('post', $this->url('auth'), [
                'form_params' => [
                    'username' => $user,
                    'password' => $password
                ]
            ]);

            $this->token = $response->data->token;

            return $this;
        } catch (ClientException $e) {
            throw new MontageAuthException('Could not authenticate with Montage.');
        }
    }

    /**
     * @return mixed
     * @throws MontageAuthException
     */
    public function getUser()
    {
        $this->requireToken();

        try {
            return $this->request('get', $this->url('user'));
        } catch (ClientException $e) {
            throw new MontageAuthException(sprintf('Could not retrieve a user with token %s', $this->token));
        }
    }

    /**
     * On functions that require a token before preceeding, this will
     * check for the token existence and throw an exception in
     * case that it doesn't exist.
     *
     * @throws MontageAuthException
     */
    private function requireToken()
    {
        if (!$this->token)
        {
            throw new MontageAuthException('Must provide $token before getting a user.');
        }
    }

    /**
     * Gets a formatted Montage endpoint, prefixed with api version.
     *
     * @param $endpoint
     * @param null $schema
     * @param null $doc_id
     * @param null $file_id
     * @return string
     * @throws MontageUnknownEndpointException
     */
    public function url($endpoint, $schema = null, $doc_id = null, $file_id = null)
    {
        return sprintf('api/v%d/%s', $this->version, $this->endpoint(
            $endpoint,
            $schema,
            $doc_id,
            $file_id
        ));
    }

    /**
     * Does what it says.  Gets a guzzle client, with an Authorization header
     * set in case of an existing token.
     *
     * @return Client
     */
    private function getHTTPClient()
    {
        $config = [
            'base_uri' => sprintf(
                'http://%s.%s/',
                $this->subdomain,
                $this->domain
            ),
            'headers' => [
                'Accept' => 'application/json',
                'User-Agent' => sprintf('Montage PHP v%d', $this->version),
                'X-Requested-With' => 'XMLHttpRequest'
            ]
        ];

        //Set the token if it exists
        if ($this->token)
        {
           $config['headers']['Authorization'] = sprintf('Token %s', $this->token);
        }

        if ($this->debug)
        {
            $config['debug'] = true;
        }

        return new Client($config);
    }

    /**
     * Creates a formatted Montage API endpoint string.
     *
     * @param $endpoint
     * @param null $schema
     * @param null $doc_id
     * @param null $file_id
     * @return string
     * @throws MontageUnknownEndpointException
     */
    private function endpoint($endpoint, $schema = null, $doc_id = null, $file_id = null)
    {
        $endpoints = [
            'auth' => 'auth/',
            'user' => 'auth/user/',
            'schema-list' => 'schemas/',
            'schema-detail' => 'schemas/%s/',
            'document-query' => 'schemas/%s/query/',
            'document-save' => 'schemas/%s/save/',
            'document-detail' => 'schemas/%s/%s/',
            'file-list' => 'files/',
            'file-detail' => 'files/%s/',
        ];

        if (!array_key_exists($endpoint, $endpoints))
        {
            throw new MontageUnknownEndpointException(
                sprintf('Unknown endpoint "%s" requested.', $endpoint)
            );
        }

        //do the endpoint formatting
        if (!is_null($file_id)) {
            return sprintf($endpoints[$endpoint], $file_id);
        } else if (!is_null($schema) && !is_null($doc_id)) {
            return sprintf($endpoints[$endpoint], $schema, $doc_id);
        } else if (!is_null($schema)) {
            return sprintf($endpoints[$endpoint], $schema);
        }

        return $endpoints[$endpoint];
    }

    /**
     * Get a list of all schemas for the given users token.
     *
     * @return mixed
     */
    public function schemas($schemaName = null)
    {
        if (is_null($schemaName)) {
            $url = $this->url('schema-list');
            return $this->request('get', $url);
        }

        return $this->schema($schemaName);
    }

    /**
     * Set the Schema for this instance of Montage.
     *
     * @param $name
     * @return Schema
     */
    public function schema($name)
    {
        return new Schema($name, $this);
    }
}