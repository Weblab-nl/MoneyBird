<?php

namespace Weblab\MoneyBird\Models;

use Weblab\MoneyBird\Exceptions\MissingExternalIdentifierException;

/**
 * Class AbstractInvoice
 * @author Weblab.nl - Eelco Verbeek
 */
abstract class AbstractInvoice extends AbstractModel {

    /**
     * @var \Weblab\Database\Models\Company     The company that is billed
     */
    protected $company;

    /**
     * @var array   The products / services the billed company will receive
     */
    protected $products = [];

    /**
     * Set the company
     *
     * @param   \Weblab\Database\Models\Company     $company
     * @throws  MissingExternalIdentifierException
     */
    public function setCompany(\Weblab\Database\Models\Company $company) {
        // set the company
        $this->company = $company;

        // If the company does not have a MoneyBird ID throw exception
        if (empty($this->company->moneybird_id)) {
            throw new MissingExternalIdentifierException();
        }

        // set the contact_id belonging to this company
        $this->contact_id = $company->moneybird_id;
    }

    /**
     * Get the company
     *
     * @return  \Weblab\Database\Models\Company
     */
    public function getCompany() {
        return $this->company;
    }

    /**
     * Add a product
     *
     * @param   Product     $product
     */
    public function addProduct(Product $product) {
        $this->products[] = $product;
    }

    /**
     * Get the products
     *
     * @return array
     */
    public function getProducts() {
        return $this->products;
    }

    /**
     * Converts the property to an array
     *
     * @param   bool    $changesOnly    If enabled it will only convert the made changes to an array
     * @return  array
     */
    public function toArray($changesOnly = false) {
        $data = parent::toArray($changesOnly);

        // Also add the products
        foreach ($this->products as $product) {
            $product = $product->toArray($changesOnly);
            if (!empty($product)) {
                $data['details_attributes'][] = $product;
            }
        }

        return $data;
    }

    /**
     * Fill the invoice with data from the API
     *
     * @param $entity
     */
    protected function fillFromAPI($entity) {
        $this->products = [];

        // Parse the products from the data
        foreach ($entity->details as $product) {
            $this->addProduct(new Product($product, true));
        }

        // unset the entity-details
        unset($entity->details);

        // fill the entity with results from the API-response
        parent::fillFromAPI($entity);
    }

}
