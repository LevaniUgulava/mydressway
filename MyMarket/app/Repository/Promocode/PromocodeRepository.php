<?php

namespace App\Repository\Promocode;

use App\Models\Promocode;

class PromocodeRepository implements PromocodeRepositoryInterface
{
    public function display()
    {
        return Promocode::all();
    }
    public function getbyid($id)
    {
        return Promocode::findorfail($id);
    }
    public function getbyName($name)
    {
        return Promocode::where("name", $name)->first();
    }
    public function create($request)
    {
        Promocode::create($request);
    }
    public function delete($id)
    {
        $promocode = $this->getbyid($id);
        $promocode->delete();
    }
    public function update($id) {}
}
