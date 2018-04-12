<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 *      ESPN Fantasy Football transaction metadata (such as a trade)
 */
class Transaction extends Model
{
    // Use revisionable trait to track model changes
    use \Venturecraft\Revisionable\RevisionableTrait;
    use \App\Traits\FormatDates;

    protected $revisionCreationsEnabled = true;
    protected $keepRevisionOf           = [];
    protected $dontKeepRevisionOf       = [];

    protected $connection = 'fantasyfootball';

    protected $table = 'transactions';

    protected $fillable = ['hash', 'leagueId', 'proposingTeamId', 'date', 'dateProposed', 'dateModified', 'dateAccepted', 'dateToProcess', 'statusId', 'activityType', 'transactionLogItemTypeId', 'typeId', 'scoringPeriodToProcess', 'pendingMoveBatchId', 'tradeProposalExpirationDays', 'teamsVotedApproveTrade', 'teamsAcceptedTrade', 'teamsVotedVetoTrade', 'teamsInvolved', 'usersProtestTrade', 'rating', 'sentInitialNotification', 'sentUpdateNotification', 'sentFinalNotification'];

    protected $primaryKey = 'primary';
    public $incrementing  = true;

    public $timestamps = false;

    public function setDateAttribute($value)
    {
        $this->attributes['date'] = $this->isoDateTimeToMySqlFormat($value);
    }

    public function setDateModifiedAttribute($value)
    {
        $this->attributes['dateModified'] = $this->isoDateTimeToMySqlFormat($value);
    }

    public function setDateProposedAttribute($value)
    {
        $this->attributes['dateProposed'] = $this->isoDateTimeToMySqlFormat($value);
    }

    public function setDateAcceptedAttribute($value)
    {
        $this->attributes['dateAccepted'] = $this->isoDateTimeToMySqlFormat($value);
    }

    public function setDateToProcessAttribute($value)
    {
        $this->attributes['dateToProcess'] = $this->isoDateTimeToMySqlFormat($value);
    }

    public static function boot()
    {
        parent::boot();
    }
}
