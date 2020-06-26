<?php

namespace Weblab\MoneyBird\Models;

/**
 * Class Invoice
 * @author Weblab.nl - Eelco Verbeek
 */
class Invoice extends AbstractInvoice {

    // define the invoice ENDPOINT and ENTITY constants
    const ENDPOINT  = 'sales_invoices';
    const ENTITY    = 'sales_invoice';

    // define the invoice mutable fields
    protected $mutable = [
        'contact_id', 'original_estimate_id', 'document_style_id', 'workflow_id', 'reference', 'invoice_sequence_id',
        'remove_invoice_sequence_id', 'invoice_date', 'first_due_interval', 'currency', 'prices_are_incl_tax',
        'payment_conditions', 'discount'
    ];

    /**
     * Register a payment done for the invoice
     *
     * @param   Payment     $payment
     * @return  mixed
     * @throws  \Exception
     */
    public function addPayment(Payment $payment) {
        // Setup the path
        $path = static::ENDPOINT . '/' . $this->id . '/' . $payment::ENDPOINT;

        // Do the API call
        return $this->api->post($path, $payment->toJSON());
    }

    /**
     * Delete payments linked to the invoice
     *
     * @return mixed
     * @throws \Exception
     */
    public function deletePayments() {
        $payments = $this->api->payments->getPaymentsForInvoice($this);

        foreach ($payments as $payment) {
            // Setup the path
            $path = static::ENDPOINT . '/' . $this->id . '/' . $payment::ENDPOINT . '/' . $payment->id;

            // Do the API call
            $result = $this->api->delete($path);

            // Throw exception for a unexpected HTTP status code
            if ($result->getStatus() !== 204) {
                throw new EntityDeleteException();
            }
        }
    }

    /**
     * Send the invoice to the customer
     *
     * @param   SendSettings    $sendInvoice
     * @return  mixed
     * @throws  \Exception
     */
    public function send(SendSettings $sendInvoice) {
        // Setup the path
        $path = static::ENDPOINT . '/' . $this->id . '/' . $sendInvoice::ENDPOINT;

        // Do the API call
        return $this->api->patch($path, $sendInvoice->toJSON());
    }

    /**
     * Add a note to the invoice
     *
     * @param   Note    $note
     *
     * @return  mixed
     *
     * @throws  \Exception
     */
    public function addNote(Note $note) {
        // Setup the path
        $path = static::ENDPOINT . '/' . $this->id . '/' . $note::ENDPOINT;

        // Do the API call
        return $this->api->post($path, $note->toJSON());
    }

}
