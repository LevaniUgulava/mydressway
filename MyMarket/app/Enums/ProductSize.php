<?php

namespace App\Enums;

enum ProductSize: string
{
    case XXS = 'XXS';
    case XS = 'XS';
    case S = 'S';
    case M = 'M';
    case L = 'L';
    case XL = 'XL';
    case XXL = 'XXL';

    case Numeric_35 = "35";
    case Numeric_36 = "36";
    case Numeric_37 = "37";
    case Numeric_38 = '38';
    case Numeric_39 = '39';
    case Numeric_40 = '40';
    case Numeric_41 = '41';
    case Numeric_42 = '42';
    case Numeric_43 = "43";
    case Numeric_44 = "44";


    public static function letterBasedSizes(): array
    {
        return [
            self::XXS,
            self::XS,
            self::S,
            self::M,
            self::L,
            self::XL,
            self::XXL
        ];
    }

    public static function numericBasedSizes(): array
    {
        return [
            self::Numeric_35,
            self::Numeric_36,
            self::Numeric_37,
            self::Numeric_38,
            self::Numeric_39,
            self::Numeric_40,
            self::Numeric_41,
            self::Numeric_42,
            self::Numeric_43,
            self::Numeric_44,
        ];
    }
}
