<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

/**
 *  Model for Twitch username association records
 */
class Twitch extends Model
{
    // Use revisionable trait to track model changes
    use \Venturecraft\Revisionable\RevisionableTrait;
    protected $revisionCreationsEnabled = true;
    protected $keepRevisionOf           = [];
    protected $dontKeepRevisionOf       = [];

    protected $connection = 'mysql';

    protected $table = 'twitch';

    protected $fillable = ['user_id', 'twitch_username', 'twitch_user_id', 'is_active'];

    protected $primaryKey = 'id';

    public $incrementing = true;

    public $timestamps = true;

    public function getTwitchUsername()
    {
        return $this->twitch_username;
    }

    public function getUserId()
    {
        return $this->user_id;
    }

    public function user()
    {
        return $this->hasOne('App\User', 'id', 'user_id');
    }

    public function isActive()
    {
        return $this->is_active == 1;
    }

    public static function boot()
    {
        parent::boot();
    }
}
