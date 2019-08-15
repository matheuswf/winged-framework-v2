<?php

namespace Winged\App;

use Winged\Route\Route;

/**
 * Class Middleware
 */
class Middleware
{

    protected $callback = null;

    protected $route = null;

    /**
     * RequestMiddleware constructor.
     *
     * @param callable     $callback
     * @param null | Route $route
     */
    public function __construct($callback = null, &$route = null)
    {
        $this->route = $route;
        $this->setCallback($callback);
    }

    /**
     * @return null
     */
    public function getCallback()
    {
        return $this->callback;
    }

    /**
     * @param null $callback
     *
     * @return $this
     */
    public function setCallback($callback)
    {
        if (is_callable($callback)) {
            $this->callback = \Closure::bind($callback, $this, 'Winged\App\Middleware');;
        }
        return $this;
    }

    /**
     * @return null | Route
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * @param null $route
     *
     * @return $this
     */
    public function setRoute(&$route)
    {
        $this->route = &$route;
        return $this;
    }


}