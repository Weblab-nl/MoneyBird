<?php

namespace Weblab\MoneyBird\Models;

use Weblab\MoneyBird\Exceptions\EntityCreationException;
use Weblab\MoneyBird\Exceptions\EntityDeleteException;
use Weblab\MoneyBird\Exceptions\EntityUpdateException;
use Weblab\MoneyBird\Exceptions\MissingExternalIdentifierException;
use Weblab\MoneyBird\MoneyBird;

/**
 * Class AbstractModel
 * @author Weblab.nl - Eelco Verbeek
 */
abstract class AbstractModel {

    use ModelTrait {
        __construct as traitConstruct;
    }

    /**
     * @var MoneyBird   A MoneyBird API instance
     */
    protected $api;

    /**
     * AbstractModel constructor.
     * @param   \stdClass|null  $entity
     * @param   bool            $fillFromAPI
     *
     * @throws \Exception
     */
    public function __construct(MoneyBird $api, \stdClass $entity = null, $fillFromAPI = false) {
        // check if the endpoint constant is set
        if (!defined('ENDPOINT')) {
            throw new \Exception('Endpoint is missing');
        }
        
        $this->api = $api;
        $this->traitConstruct($entity, $fillFromAPI);
    }

    /**
     * @param   integer         $id
     * @return  null|static
     * @throws  \Exception
     */
    public function find($id) {
        // Build path
        $path = '/' . static::ENDPOINT . '/' . $id;

        // Do API call
        $result = $this->api->get($path);

        // If status not 200 return null
        if ($result->getStatus() !== 200) {
            return null;
        }

        // Fill the model with the api result
        $this->fillFromAPI($result->getResult());

        // Return self for chaining
        return $this;
    }

    /**
     * Save the current version of the entity
     *
     * @return  bool
     * @throws  EntityCreationException
     * @throws  EntityUpdateException
     * @throws  MissingExternalIdentifierException
     */
    public function save() {
        // Detect the save type
        $saveType = $this->saveType();

        // Do API call
        $result = $this->api->{$saveType}($this->getSavePath(), $this->toJSON($saveType === 'patch'));

        // Check if there was an acceptable HTTP status. If not throw exception
        if ($saveType === 'patch' && $result->getStatus() !== 200) {
            throw new EntityUpdateException('Something went wrong saving the entity to MoneyBird');
        } else if ($saveType === 'post' && $result->getStatus() !== 201) {
            throw new EntityCreationException('Something went wrong saving the entity to MoneyBird');
        }

        // Fill instance with latest information from the API
        $this->fillFromAPI($result->getResult());

        // done, return true to confirm successfull action
        return true;
    }

    /**
     * Delete the entity
     *
     * @return  bool
     * @throws  EntityDeleteException
     * @throws  MissingExternalIdentifierException
     * @throws  \Exception
     */
    public function delete() {
        // We need an id to delete the entity
        if (!isset($this->entity->id)) {
            throw new MissingExternalIdentifierException();
        }

        // Create the path
        $path = '/' . static::ENDPOINT . '/' . $this->entity->id;

        // Do API call
        $result = $this->api->delete($path);

        // Throw exception for a unexpected HTTP status code
        if ($result->getStatus() !== 204) {
            throw new EntityDeleteException();
        }

        // done, return true to confirm successfull action
        return true;
    }

    /**
     * Get the save path
     *
     * @return  string
     * @throws  MissingExternalIdentifierException
     */
    protected function getSavePath() {
        // Make basic path
        $path = '/' . static::ENDPOINT;

        // Add identifier if its a patch call
        if ($this->saveType() === 'patch') {
            if (!isset($this->entity->id)) {
                throw new MissingExternalIdentifierException();
            }
            $path .= '/' . $this->entity->id;
        }

        return $path;
    }

    /**
     * Detect the save type
     *
     * @return  string              patch or post
     */
    protected function saveType() {
        if (isset($this->entity->id) && !empty($this->entity->id)) {
            return  'patch';
        } else {
            return 'post';
        }
    }

}
