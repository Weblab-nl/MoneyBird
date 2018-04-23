<?php

namespace Weblab\MoneyBird\Models;

/**
 * Class Product
 * @author Weblab.nl - Eelco Verbeek
 */
class Product extends AbstractModel {

    // define the product mutable fields
    protected $mutable = [
        'id', 'description', 'period', 'price', 'amount', 'tax_rate_id', 'ledger_account_id', 'product_id', 'row_order'
    ];

}
