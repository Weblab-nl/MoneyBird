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

    /**
     * @var \stdClass Holds the various property values
     */
    protected $entity;

    /**
     * @var \stdClass Holds the old version of the property values
     */
    protected $old;

    /**
     * @var array Properties that can be changed through the API. If non are added all properties will pass
     */
    protected $mutable = [];

    /**
     * AbstractModel constructor.
     * @param   \stdClass|null  $entity
     * @param   bool            $fillFromAPI
     */
    public function __construct(\stdClass $entity = null, $fillFromAPI = false) {
        // Create the entity as standard class if no entity is passed or create it from the API-resonse or
        // otherwise fill it from the passed entity
        if (is_null($entity)) {
            $this->entity = new \stdClass();
        } else if ($fillFromAPI) {
            $this->fillFromAPI($entity);
        } else {
            $this->fill($entity);
        }
    }

    /**
     * Magic getter for properties
     *
     * @param   string  $name
     * @return  mixed
     */
    public function __get($name) {
        // if the property does not exist, return null
        if (!isset($this->entity->{$name})) {
            return null;
        }

        // return the requested property
        return $this->entity->{$name};
    }

    /**
     * Magic setter for properties
     *
     * @param   string  $name
     * @param   mixed   $value
     * @return  $this
     */
    public function __set($name, $value) {
        // Check if the property is mutable
        if ($this->isMutable($name)) {
            $this->entity->{$name} = $value;
        }

        // done, return this to make chaining possible
        return $this;
    }

    /**
     * Read the properties from an array or object and stores them
     *
     * @param $entity
     * @return $this
     */
    public function fill($entity) {
        // create a new standard class
        $this->entity = new \stdClass;

        // fill the entity with the variable/value pairs
        foreach ($entity as $var => $value) {
            $this->{$var} = $value;
        }

        // done, retrn this to make chaining possible
        return $this;
    }

    /**
     * Fill the properties with the API response
     *
     * @param $entity
     */
    protected function fillFromAPI($entity) {
        // set the entity
        $this->entity = $entity;

        // if there is no old-entity, clone the current entity and set is as the old entity
        if (!isset($this->old)) {
            $this->old = clone $this->entity;
        }
    }

    /**
     * Returns a MoneyBord REST Client instance
     *
     * @return  MoneyBird
     */
    protected function connection() {
        return MoneyBird::getInstance();
    }

    /**
     * @param   integer         $id
     * @return  null|static
     * @throws  \Exception
     */
    public static function find($id) {
        // Get REST Client
        $moneyBird = MoneyBird::getInstance();

        // Build path
        $path = '/' . static::ENDPOINT . '/' . $id . '.json';

        // Do API call
        $result = $moneyBird->get($path);

        // If status not 200 return null
        if ($result->getStatus() !== 200) {
            return null;
        }

        // Entity found. Return new instance of self
        return new static($result->getResult(), true);
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
        $result = $this->connection()->{$saveType}($this->getSavePath(), $this->toJSON($saveType === 'patch'));

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
        $path = '/' . static::ENDPOINT . '/' . $this->entity->id . '.json';

        // Do API call
        $result = $this->connection()->delete($path);

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

        return $path . '.json';
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

    /**
     * Check if the property is mutable.
     *
     * @param   string  $name
     * @return  bool
     */
    protected function isMutable($name) {
        // If there are no mutable properties return true
        if (empty($this->mutable)) {
            return true;
        }

        return in_array($name, $this->mutable);
    }

    /**
     * Converts the properties to an array
     *
     * @param   bool    $changesOnly    If enabled it will only convert the made changes to an array
     * @return  array
     */
    public function toArray($changesOnly = false) {
        $data = [];
        foreach ($this->entity as $var => $value) {
            // Check if the property is mutable and if changesonly is enabled if the the property was changed
            if ($this->isMutable($var) && (!$changesOnly || ($changesOnly && $this->isPropertyChanged($var)))) {
                $data[$var] = $value;
            }
        }

        // If changesOnly is enabled and there were changes, add the id (this will never change but is mandatory)
        if ($changesOnly && !empty($data) && isset($this->entity->id)) {
            $data['id'] = $this->entity->id;
        }

        return $data;
    }

    /**
     * Check if the passed property was changed
     *
     * @param   string  $var
     * @return  bool
     */
    protected function isPropertyChanged($var) {
        if (!isset($this->old) || !property_exists($this->old, $var)) {
            return false;
        }

        return $this->old->{$var} !== $this->entity->{$var};
    }

    /**
     * Convert the properties to a JSON
     *
     * @param   bool    $changesOnly    If enabled it will only convert the made changes
     * @return  string
     */
    public function toJSON($changesOnly = false) {
        return json_encode([static::ENTITY => $this->toArray($changesOnly)]);
    }

}
