<?php

namespace Weblab\MoneyBird\Models;

/**
 * Class SendInvoice
 * @author Weblab.nl - Eelco Verbeek
 */
class SendSettings {

    use ModelTrait;

    const ENTITY    = 'sale_invoice_sending';
    const ENDPOINT  = 'send_invoice';

    protected $mutable = ['delivery_method', 'sending_scheduled', 'deliver_ulb', 'mergeable', 'email_address', 'email_message', 'invoice_date'];

}
