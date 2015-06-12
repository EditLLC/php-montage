<?php namespace Montage;

use Montage\Exceptions\MontageException;

/**
 * Sets up the actual queries used against the Montage API.
 *
 * Class Query
 * @package Montage
 */
class Query {

    /**
     * @var Schema
     */
    private $schema;

    /**
     * The results of the query.
     *
     * @var array
     */
    public $data = [];

    /**
     * Any cursors sent back as the result of a request.
     *
     * @var
     */
    public $cursors;

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
     * @param null $cursor
     * @return mixed
     */
    public function execute($cursor = null)
    {
        $name = $this->schema->name;

        if (is_null($cursor)) {
            $params = ['query' => json_encode($this->descriptor)];
        } else {
            $params = ['cursor' => $cursor];
        }

        //This is our main query function
        return $this->montage->request(
            'get',
            $this->montage->url('document-query', $name),
            ['query' => $params]
        );
    }


    /**
     * @param array $config
     * @return array
     */
    public function getDiscriptor(array $config)
    {
        $defaults = [
            'filter' => [],
            'limit' => null,
            'offset' => null,
            'order_by' => null,
            'ordering' => 'asc',
            'batch_size' => 1000
        ];

        return array_merge($defaults, $config);
    }

    /**
     * @param $config
     * @return Query
     */
    public function update(array $config)
    {
        $newDescriptor = array_merge($this->descriptor, $config);
        $this->descriptor = $this->getDiscriptor($newDescriptor);
    }

    /**
     * @param array $config
     * @return Query
     */
    public function filter(array $config)
    {
        $this->update(['filter' => $config]);
    }

    /**
     * @param $limit
     * @return Query
     */
    public function limit($limit)
    {
        $this->update(['limit' => $limit]);
    }

    /**
     * @param $offset
     * @return Query
     */
    public function offset($offset)
    {
        $this->update(['offset' => $offset]);
    }

    /**
     * @param $orderby
     * @param string $ordering
     * @return Query
     * @throws MontageException
     */
    public function orderBy($orderby, $ordering = 'asc')
    {
        if (!in_array($ordering, ['asc', 'desc']))
        {
            throw new MontageException('$ordering must be one of "asc" or "desc".');
        }

        $this->update([
            'order_by' => $orderby,
            'ordering' => $ordering
        ]);
    }
}
