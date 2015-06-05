<?php namespace Montage;

use Montage\Exceptions\MontageException;

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
        $this->query = new Query($schema, $queryDescriptor);
    }

    /**
     * Lets us call methods on the query class directly on the document
     * class instance.  Like some David Copperfield shit...
     *
     * @param $name
     * @param $arguments
     * @return $this
     * @throws MontageException
     */
    public function __call($name, $arguments)
    {
        if (method_exists($this->query, $name))
        {
            call_user_func_array([$this->query, $name], $arguments);
            return $this;
        }

        throw new MontageException(sprintf('Could not find method "%s" as part of the document query.'));
    }

    /**
     * Required function for any class that implements IteratorAggregate.  Will
     * yield documents as they become available.  If cursors are returned
     * then subsequent requests will be made, yielding more documents.
     *
     * @return \Generator
     * @throws MontageException
     */
    public function getIterator()
    {
        //Run the query
        $resp = $this->query->execute();

        //Return the documents as an ArrayIterator to satisfy the requirements
        //of the getIterator function.
        foreach ($resp->data as $document)
        {
            yield $document;
        }

        while ($resp->cursors->next) {
            //send a new request, resetting $resp
            $resp = $this->query->execute($resp->cursors->next);

            foreach ($resp->data as $document)
            {
                yield $document;
            }
        }
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
