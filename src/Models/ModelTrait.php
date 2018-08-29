<?php
/**
 * Created by PhpStorm.
 * User: eelco
 * Date: 20/06/2018
 * Time: 09:51
 */

namespace Weblab\MoneyBird\Models;


trait ModelTrait {

    /**
     * @var \stdClass   Holds the various property values
     */
    protected $entity;

    /**
     * @var \stdClass   Holds the old version of the property values
     */
    protected $old;

    /**
     * @var array       Properties that can be changed through the API. If non are added all properties will pass
     */

    public function __construct($entity, $fillFromAPI = false) {
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

        // done, return this to make chaining possible
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

        if (empty($data)) {
            $data = (object) $data;
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

    /**
     * Check if the property is mutable.
     *
     * @param   string  $name
     * @return  bool
     */
    protected function isMutable($name) {
        // If there are no mutable properties return true
        if (!isset($this->mutable) || empty($this->mutable)) {
            return true;
        }

        return in_array($name, $this->mutable);
    }

}
