<?php namespace Montage;

use Montage\Exceptions\MontageException;

/**
 * The Schema class allows access to a Montage schema as well as a
 * way to access all the documents for a specific schema.
 *
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
        $this->documents = new Documents($this);
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
}
