<?php

/**
 * Class Endpoint
 *
 * @package Paylike\Endpoint
 */
abstract class Paylike_Endpoint_Endpoint
{
    /**
     * @var Paylike_Paylike
     */
    protected $paylike;

    /**
     * Endpoint constructor.
     *
     * @param $paylike
     */
    function __construct($paylike)
    {
        $this->paylike = $paylike;
    }
}
