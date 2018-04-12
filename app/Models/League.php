<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 *      ESPN Fantasy Football League
 */
class League extends Model
{
    // Use revisionable trait to track model changes
    use \Venturecraft\Revisionable\RevisionableTrait;
    protected $revisionCreationsEnabled = true;
    protected $keepRevisionOf           = [];
    protected $dontKeepRevisionOf       = [];

    protected $connection = 'fantasyfootball';

    protected $table = 'leagues';

    protected $fillable = ['leagueId', 'name', 'currentMatchupPeriodId', 'dateDraft', 'dateDraftCompleted', 'finalRegularSeasonMatchupPeriodId', 'tradeDeadline', 'vetoVotesRequired', 'size', 'teamIds', 'playoffTeamCount', 'timePerDraftSelection', 'inviteKey', 'finalMatchupPeriodId', 'regularSeasonMatchupPeriodCount'];

    protected $primaryKey = 'primary';
    public $incrementing  = true;

    public $timestamps = true;

    public function getTeamIds()
    {
        return $this->teamIds;
    }

    public function setDateDraftAttribute($value)
    {
        $date          = \Carbon\Carbon::parse($value);
        $dateFormatted = null;
        if ($date) {
            $dateFormatted = $date->toDateTimeString();
        }
        $this->attributes['dateDraft'] = $dateFormatted;
    }

    public function setDateDraftCompletedAttribute($value)
    {
        $date          = \Carbon\Carbon::parse($value);
        $dateFormatted = null;
        if ($date) {
            $dateFormatted = $date->toDateTimeString();
        }
        $this->attributes['dateDraftCompleted'] = $dateFormatted;
    }

    public function setTradeDeadlineAttribute($value)
    {
        $date          = \Carbon\Carbon::parse($value);
        $dateFormatted = null;
        if ($date) {
            $dateFormatted = $date->toDateTimeString();
        }
        $this->attributes['tradeDeadline'] = $dateFormatted;
    }

    public static function boot()
    {
        parent::boot();
    }
}
