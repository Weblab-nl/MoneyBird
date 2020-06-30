<?php

namespace Weblab\MoneyBird\Repositories;
use Weblab\MoneyBird\Exceptions\NonExistingRepositoryException;
use Weblab\MoneyBird\MoneyBird;

/**
 * Class Factory
 * @author Weblab.nl - Eelco Verbeek
 */
class Factory {

    /**
     * @var MoneyBird   Instance of the MoneyBird RESTClient
     */
    protected $api;

    /**
     * @var array       Names of the available repositories
     */
    protected $availableRepositories = [
        'invoices', 'contacts', 'payments'
    ];

    /**
     * @var array       Local storage of instantiated repositories
     */
    protected $repositories = [];

    /**
     * Factory constructor.
     * @param MoneyBird $api
     */
    public function __construct(MoneyBird $api) {
        $this->api = $api;
    }

    /**
     * Returns instance of requested repository
     *
     * @param   string  $name
     * @return  mixed
     * @throws  NonExistingRepositoryException
     */
    public function get($name) {
        // Check if repository is available
        if (!in_array($name, $this->availableRepositories)) {
            // Repository not available, throw exception
            throw new NonExistingRepositoryException($name);
        }

        // Check if repository already instantiated
        if (!isset($this->repositories[$name])) {
            // Make new instance of requested repository
            $class = '\\Weblab\\MoneyBird\\Repositories\\' . ucfirst($name);
            $this->repositories[$name] = new $class($this->api);
        }

        // Return repository
        return $this->repositories[$name];
    }

}
