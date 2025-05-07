<?php

namespace App\Observers;

use App\Models\Product;
use App\Models\Sitelog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ProductLoggerObserver
{
    /**
     * Handle the Product "created" event.
     */
    public function created(Product $product): void
    {
        $this->LoggerAction($product, 'created');
    }

    /**
     * Handle the Product "updated" event.
     */
    public function updated(Product $product): void
    {
        $this->LoggerAction($product, 'updated');
    }

    /**
     * Handle the Product "deleted" event.
     */
    public function deleting(Product $product): void
    {
        $this->LoggerAction($product, 'deleted');
    }

    /**
     * Handle the Product "restored" event.
     */
    public function restored(Product $product): void
    {
        //
    }

    /**
     * Handle the Product "force deleted" event.
     */
    public function forceDeleted(Product $product): void
    {
        //
    }


    private function LoggerAction(Product $product, string $action)
    {
        $user = Auth::user();

        Sitelog::create([
            'model' => "Product",
            'model_id' => $product->id,
            'user_id' => $user->id,
            'action' => $action,
            'role' => implode(', ', $user->getRoleNames()->toArray())
        ]);
    }
}
