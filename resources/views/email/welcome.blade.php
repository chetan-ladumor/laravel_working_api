Hello {{$user->name}}
Thank you for create an account.Please verify your email using this link:
{{root('verify',['token'=>$user->verification_token])}}