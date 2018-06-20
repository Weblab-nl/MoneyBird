<?php

namespace Weblab\MoneyBird\Models;

/**
 * Class AbstractInvoice
 * @author Weblab.nl - Eelco Verbeek
 */
abstract class AbstractInvoice extends AbstractModel {

    /**
     * @var array   The products / services the billed company will receive
     */
    protected $products = [];

    /**
     * Add a product
     *
     * @param   InvoiceProduct     $product
     */
    public function addProduct(InvoiceProduct $product) {
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
            $this->addProduct(new InvoiceProduct($product, true));
        }

        // unset the entity-details
        unset($entity->details);

        // fill the entity with results from the API-response
        parent::fillFromAPI($entity);
    }

}
