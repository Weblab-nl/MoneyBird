<?php

namespace Weblab\MoneyBird\Models;

/**
 * Class Payment
 * @author Weblab.nl - Eelco Verbeek
 */
class Note {

    use ModelTrait;

    const ENDPOINT  = 'notes';
    const ENTITY    = 'note';

    // define the product mutable fields
    protected $mutable = [
        'id', 'note', 'todo', 'assignee_id'
    ];

}
