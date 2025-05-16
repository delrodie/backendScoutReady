<?php

namespace App\Services;

class UtilityService
{
    public const MODULE_ASN = 'ans';
    public const MODULE_REGION = 'region';
    public const MODULE_DISTRICT = 'district';
    public const MODULE_GROUPE = 'groupe';
    public const MODULE_SCOUT = "scout";
    public const MODULE_ACTIVITE = "activite";
    public function validForm($str): string
    {
        return htmlspecialchars(stripslashes(trim($str)), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}