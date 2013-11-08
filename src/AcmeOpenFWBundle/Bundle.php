<?php
/**
 * @author AlexanderC <self@alexanderc.me>
 * @date 11/8/13
 * @time 3:07 PM
 */

namespace AcmeOpenFWBundle;


use OpenFW\Traits\Bundle as MainBundle;
use OpenFW\Traits\ContainerAware;

class Bundle
{
    use MainBundle;
    use ContainerAware;

    public function checkEnvironment()
    {
        // validate environment
    }

    public function init()
    {
        // initialize the bundle
    }
} 