<?php

namespace EG\AssignmentBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class EGAssignmentBundle extends Bundle
{
    public function getParent()
    {
        return 'FOSUserBundle';
    }
}
