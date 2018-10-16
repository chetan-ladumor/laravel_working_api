<?php

use App\Category;
use App\Product;
use App\Transaction;
use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	DB::statement('SET FOREIGN_KEY_CHECKS= 0');// disable foreign key checks to rempve error:Syntax error or access violation - Cannot truncate a table referenced in a foreign key constraint
        // $this->call(UsersTableSeeder::class);
        //every time databse seeding runs we  restarts the data seeding on its orginal ststaes
        User::truncate();
        Category::truncate();
        Transaction::truncate();
        Product::truncate();
        DB::table('category_product')->truncate();

        User::flushEventListeners();
        Category::flushEventListeners();
        Product::flushEventListeners();
        Transaction::flushEventListeners();

        $usersQuantity=200;
        $categoryQuantity=30;
        $productsQuantity=1000;
        $transactionsQuantity=1000;
        
        factory(User::class,$usersQuantity)->create();
        factory(Category::class,$categoryQuantity)->create();
		factory(Product::class,$productsQuantity)->create()->each(

			function($product)  //receives every specific products.
			{
				$categories=Category::all()->random(mt_rand(1,5))->pluck('id'); //array
				$product->categories()->attach($categories);
			}

		);
        factory(Transaction::class,$transactionsQuantity)->create();


    }
}
