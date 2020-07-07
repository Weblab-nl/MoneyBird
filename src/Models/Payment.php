<?php

namespace Weblab\MoneyBird\Models;

/**
 * Class Payment
 * @author Weblab.nl - Eelco Verbeek
 */
class Payment {

    use ModelTrait;

    const ENDPOINT  = 'payments';
    const ENTITY    = 'payment';

    // define the product mutable fields
    protected $mutable = [
        'id', 'payment_date', 'price', 'price_base', 'financial_account_id', 'financial_mutation_id'
    ];

}
