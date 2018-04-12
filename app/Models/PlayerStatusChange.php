<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 *      ESPN Fantasy Football transaction details
 */
class PlayerStatusChange extends Model
{
    // Use revisionable trait to track model changes
    use \Venturecraft\Revisionable\RevisionableTrait;

    protected $revisionCreationsEnabled = true;
    protected $keepRevisionOf           = [];
    protected $dontKeepRevisionOf       = [];

    protected $connection = 'fantasyfootball';

    protected $table = 'playerStatusChanges';

    protected $fillable = ['playerId', 'currentStatus', 'priorStatus', 'IsBetter', 'IsProcessed'];

    protected $primaryKey = 'primary';
    public $incrementing  = true;

    public $timestamps = false;

    public function player()
    {
        return $this->hasOne('\App\Models\EspnAllPlayers', 'playerId', 'playerId');
    }

    public function roster()
    {
        return $this->belongsTo('\App\Models\Roster', 'playerId', 'playerId');
    }

    public static function boot()
    {
        parent::boot();
    }
}
