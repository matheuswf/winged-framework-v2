<?php

namespace Winged\App;

/**
 * Class RequestMiddleware
 */
class RequestMiddleware extends Middleware
{

    /**
     * @param null $callback
     *
     * @return $this
     */
    public function setCallback($callback)
    {
        if (is_callable($callback)) {
            $this->callback = \Closure::bind($callback, $this, 'Winged\App\RequestMiddleware');;
        }
        return $this;
    }


}