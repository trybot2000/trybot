<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 *      ESPN Fantasy Football schedule item (i.e. matchup)
 */
class Team extends Model
{
    // Use revisionable trait to track model changes
    use \Venturecraft\Revisionable\RevisionableTrait;
    protected $revisionCreationsEnabled = true;
    protected $keepRevisionOf           = [];
    protected $dontKeepRevisionOf       = [];

    protected $connection = 'fantasyfootball';

    protected $table = 'teams';

    protected $fillable = ['leagueId', 'teamId', 'teamName', 'teamLocation', 'teamNickname', 'teamAbbrev', 'overallWins', 'overallLosses', 'overallTies', 'divisionStanding', 'overallStanding', 'divisionId', 'waiverRank', 'streakLength', 'streakType', 'pointsFor', 'pointsAgainst', 'overallAcquisitionTotal', 'dropsTotal', 'ownerLastName', 'ownerUserName', 'ownerFirstName', 'ownerPhotoUrl', 'ownerUserProfileId', 'logoUrl'];

    protected $primaryKey = 'primary';

    public $timestamps = true;

    public function getTeamId()
    {
        return $this->teamId;
    }

    public function getName()
    {
      return $this->teamName;
    }

    public function setIsByeAttribute($value)
    {
        $this->attributes['isBye'] = (int) $value;
    }

    public function teamNotificationId()
    {
        return $this->hasOne('\App\Models\TeamsNotificationId', 'teamId', 'teamId')->select('teamId', 'slackUserId');
    }

    public static function boot()
    {
        parent::boot();
    }
}
