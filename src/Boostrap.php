<?php

namespace BIT\DhvJugend;

use BIT\DhvJugend\Event\RegistrationForm;

/**
 * @author Christoph Bessei
 */
class Boostrap
{
    public function run()
    {
        RegistrationForm::registerHooks();
    }
}
