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

}
