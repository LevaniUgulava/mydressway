<?php

namespace App\Http\Controllers;

use App\Repository\EligibleProduct\EligibleProductRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EligibleProductController extends Controller
{
    private $eligibleproductrepository;
    public function __construct(EligibleProductRepositoryInterface $eligibleproductrepository)
    {
        $this->eligibleproductrepository = $eligibleproductrepository;
    }

    public function display($id)
    {
        $result = $this->eligibleproductrepository->display($id);
        return $result;
    }
    public function create($id, Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'discount' => 'required|numeric|between:0,100'
        ]);

        $ids = $request->input('ids');
        $discount = $request->input('discount');

        $result = $this->eligibleproductrepository->create($id, $ids, $discount);

        return response()->json($result, 200);
    }

    public function delete($id, Request $request)
    {
        $data = $request->validate([
            'id' => 'required|array'
        ]);
        $result = $this->eligibleproductrepository->delete($id, $data);
        return $result;
    }

    public function displayEligibleProduct()
    {
        $user = Auth::user();
        $response = $this->eligibleproductrepository->displayEligibleProduct($user);
        return response()->json($response);
    }
}
