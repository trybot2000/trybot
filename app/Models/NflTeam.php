<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 *      An NFL team's info
 */
class NflTeam extends Model
{
    // Use revisionable trait to track model changes
    use \Venturecraft\Revisionable\RevisionableTrait;
    protected $revisionCreationsEnabled = true;
    protected $keepRevisionOf           = [];
    protected $dontKeepRevisionOf       = [];

    protected $connection = 'fantasyfootball';

    protected $table = 'nflTeams';

    protected $fillable = ['proTeamId', 'logo', 'name', 'abbreviation', 'location', 'shortDisplayName', 'displayName', 'color', 'altColor', 'record', 'wins', 'losses', 'rankCurrent', 'rankPrevious', 'rankType', 'rankHeadline'];

    protected $primaryKey = 'primary';
    public $incrementing  = true;

    public $timestamps = false;

    public function getColor()
    {
        if (!is_null($this->altColor)) {
            return $this->altColor;
        }
        return $this->color;
    }

    public static function boot()
    {
        parent::boot();
    }
}
