<?php

namespace App\Exceptions;

use Exception;

/**
 * Excepción para transiciones de estado inválidas
 * 
 * @package App\Exceptions
 */
class InvalidStateTransitionException extends Exception
{
    /**
     * Constructor
     * 
     * @param string $message
     * @param int $code
     * @param Exception|null $previous
     */
    public function __construct(string $message = "Transición de estado inválida", int $code = 422, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}