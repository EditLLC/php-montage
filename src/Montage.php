<?php namespace Montage;

use Montage\Exceptions\MontageUnknownEndpointException;
use Montage\Exceptions\MontageAuthException;
use GuzzleHttp\Exception\ClientException;
use Montage\Exceptions\MontageException;
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
     * @param $subdomain
     * @param null $token
     * @param int $version
     */
    public function __construct($subdomain, $token = null, $version = 1)
    {
        $this->subdomain = $subdomain;
        $this->version = $version;
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
        $response = $this->getGuzzleClient()
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
        return sprintf('api/v%d/%s', $this->version, $this->endpoint($endpoint,
            $schema, $doc_id, $file_id));
    }

    /**
     * Does what it says.  Gets a guzzle client, with an Authorization header
     * set in case of an existing token.
     *
     * @return Client
     */
    private function getGuzzleClient()
    {
        $config = [
            'base_uri' => sprintf(
                'http://%s.%s/',
                $this->subdomain,
                $this->domain
            )
        ];

        if ($this->token)
        {
           $config['headers'] = [
               'Authorization' => sprintf('Token %s', $this->token)
           ];
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
            'schema-list' => 'schemas/',
            'schema-detail' => 'schemas/%s/',
            'document-query' => 'schemas/%s/query/',
            'document-save' => 'schemas/%s/save/',
            'document-detail' => 'schemas/%s/%s/',
            'file-list' => 'files/',
            'file-detail' => 'files/%s',
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

/**
 * Class Schema
 * @package Montage
 */
class Schema
{
    /**
     * @var Montage
     */
    public $montage;

    /**
     * @var
     */
    public $name;

    /**
     * @param $name
     * @param Montage $montage
     */
    public function __construct($name, Montage $montage)
    {
        $this->montage = $montage;
        $this->name = $name;
    }

    /**
     * Allows for easy access to class methods without having to call them
     * as a function.
     *
     * @param $name
     * @return mixed
     * @throws MontageException
     */
    public function __get($name)
    {
        if (!method_exists($this, $name))
        {
            throw new MontageException(sprintf('Unknown method or property "%s" called.', $name));
        }

        return $this->$name();
    }

    /**
     * Returns the details of a specific Montage schema.
     *
     * @return mixed
     */
    public function detail()
    {
        $url = $this->montage->url('schema-detail', $this->name);
        return $this->montage->request('get', $url);
    }

    /**
     * Can be used as $schema->documents or $schema->documents($queryDescriptor) for
     * more fine grained control.
     *
     * @return Documents
     */
    public function documents(array $queryDescriptor = [])
    {
        return new Documents($queryDescriptor, $this);
    }
}

/**
 * Class Documents
 * @package Montage
 */
class Documents implements \IteratorAggregate {

    /**
     * @var array
     */
    public $documents = [];

    /**
     * @var array
     */
    private $queryDescriptor;

    /**
     * @var Schema
     */
    private $schema;

    /**
     * @param Schema $schema
     */
    public function __construct($queryDescriptor = [], Schema $schema)
    {
        $this->queryDescriptor = $queryDescriptor;
        $this->schema = $schema;
    }

    /**
     * The Documents class implements IteratorAggregate, so this function is required :)
     * This is used when attempting to iterate over a list of documents.
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        //Run the query
        $query = new Query($this->schema, $this->queryDescriptor);

        //Set the documents
        $this->documents = $query->execute();

        //Return the documents as an ArrayIterator to satisfy the requirements
        //of the getIterator function.
        return new \ArrayIterator($this->documents);
    }

    /**
     *
     */
    public function save(){}

    /**
     *
     */
    public function get(){}

    /**
     *
     */
    public function delete(){}
}

/**
 * Class Query
 * @package Montage
 */
class Query {

    /**
     * @var Schema
     */
    private $schema;

    /**
     * @param Schema $schema
     * @param array $queryDescriptor
     */
    public function __construct(Schema $schema, array $queryDescriptor = [])
    {
        $this->descriptor = $this->getDiscriptor($queryDescriptor);
        $this->montage = $schema->montage;
        $this->schema = $schema;
    }

    /**
     * Execute the given query and return the response data.
     *
     * @return mixed
     */
    public function execute()
    {
        $name = $this->schema->name;
        $query = ['query' => ['query' => json_encode($this->descriptor)]];
        $response = $this->montage->request('get', $this->montage->url('document-query', $name), $query);
        return $response->data;
    }


    /**
     * Merge in a provided $config and return the result for passing
     * over to the Montage API via a query string.
     *
     * @param $config
     * @return array
     */
    public function getDiscriptor($config)
    {
        $defaults = [
            'filter' => [],
            'limit' => null,
            'offset' => null,
            'order_by' => null,
            'ordering' => 'asc',
        ];

        return array_merge($defaults, $config);
    }

    /**
     * @param $config
     * @return Query
     */
    public function update($config)
    {
        $descriptor = clone $this->descriptor;
        $descriptor = array_merge($descriptor, $config);
        return new Query($this->schema, $descriptor);
    }

    /**
     * @param array $config
     * @return Query
     */
    public function filter(array $config)
    {
        $filter = $this->descriptor->filter;
        $filter = array_merge($filter, $config);
        return $this->update(['filter' => $filter]);
    }

    /**
     * @param $limit
     * @return Query
     */
    public function limit($limit)
    {
        return $this->update(['limit' => $limit]);
    }

    /**
     * @param $offset
     * @return Query
     */
    public function offset($offset)
    {
        return $this->update(['offset' => $offset]);
    }

    /**
     * @param $orderby
     * @param string $ordering
     * @return Query
     * @throws MontageGeneralException
     */
    public function orderBy($orderby, $ordering = 'asc')
    {
        if (!in_array($ordering, ['asc', 'desc']))
        {
            throw new MontageException('$ordering must be asc or desc.');
        }

        return $this->update(['order_by' => $orderby, 'ordering' => $ordering]);
    }
}