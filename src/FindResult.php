<?php

namespace Bigcommerce\Injector;

/**
 * Sentinel returned by {@see FindableContainerInterface::find()} to signal "no entry for this id".
 *
 * find() returns this instead of NULL so that an entry which is itself NULL can be returned and told
 * apart from a genuine absence. A service may legitimately resolve to NULL (e.g. an optional setting);
 * conflating that with "not registered" is what previously stopped the Injector from supplying NULL to
 * nullable-but-present constructor dependencies.
 */
enum FindResult
{
    case NotFound;
}
