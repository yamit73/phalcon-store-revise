<?php
namespace App\Components;

use Phalcon\Escaper;

class MyEscaper
{
    /**
     * function to sanitize input data of a form
     *
     * @param [type] array, accept form data
     * @return [type] array, return sanitized data
     */
    public function sanitize($data)
    {
        $escaper=new Escaper();
        $res=array();
        foreach ($data as $key => $val) {
            $res[$key]=$escaper->escapeHtml($val);
        }
        return $res;
    }
}
