<?php

namespace Framework;

use Framework\Session;

class Authorization {

    /**
     * check if currunt logged-in user owns a resource
     *@param int $resourceID
     *@return boolean
     */
    public static function isOwner($resourceID)
    {
        $sessionUser = Session::get('user');
        if ($sessionUser !== null && $sessionUser['id']) {
            $sessionUserID = (int)$sessionUser['id'];
            return $sessionUserID === $resourceID;
        }
        return false;
    }
}