<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Socialite;
use App\User;
use Auth;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest', ['except' => 'logout']);
    }

    public function redirectToProvider(){
      return Socialite::driver('slack')
        ->redirect();
    }

    public function handleProviderCallback(){
      $user = Socialite::driver('slack')->user();
      $slackUserId = $user->id;
      $existingUser = User::firstOrNew(['slack_user_id'=>$slackUserId]);
      $data = $user->user['user']; // Yes, really
      $existingUser->update([
        'name' => $data['name'],
        'email' => $data['email'],
        'avatar' => $data['image_1024'],
      ]);

      $existingUser->save();

      Auth::login($existingUser, true);

      return redirect($this->redirectTo);
    }
}
