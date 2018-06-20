<?php

namespace Weblab\MoneyBird\Repositories;
use Weblab\MoneyBird\Models\AbstractModel;
use Weblab\MoneyBird\MoneyBird;

/**
 * Class AbstractRepository
 * @author Weblab.nl - Eelco Verbeek
 */
abstract class AbstractRepository {

    /**
     * @var MoneyBird   RESTClient instance
     */
    protected $api;

    /**
     * AbstractRepository constructor.
     * @param MoneyBird $api
     */
    public function __construct(MoneyBird $api) {
        $this->api = $api;
    }

    /**
     * Create a new instance of a model
     *
     * @param   array           $data
     * @return  AbstractModel
     */
    public function create($data) {
        // Get the full class name (including namespace)
        $class = $this->getModelClass();

        // Create instance of model
        $instance = new $class($this->api);
        // Fill the instance with data
        $instance->fill($data);

        // Return instance
        return $instance;
    }

    /**
     * Search for specific models by id
     *
     * @param   int                 $id
     * @return  null|AbstractModel
     */
    public function find($id) {
        // Get the full class name (including namespace)
        $class = $this->getModelClass();

        // Create instance of model
        $instance = new $class($this->api);

        // Load model
        if ($instance->find($id)) {
            // Model found, return it
            return $instance;
        }
        else {
            // Model not found return null
            return null;
        }
    }

    /**
     * Get the full class name of the model, including namespace
     *
     * @return  string
     */
    protected function getModelClass() {
        return '\\Weblab\\MoneyBird\\Models\\' . ucfirst(static::MODEL);
    }
    
}
