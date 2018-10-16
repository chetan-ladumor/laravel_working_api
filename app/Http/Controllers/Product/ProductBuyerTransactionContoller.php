<?php

namespace App\Http\Controllers\Product;

use App\Buyer;
use App\Http\Controllers\ApiController;
use App\Product;
use App\Transaction;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductBuyerTransactionContoller extends ApiController
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request,Product $product,User $buyer)
    {
        
        $rules=[
            'quantity'=>'required|integer|min:1',
        ];
        $this->validate($request,$rules);

        if($buyer->id == $product->seller_id)
        {
            return $this->errorResponse('The Buyer Must be different from the seller.',409);
        }
        if(!$buyer->isverified())
        {
            return $this->errorResponse('The Buyer Must be a Verified user.',409);
        }
        if(!$product->seller->isverified())
        {
            return $this->errorResponse('The Seller Must be a Verified user.',409);
        }
        if(!$product->isAvailable())
        {
            return $this->errorResponse('The Product is Not Available.',409);
        }
        if($product->quantity < $request->quantity)
        {
            return $this->errorResponse('The Product does not have enough units for this transaction.',409);
        }

        return DB::transaction(function() use ($request,$product,$buyer){
            $product->quantity -=$request->quantity;
            $product->save();

            $transaction=Transaction::create([
                'quantity'=>$request->quantity,
                'buyer_id'=>$buyer->id,
                'product_id'=>$product->id,
            ]);
            return $this->showOne($transaction,201);
        });
    }

   
}
