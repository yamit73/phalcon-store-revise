<?php

use Phalcon\Mvc\Model;

/**
 * Model, to access orders table in db
 */
class Orders extends Model
{
    public $id;
    public $customer_name;
    public $address;
    public $zipcode;
    public $product_id;
    public $quantity;
}
