<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'slack_user_name', 'slack_user_id', 'slack_team_id', 'slack_team_domain'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $primary = 'id';

    public function getId()
    {
      return $this->id;
    }

    public function getTwitchUsername($firstOnly = true)
    {
      $usernames = $this->twitch()->pluck('twitch_username');
      if ($firstOnly) {
          return $usernames->first();
      }
      return $usernames;
    }

    public function twitch()
    {
        return $this->hasMany('App\Http\Models\Twitch');
    }

}
