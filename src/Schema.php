<?php namespace Montage;

use Montage\Exceptions\MontageException;

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
     * as a function.  This makes it possible to call functions like
     * $schema->documents (which is an iterable) as the basis of
     * a foreach loop, etc...
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
     * Can be used as $schema->documents or $schema->documents($queryDescriptor)
     * for more fine grained control.
     *
     * @return Documents
     */
    public function documents(array $queryDescriptor = [])
    {
        return new Documents($queryDescriptor, $this);
    }
}
