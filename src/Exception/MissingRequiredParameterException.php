<?php
namespace Bigcommerce\Injector\Exception;

/**
 * Internal exception used by the Injector representing a parameter that could not be located (either in the service
 * container or in the provided parameters).
 */
class MissingRequiredParameterException extends ServiceException
{
    /**
     * The name of the method parameter that could not be found
     * @var string
     */
    private $parameterName;

    /**
     * The type (if provided) of the method parameter that could not be found
     * @var string
     */
    private $parameterType;

    /**
     * MissingRequiredParameterException constructor.
     * @param string $parameterName
     * @param string $parameterType
     * @param string $message
     * @param \Exception $previous
     * @internal param int $code
     */
    public function __construct($parameterName, $parameterType, $message, ?\Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
        $this->parameterName = $parameterName;
        $this->parameterType = $parameterType;
    }

    /**
     * The name of the method parameter that could not be found
     * @return string
     */
    public function getParameterName()
    {
        return $this->parameterName;
    }

    /**
     * The type (if provided) of the method parameter that could not be found
     * @return string
     */
    public function getParameterType()
    {
        return $this->parameterType;
    }

    /**
     * Fetch a developer readable string with the parameter name (and if provided type) of the missing parameter.
     * @return string
     */
    public function getParameterString()
    {
        $str = "$" . $this->getParameterName();
        if ($this->parameterType) {
            $str .= " [" . $this->getParameterType() . "]";
        }
        return $str;
    }
}
