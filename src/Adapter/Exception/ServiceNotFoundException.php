<?php
namespace Bigcommerce\Injector\Adapter\Exception;

use Interop\Container\Exception\NotFoundException;

class ServiceNotFoundException extends \Exception implements NotFoundException
{

}