<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\ApiController;
use App\Transaction;
use Illuminate\Http\Request;

class TransactionController extends ApiController
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
    public function index()
    {
        $transactions=Transaction::all();
        return $this->showAll($transactions);
    }

    

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Transaction $transaction)
    {
        return $this->showOne($transaction);
    }

    // public function transactionCategory($id)
    // {   
    //     $transaction=Transaction::findOrFail($id)->product->categories;
    //     //$categories=$transaction;
    //     return $this->showAll($transaction);
    // }

    
}