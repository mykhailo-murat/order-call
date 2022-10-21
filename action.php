<?php
require_once 'order-call.php';

var_dump($_POST);
if (class_exists('OrderCall')) {
    $orderCall = new OrderCall();
    $orderCall->form_handler($_POST);
};