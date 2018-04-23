<?php

namespace WebLab\MoneyBird\Models;

/**
 * Class RecurringInvoice
 * @author Weblab.nl - Eelco Verbeek
 */
class RecurringInvoice extends AbstractInvoice {

    // define the recurring invoice ENDPOINT and ENTITY constants
    const ENDPOINT  = 'recurring_sales_invoices';
    const ENTITY    = 'recurring_sales_invoice';

}
