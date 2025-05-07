<?php

namespace App\Helpers;

use LaravelLang\Translator\Facades\Translate;

class Translator
{
    public static function translate($data, $lang)
    {

        $Translatedata = Translate::text(
            text: $data,
            to: $lang
        );

        return $Translatedata;
    }
}
