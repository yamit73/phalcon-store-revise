<?php
namespace App\Components;

/**
 * Helper class
 * has some functions to support the execution
 */
class Helper
{
    /**
     * Function to check if user is logged in
     *
     * @return boolean
     */
    public function isLoggedIn()
    {
       if ($this->session->has('user_id')) {
           return true;
       }
       return false;
    }
}
