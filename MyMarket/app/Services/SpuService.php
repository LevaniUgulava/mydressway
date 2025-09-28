<?php

namespace App\Services;

use App\Models\Spu;

class SpuService
{


    private function getGenerateName($name)
    {
        $name = preg_replace('/-/', '', $name);

        preg_match('/^[a-zA-Z0-9\s]+/', $name, $matches);
        $string_name = strtoupper((str_replace(" ", "", $matches[0])));
        $name = rtrim($matches[0]);
        return (string)$string_name;
    }


    public function createOrUpdate($data)
    {
        $name = $this->getGenerateName($data['name']);
        $spu = Spu::firstOrCreate(['name' => $name], [
            'name' => $name,
            'brand' => $data['brand'],
            'category' => $data['category']
        ]);

        return $spu->id;
    }

    public function generateSku($name_string, $size, $color)
    {
        $name = substr($this->getGenerateName($name_string), 0, 2);

        $color = strtoupper(substr($color, 0, 3));

        $color = str_pad($color, 3, "L");

        $serialNumber = rand(100, 999);  

        return $name . "-" . $size . "-" . $color . "-" . $serialNumber;
    }
}
