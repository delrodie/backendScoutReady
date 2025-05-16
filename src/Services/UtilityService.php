<?php

namespace App\Services;

class UtilityService
{
    public function validForm($str): string
    {
        return htmlspecialchars(stripslashes(trim($str)), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}