<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\ApiController;
use App\Mail\UserCreated;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class UserController extends ApiController
{
    public function __construct()
    {
        $this->middleware('client.credentials')->only(['store','resend']);
        $this->middleware('auth:api')->except(['store','resend','verify']);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //return all lists of user that we have.
        $users = User::all();
        return $this->showAll($users);// 200 is response code meaning that is everything is ok.
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
    public function store(Request $request)
    {
        $rules=[
            'name'=>'required',
            'email'=>'required|email|unique:users',   // must ne uniqueue in users table
            'password'=>'required|min:6|confirmed',
        ];

        $this->validate($request,$rules);

        $data=$request->all();
        $data['password'] =bcrypt($request->password); // encrypt the user password
        $data['verified']= User::UNVERIFIED_USER;
        $data['verification_token']=User::generateVerificationCode();
        $data['admin'] =User::REGULAR_USER;

        $user=User::create($data);
        
       return $this->showOne($user,201); //201 means that data has been created. 

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    // public function show($id)
    // {
    //     //shows the specific instance of user depending on the id receives
    //     //if the id is not present then we need to return errro message 400 'user not found'

    //     $user=User::findOrFail($id);
    //     return $this->showOne($user);

    // }

    //implicit model binding
    public function show(User $user)
    {
        //shows the specific instance of user depending on the id receives
        //if the id is not present then we need to return errro message 400 'user not found'

        //$user=User::findOrFail($id); don't need this line of code.
        return $this->showOne($user);

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        // to change or edit any specific instance that already exists
        //$user=User::findOrFail($id);
        $rules=[
            
            'email'=>'email|unique:users,email,'.$user->id,   // except current email
            'password'=>'min:6|confirmed',
            'admin'=>'in:'. User::ADMIN_USER. ','. User::REGULAR_USER,
        ];

        //$this->validate($request,$rules);
        if($request->has('name'))
        {
            $user->name=$request->name;
        }

        if($request->has('email') && $user->email != $request->email)
        {
            $user->verified = User::UNVERIFIED_USER;
            $user->verification_token=User::generateVerificationCode();
            $user->email=$request->email;
        }

        if($request->has('password'))
        {
            $user->password=bcrypt($request->password);
        }

        if($request->has('admin'))
        {
            if(!$user->isverified())
            {
                return $this->errorResponse('Only verified users can modify the admin field',409);
            }
            $user->admin=$request->admin;
        }

        if(!$user->isDirty())
        {
            return $this->errorResponse('You need to specify a different value to update.',422);
        }

        $user->save();
        return $this->showOne($user);
    }   

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        //$user=User::findOrFail($id);
        $user->delete();
        return $this->showOne($user);
    }

    public function verify($token)
    {
        $user=User::where('verification_token',$token)->firstOrFail();
        $user->verified=User::VERIFIED_USER;
        $user->verification_token=null;
        $user->save();
        return $this->showMessage('The account has been verified successully.');
    }

    public function resend(User $user)
    {
        if($user->isverified())
        {
            return $this->errorResponse('This user is already verified.',409);
        }

        retry(5,function() use($user){
            Mail::to($user)->send(new UserCreated($user));
        },100);
        return $this->showMessage('The verification message has been sent.');
    }
}
