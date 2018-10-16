<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\ApiController;
use App\Product;
use App\Seller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SelleProductController extends ApiController
{
    public function __construct()
    {
        parent::__construct();
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Seller $seller)
    {
        $products=$seller->products;
        return $this->showAll($products);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request,User $seller)
    {
        //User because at the first time when user creates product at that time that user is not seller so instead of Seller we have used the User model.

        $rules=[
            'name'=>'required',
            'description'=>'required',
            'quantity'=>'required|integer|min:1',
            'image'=>'required|image',

        ];

        $this->validate($request,$rules);

        $data=$request->all();
        $data['status']=Product::UNAVAILABLE_PRODUCT;
        $data['image']=$request->image->store('');
        $data['seller_id']=$seller->id;

        $product = Product::create($data);

        return $this->showOne($product);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Seller  $seller
     * @return \Illuminate\Http\Response
     */
    public function show(Seller $seller)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Seller  $seller
     * @return \Illuminate\Http\Response
     */
    public function edit(Seller $seller)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Seller  $seller
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Seller $seller,Product $product)
    {
        $rules=[
            'quantity'=>'integer|min:1',
            'status'=>'in: '.Product::AVAILABLE_PRODUCT. ','.Product::UNAVAILABLE_PRODUCT,
            'image'=>'image'
        ];
        
        $this->validate($request,$rules);
        
        $this->checkSeller($seller,$product);

        $product->fill($request->intersect([
            'name',
            'description',
            'quantity'
        ]));

        if($request->has('status'))
        {
            $product->status= $request->status;
            if($product->isAvailable() && $product->categories()->count() == 0)
            {
                return $this->errorResponse('An Active Product Must have atleast one category.',409);
            }
        }

        if($request->hasFile('image'))
        {
            Storage::delete($product->image);
            $product->image=$request->image->store('');
        }

        if($product->isClean())
        {
            return $this->errorResponse('You need to specify a different value to update.',422);
        }

        $product->save();
        return $this->showOne($product);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Seller  $seller
     * @return \Illuminate\Http\Response
     */
    public function destroy(Seller $seller, Product $product)
    {
        //remove specific product of seller

        $this->checkSeller($seller,$product);
        Storage::delete($product->image);
        $product->delete();
        return $this->showOne($product);
    }

    protected function checkSeller(Seller $seller,Product $product)
    {
        if($seller->id != $product->seller_id)
        {
            throw new HttpException(422,'The Specified Seller Is not the Actual Seller Of this Product.');
        }
    }
}
