<?php

namespace Weblab\MoneyBird\Repositories;

use Weblab\MoneyBird\Models\Invoice;

/**
 * Class Payments
 * @author Weblab.nl - Eelco Verbeek
 */
class Payments extends AbstractRepository {

    const MODEL = 'payment';

    /**
     * Get all the payments for an invoice
     *
     * @param   Invoice     $invoice
     *
     * @return  array
     *
     * @throws  \Exception
     */
    public function getPaymentsForInvoice(Invoice $invoice) {
        // Get the class for the model
        $class = $this->getModelClass();

        // Build path
        $path = $invoice::ENDPOINT . '/' . $invoice->id . '/' . $class::ENDPOINT;

        // Do API call
        $result = $this->api->get($path);

        // If status not 200 return null
        if ($result->getStatus() !== 200) {
            return [];
        }

        // Initialize payments array
        $payments = [];

        // Loop API results
        foreach ($result->getResult() as $payment) {
            // Store payments in array
            $payments[] = new $class($payment, true);
        }

        // Return found payments
        return $payments;
    }

}
