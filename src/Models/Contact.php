<?php

namespace Weblab\MoneyBird\Models;

/**
 * Class Contact
 * @author Weblab.nl - Eelco Verbeek
 */
class Contact extends AbstractModel {

    // define the invoice ENDPOINT and ENTITY constants
    const ENDPOINT  = 'contacts';
    const ENTITY    = 'contact';

    // define the invoice mutable fields
    protected $mutable = [
        'company_name', 'address1', 'address2', 'zipcode', 'city', 'country', 'phone', 'delivery_method', 'customer_id',
        'tax_number', 'firstname', 'lastname', 'chamber_of_commerce', 'bank_account', 'send_invoices_to_attention',
        'send_invoices_to_email', 'send_estimates_to_attention', 'send_estimates_to_email', 'sepa_active', 'sepa_iban',
        'sepa_iban_account_name', 'sepa_bic', 'sepa_mandate_id', 'sepa_mandate_date', 'sepa_sequence_type',
        'credit_card_number', 'credit_card_reference', 'credit_card_type', 'invoice_workflow_id',
        'estimate_workflow_id', 'email_ubl'
    ];

}
