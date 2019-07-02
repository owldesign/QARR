<?php

namespace owldesign\qarr\errors;

use yii\base\Exception;

class UserNotAllowedException extends Exception
{
    /**
     * @return string the user-friendly name of this exception
     */
    public function getName()
    {
        return 'User operation not allowed';
    }
}