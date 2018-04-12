<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 *     All NFL schedule information
 */
class NflSchedule extends Model
{
    // Use revisionable trait to track model changes
    use \Venturecraft\Revisionable\RevisionableTrait;
    use \App\Traits\FormatDates;

    protected $revisionCreationsEnabled = true;
    protected $keepRevisionOf           = [];
    protected $dontKeepRevisionOf       = [];

    protected $connection = 'fantasyfootball';

    protected $table = 'nflSchedule';

    protected $fillable = ['id', 'dateStart', 'seasontype', 'week', 'weekday', 'venueId', 'venueIndoor', 'venueLocation', 'venueFullName', 'weatherConditions', 'weatherTemperature', 'neutralSite', 'statusId', 'broadcastIsNational', 'broadcastNetwork', 'oddsOverUnder', 'oddsFavoriteId', 'oddsSpread', 'oddsDetail', 'statusDetail', 'statusState', 'period', 'displayClock', 'attendance', 'homeTeamId', 'homeTeamScore', 'homeTeamLinescores', 'awayTeamId', 'awayTeamScore', 'awayTeamLinescores', 'winnerTeamId', 'conferenceCompetition', 'headlinesDescription', 'headlinesShortLinkText', 'headlinesType', 'links'];

    protected $primaryKey = 'primary';
    public $incrementing  = true;

    public $timestamps = false;

    public function setDateStartAttribute($value)
    {
        $this->attributes['dateStart'] = $this->isoDateTimeToMySqlFormat($value);
    }

    public function homeTeam()
    {
        return $this->hasOne('\App\Models\NflTeam', 'proTeamId', 'homeTeamId');
    }

    public function awayTeam()
    {
        return $this->hasOne('\App\Models\NflTeam', 'proTeamId', 'awayTeamId');
    }

    public static function boot()
    {
        parent::boot();
    }
}
