<?php

namespace Winged\App;

/**
 * Class ResponseMiddleware
 */
class ResponseMiddleware extends Middleware
{

    /**
     * @param null $callback
     *
     * @return $this
     */
    public function setCallback($callback)
    {
        if (is_callable($callback)) {
            $this->callback = \Closure::bind($callback, $this, 'Winged\App\ResponseMiddleware');;
        }
        return $this;
    }


}