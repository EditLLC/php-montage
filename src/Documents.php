<?php namespace Montage;

use Montage\Exceptions\MontageException;

/**
 * The documents class implements IteratorAggregate, making it simple to
 * use as the basis of a loop.
 *
 * Class Documents
 * @package Montage
 */
class Documents implements \IteratorAggregate {

    /**
     * @var array
     */
    public $documents = [];

    /**
     * @var Schema
     */
    private $schema;

    /**
     * @param Schema $schema
     */
    public function __construct(Schema $schema)
    {
        $this->schema = $schema;
        $this->montage = $schema->montage;
        $this->query = new Query($schema);
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
     * Persist one or more document objects to montage.
     *
     * @param $doc
     * @return mixed
     */
    public function save($doc)
    {
        return $this->montage->request(
            'post',
            $this->montage->url('document-save', $this->schema->name),
            ['body' => json_encode($doc)]
        );
    }

    /**
     * Get a single document by it's ID from montage.
     *
     * @param $docId
     * @return mixed
     */
    public function get($docId)
    {
        return $this->montage->request(
            'get',
            $this->montage->url('document-detail', $this->schema->name, $docId)
        );
    }

    /**
     * Update a document with a given $docId with new details.
     *
     * @param $docId
     * @param $doc
     * @return mixed
     */
    public function update($docId, $doc)
    {
        return $this->montage->request(
            'post',
            $this->montage->url('document-detail', $this->schema->name, $docId),
            ['body' => json_encode($doc)]
        );
    }

    /**
     * Delete a record with montage.
     *
     * @param $docId
     * @return mixed
     */
    public function delete($docId)
    {
        return $this->montage->request(
            'delete',
            $this->montage->url('document-detail', $this->schema->name, $docId)
        );
    }

}
