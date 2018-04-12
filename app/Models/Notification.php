<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 *      Tracking notifications sent for fantasy football events/actions
 */
class Notification extends Model
{
    // Use revisionable trait to track model changes
    use \Venturecraft\Revisionable\RevisionableTrait;
    protected $revisionCreationsEnabled = true;
    protected $keepRevisionOf           = [];
    protected $dontKeepRevisionOf       = [];

    protected $connection = 'fantasyfootball';

    protected $table = 'notifications';

    protected $fillable = ['leagueId', 'type', 'hash', 'isProcessed'];

    protected $primaryKey = 'primary';
    public $incrementing  = true;

    public $timestamps = false;


    public static function boot()
    {
        parent::boot();
    }
}
