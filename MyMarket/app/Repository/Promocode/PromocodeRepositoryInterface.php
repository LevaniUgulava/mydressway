<?php

namespace App\Repository\Promocode;


interface PromocodeRepositoryInterface
{
    function display();
    function getbyid($id);
    function getbyName($name);
    function create($request);
    function delete($id);
    function update($id);
}
