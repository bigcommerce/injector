<?php
declare(strict_types = 1);
namespace Tests;

use Bigcommerce\Injector\InjectorServiceProvider;
use Pimple\Container;

/**
 * This service provider is a dummy object for the sake of testing the internal (protected) behaviour of
 * the abstract ServiceProvider. Normally you should not do this (protected/private methods are implementation concerns
 * and thus outside the contract implied by the class so not tested directly), but as the abstract service behaves as
 * a service itself exposing behaviour only to its children, and there is no public contract that exercises this
 * behaviour it's important that it's tested.
 * This usually represents a smell where Composition should be favoured over Inheritance - however in the case of
 * Pimple ServiceProviders we explicitly want to prevent this behaviour being accessible ANYWHERE else within the
 * application other than the ServiceProviders themselves. This is a conscious design decision to avoid the container
 * being exposed past bootstrap and should be reasoned about prior to any future iterations upon this design.
 */
class SampleProvider extends InjectorServiceProvider
{
    /**
     * Registers services on the given container.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param Container $app An Container instance
     * @return void
     */
    public function register(Container $app)
    {
        $this->bind("test1", 123);
        $this->get("test1");
        $this->alias("alias", "test1");

        $this->create(self::class, ["abc" => 123]);

        $this->autoBind(self::class);
        $this->autoBindFactory(self::class);

        $this->lazyBind(self::class);
        $this->lazyBindFactory(self::class);
    }
}
