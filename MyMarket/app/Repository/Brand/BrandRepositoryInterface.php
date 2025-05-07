<?php

namespace App\Repository\Brand;

use Illuminate\Http\Request;

interface BrandRepositoryInterface
{

    public function display();
    public function admindisplay();
    public function displaybyid($id);
    public function create(Request $request);
    public function update($id, Request $request);
    public function ProductRelation($product_id, $brand_id);
    public function delete($id);
    public function notactive($id);
    public function active($id);
}
