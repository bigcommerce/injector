<?php
namespace Bigcommerce\Injector\Exception;

use Exception;

/**
 * Exception denoting something went wrong when trying to create or invoke a method using the Injector. This usually
 * means the class we were trying to construct (or method we were trying to invoke) needed parameters we were not able
 * to satisfy and inject from the container or passed additional parameters.
 */
class InjectorInvocationException extends ServiceException
{
    /**
     * InjectorInvocationException constructor.
     * @param string $message
     * @param Exception $previous
     */
    public function __construct($message, ?Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
