<?php

namespace App\Services;

// use App\Notifications\FantasyPlayerStatusChange;
// use App\User;
// use App\Services\FantasyFootball\NFL;
// use App\Services\FantasyFootball\ESPN;
// use App\Services\FantasyFootball\Analysis;
use App\Http\Controllers\Slack\Helpers\Message;
use App\Http\Controllers\Slack\Slack;
use App\Models\EspnAllPlayers;
use App\Models\EspnAllPlayersLog;
use App\Models\FantasyFootball\Log;
use App\Models\League;
use App\Models\Matchup;
use App\Models\NflSchedule;
use App\Models\NflTeam;
use App\Models\Notification;
use App\Models\PlayerProTeamChange;
use App\Models\PlayerStatusChange;
use App\Models\Roster;
use App\Models\RosterLog;
use App\Models\ScheduleItem;
use App\Models\Team;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Facades\Helper;
use Illuminate\Support\Facades\Redis;
use Intervention\Image\ImageManager;
use Ixudra\Curl\Facades\Curl;

/**
 *
 */
class FantasyFootball
{

    public $root;
    public $espnBase = "http://games.espn.com";

    public $debug;

    const RETURN_ROSTER = 0;
    const RETURN_ANALYSIS = 1;
    const RETURN_ALL = 2;

    protected $cookie = 'SWID={1412F6BE-03BA-4329-BED6-4B6F0A2A57F4};espn_s2=AEBc6K1Kb%2B1bZAPwMyDBltpP6DSEw9AffZ6yLWoxoEC84dAE%2F0Tu6mHJEeU0FS8Hv%2BAwvIOlRBtzZKNI22%2BMDSGtgnSWtYUSevHocqOh4hcgS2azfzm2zhjy915rYw924VeiO9Ys%2BL5sFA9L1bIZG6MQT047il7sFzrR56lTv%2FnoPR8GB5cQvZrlsxCDPjIzPOGgf%2FWamAqlC90kLJ%2F9zduldCec81W5WKMGhxLhfDhvjauu1MtM3AnV8tumcUqBTf92GFMyO5xO0cB2eStYs%2B8e;';
    public $config = [];

    public function __construct($debug = false)
    {
        $this->debug = $debug;
        $this->root = rtrim(public_path(), '/') . '/img/ff/';
    }

    /**
     *
     *  NFL
     *
     */

    public function updateNflScheduleSeason()
    {
        $weeks = config('espn.nflScheduleWeeks');
        $r = [];
        foreach ($weeks as $week) {
            $r[$week['week']] = $this->updateNflSchedule($week['seasontype'], $week['week']);
        }
        return $r;
    }

    public function updateNflSchedule($seasontype = null, $week = null)
    {
        if (($seasontype !== null) && ($week !== null)) {
            $url = "http://cdn.espn.go.com/core/nfl/schedule/_/seasontype/" . $seasontype . "/week/" . $week . "?xhr=1";
        } else {
            $weekAndSeasonType = $this->getWeek(true);
            $seasontype = $weekAndSeasonType['seasontype'];
            $week = $weekAndSeasonType['week'];
            $url = "http://cdn.espn.go.com/core/nfl/schedule/_/seasontype/" . $seasontype . "/week/" . $week . "?xhr=1";
        }

        $r = Curl::to($url)
            ->allowRedirect(true)
            ->withHeader('Cookie: ' . $this->cookie)
            ->asJson(true)
            ->get();

        $scheduleInfo = $r['content']['schedule'];
        $games = [];

        foreach ($scheduleInfo as $d => $games) {
            $dateStartOfGameDay = \DateTime::createFromFormat('YmdHis', $d . '070000', new \DateTimeZone('America/Chicago'));

            // echo $dateStartOfGameDay->format('Y-m-d H:m:i');
            foreach ($games['games'] as $game) {
                $tmpGame = array(
                    "id" => null,
                    "dateStart" => null,
                    "seasontype" => $seasontype,
                    "week" => $week,
                    "weekday" => null,
                    "weatherConditions" => null,
                    "weatherTemperature" => null,
                    "neutralSite" => null,
                    "statusId" => null,
                    "statusDetail" => null,
                    "statusState" => null,
                    "period" => null,
                    "displayClock" => null,
                    "attendance" => null,
                    "homeTeamId" => null,
                    "awayTeamId" => null,
                    "winnerTeamId" => null,
                    "homeTeamScore" => null,
                    "awayTeamScore" => null,
                    "homeTeamLinescores" => null,
                    "awayTeamLinescores" => null,
                    "conferenceCompetition" => null,
                    "headlinesDescription" => null,
                    "headlinesShortLinkText" => null,
                    "headlinesType" => null,
                    "venueId" => null,
                    "venueIndoor" => null,
                    "venueLocation" => null,
                    "venueFullName" => null,
                    "links" => null,
                );

                $dateGameStart = \DateTime::createFromFormat('Y-m-d\TH:i\Z', $game['competitions'][0]['startDate'], new \DateTimeZone('UTC'));
                foreach ($game['competitions'][0]['competitors'] as $team) {
                    if ($team['homeAway'] == "home") {
                        $tmpGame['homeTeamId'] = $team['id'];
                        $tmpGame['homeTeamScore'] = $team['score'];
                        $tmpGame['homeTeamLinescores'] = isset($team['linescores']) ? json_encode($team['linescores']) : json_encode(array());
                    } else {
                        $tmpGame['awayTeamId'] = $team['id'];
                        $tmpGame['awayTeamScore'] = $team['score'];
                        $tmpGame['awayTeamLinescores'] = isset($team['linescores']) ? json_encode($team['linescores']) : json_encode(array());
                    }
                    if (array_get($team, 'winner') == true) {
                        $tmpGame['winnerTeamId'] = $team['id'];
                    }

                    $this->logNflTeamInfo($team);
                }
                $tmpGame['id'] = $game['id'];
                $tmpGame['dateStart'] = $dateGameStart->format("Y-m-d H:i:s");
                $tmpGame['weekday'] = $dateGameStart->setTimezone(new \DateTimeZone('America/Chicago'))
                    ->format("N");
                $tmpGame['statusId'] = $game['status']['type']['id'];
                $tmpGame['statusDetail'] = $game['status']['type']['detail'];
                $tmpGame['statusState'] = $game['status']['type']['state'];
                $tmpGame['period'] = $game['status']['period'];
                $tmpGame['displayClock'] = $game['status']['displayClock'];
                if (isset($game['weather'])) {
                    $tmpGame['weatherConditions'] = $game['weather']['displayValue'];
                    $tmpGame['weatherTemperature'] = array_get($game, 'weather.temperature', array_get($game, 'weather.highTemperature'));
                }
                $tmpGame['links'] = json_encode($game['links']);
                $tmpGame['neutralSite'] = $game['competitions'][0]['neutralSite'];
                $tmpGame['attendance'] = $game['competitions'][0]['attendance'];
                $tmpGame['conferenceCompetition'] = $game['competitions'][0]['conferenceCompetition'];
                if (isset($game['competitions'][0]['headlines'])) {
                    $tmpGame['headlinesDescription'] = $game['competitions'][0]['headlines'][0]['description'];
                    $tmpGame['headlinesShortLinkText'] = $game['competitions'][0]['headlines'][0]['shortLinkText'];
                    $tmpGame['headlinesType'] = $game['competitions'][0]['headlines'][0]['type'];
                }

                $tmpGame['venueId'] = $game['competitions'][0]['venue']['id'];
                $tmpGame['venueIndoor'] = $game['competitions'][0]['venue']['indoor'];
                $tmpGame['venueLocation'] = array_get($game, 'competitions.0.venue.address.city') . ", " . array_get($game, 'competitions.0.venue.address.state');
                $tmpGame['venueFullName'] = $game['competitions'][0]['venue']['fullName'];

                $games[] = $tmpGame;

                $this->logNflScheduleItem($tmpGame);
            }
        }

        $this->logToRedis(__FUNCTION__);
        return $games;
    }

    public function updateNflBroadcastInfo()
    {
        $url = "http://api-app.espn.com/v1/sports/football/nfl/events?advance=true&apikey=9342q2d6jhdwvmnqueveu58q&profile=sportscenter_v1&platform=ios&device=handset&lang=en";

        $r = Curl::to($url)
            ->allowRedirect(true)
            ->withHeader('Cookie: ' . $this->cookie)
            ->asJson(true)
            ->get();

        $arrBroadcastInfo = array_get($r, 'sports.0.leagues.0.events');

        foreach ($arrBroadcastInfo as $event) {
            $e = NflSchedule::firstOrNew(['id' => array_get($event, 'competitions.0.id')])
                ->fill([
                    'broadcastIsNational' => array_get($event, 'competitions.0.broadcasts.0.isNational'),
                    'broadcastNetwork' => array_get($event, 'competitions.0.broadcasts.0.shortName'),
                    'oddsOverUnder' => array_get($event, 'competitions.0.odds.0.overUnder'),
                    'oddsFavoriteId' => array_get($event, 'competitions.0.odds.0.favoriteId'),
                    'oddsSpread' => array_get($event, 'competitions.0.odds.0.spread'),
                    'oddsDetail' => array_get($event, 'competitions.0.odds.0.detail'),
                    'id' => array_get($event, 'competitions.0.id'),
                    'statusDetail' => array_get($event, 'competitions.0.status.detail'),
                    'statusState' => array_get($event, 'competitions.0.status.state'),
                    'period' => array_get($event, 'competitions.0.period'),
                    'displayClock' => array_get($event, 'competitions.0.clock'),
                ])
                ->save();
        }
    }
    public function getLiveNflGames()
    {
        $datePast = new \DateTime(null, new \DateTimeZone('UTC'));
        $datePast->sub(new \DateInterval('PT5H'));

        return NflSchedule::where(function ($query) use ($datePast) {
            $query->where('dateStart', '>=', $datePast)->where('statusState', '=', 'post');
        })->orWhere('statusState', '=', 'in')->get();
    }
    public function getNflScoresForWeek($seasontype = null, $week = null)
    {}

    public function getUpcomingGames($daysInFuture = 1, $daysInPast = 2)
    {
        $dateNow = new \DateTime(null, new \DateTimeZone('UTC'));
        $dateFuture = new \DateTime(null, new \DateTimeZone('UTC'));
        $dateFuture->add(new \DateInterval('P' . $daysInFuture . 'D'));
        $dateNow->sub(new \DateInterval('P' . $daysInPast . 'D'));

        return NflSchedule::where('dateStart', '<=', $dateFuture)->where('dateStart', '>=', $dateNow)->get();
    }

    public function getUpcomingGamesByTeam($teamName)
    {}
    public function logNflScheduleItem($data)
    {
        $n = NflSchedule::firstOrNew([
            'id' => $data['id'],
        ]);

        $n->fill([
            'id' => $data['id'],
            'dateStart' => $data['dateStart'],
            'seasontype' => $data['seasontype'],
            'week' => $data['week'],
            'weekday' => $data['weekday'],
            'venueId' => $data['venueId'],
            'venueIndoor' => $data['venueIndoor'],
            'venueLocation' => $data['venueLocation'],
            'venueFullName' => $data['venueFullName'],
            'weatherConditions' => $data['weatherConditions'],
            'weatherTemperature' => $data['weatherTemperature'],
            'neutralSite' => $data['neutralSite'],
            'statusId' => $data['statusId'],
            'statusDetail' => $data['statusDetail'],
            'statusState' => $data['statusState'],
            'period' => $data['period'],
            'displayClock' => $data['displayClock'],
            'attendance' => $data['attendance'],
            'homeTeamId' => $data['homeTeamId'],
            'homeTeamScore' => $data['homeTeamScore'],
            'homeTeamLinescores' => $data['homeTeamLinescores'],
            'awayTeamId' => $data['awayTeamId'],
            'awayTeamScore' => $data['awayTeamScore'],
            'awayTeamLinescores' => $data['awayTeamLinescores'],
            'winnerTeamId' => $data['winnerTeamId'],
            'conferenceCompetition' => $data['conferenceCompetition'],
            'headlinesDescription' => $data['headlinesDescription'],
            'headlinesShortLinkText' => $data['headlinesShortLinkText'],
            'headlinesType' => $data['headlinesType'],
            'links' => $data['links'],

        ]);

        return $n->save();
    }

    public function logNflTeamInfo($team)
    {
        $t = NflTeam::firstOrNew([
            'proTeamId' => $team['team']['id'],
        ]);

        $t->fill([
            'proTeamId' => $team['team']['id'],
            'logo' => $team['team']['logo'],
            'name' => $team['team']['name'],
            'abbreviation' => $team['team']['abbreviation'],
            'location' => $team['team']['location'],
            'shortDisplayName' => $team['team']['shortDisplayName'],
            'displayName' => $team['team']['displayName'],
            'color' => $team['team']['color'],
            'record' => $team['records'][0]['summary'],
            'wins' => explode("-", $team['records'][0]['summary'])[0],
            'losses' => explode("-", $team['records'][0]['summary'])[1],
            'rankCurrent' => ! isset($team['ranks']) ? null : $team['ranks'][0]['rank']['current'],
            'rankPrevious' => ! isset($team['ranks']) ? null : $team['ranks'][0]['rank']['previous'],
            'rankType' => ! isset($team['ranks']) ? null : $team['ranks'][0]['type'],
            'rankHeadline' => ! isset($team['ranks']) ? null : $team['ranks'][0]['headline'],
        ]);

        return $t->save();
    }

    public function getProTeamsYetToPlay($weekInfo = null)
    {
        if ($weekInfo === null) {
            $weekInfo = $this->getWeek(true);
        }
        $week = $weekInfo['week'];
        $seasontype = $weekInfo['seasontype'];

        return NflSchedule::where('statusState', '!=', 'post')->where('week', '=', $week)->where('seasontype', '=', $seasontype)->get();
    }

    public function getNflGamesPlayed($weekInfo = null)
    {
        if ($weekInfo === null) {
            $weekInfo = $this->getWeek(true);
        }
        $week = $weekInfo['week'];
        $seasontype = $weekInfo['seasontype'];

        return NflSchedule::where('seasontype', '=', $seasontype)->where('week', '=', $week)->where('statusState', '!=', 'pre')->count();
    }

    public function getNflStandings($division = null)
    {}

    /**
     *
     *  ESPN
     *
     */

    public function updateAllLeagues($week = null)
    {
        $r = [];
        foreach (config('espn.leagues.ids') as $leagueId) {
            $r[$leagueId] = $this->updateLeague($leagueId, $week);
        }
        return $r;
    }

    public function updateLeague($leagueId, $week = null)
    {
        $this->results = [];
        \Log::info("Starting updateLeague for $leagueId");
        // Basic league info
        $r['updateLeagueInfo'] = $this->updateLeagueInfo($leagueId);

        // Update basic info about each team in the league
        $r['updateTeamInfo'] = $this->updateTeamInfo($leagueId);

        // Schedule items
        $r['updateScheduleItems'] = $this->updateScheduleItems($leagueId);

        // Update each team's current roster
        $r['updateTeamRosterInfo'] = $this->updateTeamRosterInfo($leagueId);

        // Get transactions and their details
        $r['updateTransactionInfo'] = $this->updateTransactionInfo($leagueId);

        // Update current matchups' scores and info
        $r['updateLeagueScoreboard'] = $this->updateLeagueScoreboard($leagueId, $week);

        // Calculate standings for the league
        $r['updateLeagueStandings'] = $this->updateLeagueStandings($leagueId);

        $results = $this->results;
        return compact('results');
    }

    /**
     * Update the league's basic info, such as name, team IDs, and certain dates
     *
     * @param      string  $leagueId  The league ID
     *
     * @return     array|null  An array of the league's info
     */
    public function updateLeagueInfo($leagueId)
    {
        // Get the full league info
        $url = $this->espnBase . "/ffl/api/v2/leagueInformation?leagueId={$leagueId}&includeTeamRecords=true&fromTeamId=1&rand=" . random_int(11111, 999999);
        $r = Curl::to($url)
            ->allowRedirect(true)
            ->withHeader('Cookie: ' . $this->cookie)
        // ->asJson()
            ->get();

        $r = json_decode(utf8_encode($r));

        // Pull out the team IDs
        $leagueInfo = $r->leagueinformation;
        $teamIds = collect($leagueInfo->leaguesettings->teams)->keys()->implode(',');

        // Insert/update the league record
        $league = League::firstOrNew(['leagueId' => $leagueId]);
        $league->fill([
            'leagueId' => $leagueInfo->leagueenvironment->leagueId,
            'name' => $leagueInfo->leaguesettings->name,
            'currentMatchupPeriodId' => $leagueInfo->leagueenvironment->currentMatchupPeriodId,
            'dateDraft' => $leagueInfo->leaguesettings->dateDraft,
            'dateDraftCompleted' => $leagueInfo->leaguesettings->dateDraftCompleted,
            'finalRegularSeasonMatchupPeriodId' => $leagueInfo->leaguesettings->finalRegularSeasonMatchupPeriodId,
            'tradeDeadline' => $leagueInfo->leaguesettings->tradeDeadline,
            'vetoVotesRequired' => $leagueInfo->leaguesettings->vetoVotesRequired,
            'size' => $leagueInfo->leaguesettings->size,
            'teamIds' => $teamIds,
            'playoffTeamCount' => $leagueInfo->leaguesettings->playoffTeamCount,
            'timePerDraftSelection' => $leagueInfo->leaguesettings->timePerDraftSelection,
            'inviteKey' => $leagueInfo->leaguesettings->inviteKey,
            'finalMatchupPeriodId' => $leagueInfo->leaguesettings->finalMatchupPeriodId,
            'regularSeasonMatchupPeriodCount' => $leagueInfo->leaguesettings->regularSeasonMatchupPeriodCount,
        ]);

        $this->results[__FUNCTION__] = $league->save();
        $this->logToRedis(__FUNCTION__);

        return $league;

    }

    /**
     * Update the league's schedule items (matchups)
     *
     * @param      string  $leagueId  The league ID
     * @param      string  $teamId    (optional) a Team ID, defaults to all teams in the league
     */
    public function updateScheduleItems($leagueId, $teamId = null)
    {
        // Decide if a team ID should be used, defaulting to the full list of team IDs from the database
        if (! $teamId) {
            $teamId = $this->getLeagueTeamNumbers($leagueId);
        }

        // Get the league's schedule info
        $url = $this->espnBase . "/ffl/api/v2/newTeams?leagueId={$leagueId}&teamIds={$teamId}&rand=" . random_int(11111, 999999);

        $r = Curl::to($url)
            ->allowRedirect(true)
            ->withHeader('Cookie: ' . $this->cookie)
        // ->asJson()
            ->get();

        $r = json_decode(utf8_encode($r));
        // echo json_encode($r);
        // exit();
        $results = [];
        $scheduleInfo = collect((array) $r->teams)
            ->pluck('scheduleItems')
            ->map(function ($v, $k) {
                $matchups = [];
                foreach ($v as $teamSchedule) {
                    $teamSchedule->matchups[0]->matchupPeriodId = $teamSchedule->matchupPeriodId;
                    $matchups[] = $teamSchedule->matchups;
                }
                return $matchups;
            })
            ->flatten()
            ->tap(function ($collection) {
                // echo json_encode($collection);
                // die();
            })
            ->each(function ($matchup) use ($leagueId, &$results) {
                if ($matchup->isBye) {
                    $awayTeamId = null;
                } else {
                    $awayTeamId = $matchup->awayTeamId;
                }
                $teamsHash = md5($matchup->homeTeamId . $awayTeamId);

                // Insert/update this matchup
                $item = ScheduleItem::firstOrNew([
                    'leagueId' => $leagueId,
                    'teamsHash' => $teamsHash,
                    'matchupPeriodId' => $matchup->matchupPeriodId,
                ]);
                $item->leagueId = $leagueId;
                $item->teamsHash = $teamsHash;
                $item->matchupTypeId = $matchup->matchupTypeId;
                $item->matchupPeriodId = $matchup->matchupPeriodId;
                $item->isBye = $matchup->isBye;
                $item->homeTeamId = $matchup->homeTeamId;
                $item->homeTeamScores = json_encode($matchup->homeTeamScores);
                $item->homeTeamAdjustment = $matchup->homeTeamAdjustment;
                if (! $matchup->isBye) {
                    $item->awayTeamId = $awayTeamId;
                    $item->awayTeamScores = json_encode($matchup->awayTeamScores);
                    $item->awayTeamAdjustment = $matchup->awayTeamAdjustment;
                }
                $item->outcome = $matchup->outcome;

                $results[$item->primary] = $item->save();
            });

        $this->results[__FUNCTION__] = $results;
        $this->logToRedis(__FUNCTION__);
        return $scheduleInfo;
    }

    public function updateTeamInfo($leagueId, $teamId = null)
    {
        // Decide if a team ID should be used, defaulting to the full list of team IDs from the database
        if (! $teamId) {
            $teamId = $this->getLeagueTeamNumbers($leagueId);
        }

        // Get the league's schedule info
        $url = $this->espnBase . "/ffl/api/v2/newTeams?leagueId={$leagueId}&teamIds={$teamId}&rand=" . random_int(11111, 999999);

        $r = Curl::to($url)
            ->allowRedirect(true)
            ->withHeader('Cookie: ' . $this->cookie)
        // ->asJson()
            ->get();

        $r = json_decode(utf8_encode($r));

        $arrayableFields = ['record', 'teamTransactions', 'division', 'primaryOwner'];
        $results = [];
        $teamsInfo = collect($r->teams)->tap(function ($collection) {
            // dd($collection);
        })->each(function ($teamInfo) use ($leagueId, $arrayableFields, &$results) {

            // For each team, clean up the collection and insert/update their info
            $team = collect($teamInfo);
            $team = $team->put('primaryOwner', $team->get('owners')[0])
                ->map(function ($item, $key) use ($arrayableFields) {
                    if (in_array($key, $arrayableFields)) {
                        return (array) $item;
                    }
                    return $item;
                })->tap(function ($collection) {
                // dd($collection);
            })->toArray();

            $teamId = array_get($team, 'teamId');
            $t = Team::firstOrNew([
                'leagueId' => $leagueId,
                'teamId' => $teamId,
            ]);

            try {
                $t->fill([
                    'leagueId' => $leagueId,
                    'teamId' => $teamId,
                    'overallWins' => array_get($team, 'record.overallWins'),
                    'overallLosses' => array_get($team, 'record.overallLosses'),
                    'overallTies' => array_get($team, 'record.overallTies'),
                    'streakLength' => array_get($team, 'record.streakLength'),
                    'streakType' => array_get($team, 'record.streakType'),
                    'pointsFor' => array_get($team, 'record.pointsFor'),
                    'pointsAgainst' => array_get($team, 'record.pointsAgainst'),
                    'overallAcquisitionTotal' => array_get($team, 'teamTransactions.overallAcquisitionTotal'),
                    'dropsTotal' => array_get($team, 'teamTransactions.drops'),
                    'divisionStanding' => array_get($team, 'divisionStanding'),
                    'overallStanding' => array_get($team, 'overallStanding'),
                    'waiverRank' => array_get($team, 'waiverRank'),
                    'divisionId' => array_get($team, 'division.divisionId'),
                    'teamName' => trim(array_get($team, 'teamLocation')) . " " . trim(array_get($team, 'teamNickname')),
                    'teamLocation' => trim(array_get($team, 'teamLocation')),
                    'teamNickname' => trim(array_get($team, 'teamNickname')),
                    'teamAbbrev' => array_get($team, 'teamAbbrev'),
                    'ownerFirstName' => array_get($team, 'primaryOwner.firstName'),
                    'ownerLastName' => array_get($team, 'primaryOwner.lastName'),
                    'ownerUserName' => array_get($team, 'primaryOwner.userName'),
                    'ownerPhotoUrl' => array_get($team, 'primaryOwner.photoUrl'),
                    'ownerUserProfileId' => array_get($team, 'primaryOwner.userProfileId'),
                    'logoUrl' => array_get($team, 'logoUrl'),
                ]);

                $results[$teamId] = $t->save();

            } catch (\Throwable $e) {
                $results[$teamId] = false;
            }

        });

        $this->results[__FUNCTION__] = $results;
        $this->logToRedis(__FUNCTION__);

        return $teamsInfo;

    }

    public function updateTeamRosterInfo($leagueId, $teamId = null, $scoringPeriodId = null)
    {

        $teamId = ! is_null($teamId) ?: $this->getLeagueTeamNumbers($leagueId);

        $strScoringPeriodId = "";
        if ($scoringPeriodId !== null) {
            $return->setMeta("scoringPeriodId", $scoringPeriodId);
            $strScoringPeriodId = "&scoringPeriodId=$scoringPeriodId";
        }

        $url = "http://games.espn.go.com/ffl/api/v2/rosterInfo?leagueId={$leagueId}&includeProjectionText=true{$strScoringPeriodId}&teamIds={$teamId}&usePreviousSeasonRealStats=true&useCurrentSeasonRealStats=true&useCurrentPeriodRealStats=true&useCurrentPeriodProjectedStats=true&usePreviousPeriodRealStats=false&includeRankings=true&includeLatestNews=true&fromTeamId=1&rand=" . random_int(11111111, 999999999);

        $r = Curl::to($url)
            ->allowRedirect(true)
            ->withHeader('Cookie: ' . $this->cookie)
        // ->asJson()
            ->get();

        $r = json_decode(utf8_encode($r), true);

        $arrRosterInfo = $r['leagueRosters'];

        foreach ($arrRosterInfo['teams'] as $teams) {
            if (($scoringPeriodId != $this->getMatchupPeriodId($leagueId)) && ($scoringPeriodId !== null)) {

                // If it's for another period (e.g. not current roster), log on rostersLog
                $hash = md5(json_encode($teams['slots']));
                $highestPlayerId = 0;
                $highestPlayerScore = -9999;
                $lowestPlayerId = 0;
                $lowestPlayerScore = 9999;
                foreach ($teams['slots'] as $k => $v) {
                    $tmpStats = $v['currentPeriodRealStats']['appliedStatTotal'];
                    if (($tmpStats === null) || ($k >= 9)) {
                        continue;
                    }
                    if ($tmpStats > $highestPlayerScore) {
                        $highestPlayerScore = $tmpStats;
                        $highestPlayerId = $v['player']['playerId'];
                    }
                    if ($tmpStats < $lowestPlayerScore) {
                        $lowestPlayerScore = $tmpStats;
                        $lowestPlayerId = $v['player']['playerId'];
                    }
                }

                RosterLog::firstOrNew([
                    'leagueId' => $leagueId,
                    'teamId' => $teams['teamId'],
                    'hash' => $hash,
                ])
                    ->fill([
                        'roster' => json_encode($teams['slots']),
                        'highestPlayerId' => $highestPlayerId,
                        'highestPlayerScore' => $highestPlayerScore,
                        'lowestPlayerId' => $lowestPlayerId,
                        'lowestPlayerScore' => $lowestPlayerScore,
                    ])
                    ->save();

            } else {

                // Update current roster info in rosters table
                // Delete existing roster info (or now-empty slots will persist)
                Roster::where('teamId', '=', $teams['teamId'])->where('leagueId', '=', $leagueId)->delete();

                foreach ($teams['slots'] as $k => $v) {

                    // Add the roster record for this player
                    Roster::create([
                        'leagueId' => $leagueId,
                        'teamId' => $teams['teamId'],
                        'playerId' => array_get($v, 'player.playerId'),
                        'slotId' => $k,
                        'lockStatus' => array_get($v, 'lockStatus'),
                    ]);

                    if (array_get($v, 'player.playerId')) {

                        $fullName = $v['player']['firstName'] . " " . $v['player']['lastName'];

                        // Update the ESPN player table
                        EspnAllPlayers::where('playerId', '=', $v['player']['playerId'])->update([
                            'currentPeriodProjectedStats' => json_encode($v['currentPeriodProjectedStats']),
                            'currentPeriodRealStats' => json_encode($v['currentPeriodRealStats']),
                            'currentSeasonRealStats' => json_encode($v['currentSeasonRealStats']),
                            'playerId' => $v['player']['playerId'],
                            'fullName' => $fullName,
                        ]);
                    }
                }
            }
        }
        return $r;

    }

    public function updateTransactionInfo($leagueId)
    {
        $recentActivity = $this->getRecentActivity($leagueId, 100);

        foreach ($recentActivity as $k => $v) {

            if (isset($v->transactionLogItemTypeId) && in_array($v->transactionLogItemTypeId, array_keys(config('espn.transactionLogItemTypeId')))) {
                $d = $v;
                $d->leagueId = $leagueId;
                $d->hash = md5($leagueId . $v->dateProposed . $v->proposingTeamId . json_encode($v->teamsInvolved) . json_encode($v->pendingMoveItems));

                // // See if we already have a record of this transaction
                // $existingTransactions = Transaction::where('hash', 'LIKE', $d->hash)->get();

                $t = Transaction::firstOrNew([
                    'hash' => $d->hash,
                ]);

                $t->fill([
                    'hash' => $d->hash,
                    'date' => $v->date,
                    'dateModified' => $v->dateModified,
                    'leagueId' => $d->leagueId,
                    'proposingTeamId' => $v->proposingTeamId,
                    'dateProposed' => $v->dateProposed,
                    'dateAccepted' => $v->dateAccepted,
                    'dateToProcess' => $v->dateToProcess,
                    'statusId' => $v->statusId,
                    'activityType' => $v->activityType,
                    'transactionLogItemTypeId' => $v->transactionLogItemTypeId,
                    'typeId' => $v->typeId,
                    'scoringPeriodToProcess' => $v->scoringPeriodToProcess,
                    'pendingMoveBatchId' => $v->pendingMoveBatchId,
                    'tradeProposalExpirationDays' => $v->tradeProposalExpirationDays,
                    'teamsVotedApproveTrade' => json_encode($v->teamsVotedApproveTrade),
                    'teamsAcceptedTrade' => json_encode($v->teamsAcceptedTrade),
                    'teamsVotedVetoTrade' => json_encode($v->teamsVotedVetoTrade),
                    'teamsInvolved' => json_encode($v->teamsInvolved),
                    'usersProtestTrade' => json_encode($v->usersProtestTrade),
                    'rating' => $v->rating,
                ]);

                $this->results[__FUNCTION__][] = $t->save();

                $this->logTransactionDetails($d);
            }
        }
        $this->logToRedis(__FUNCTION__);
        return $recentActivity;
    }

    public function getRecentActivity($leagueId, $limit = 20)
    {
        // Get the league's schedule info
        $url = $this->espnBase . "/ffl/api/v2/recentActivity?leagueId={$leagueId}&count={$limit}&fromTeamId=1&rand=" . random_int(11111111, 999999999);

        $r = Curl::to($url)
            ->allowRedirect(true)
            ->withHeader('Cookie: ' . $this->cookie)
            ->asJson()
            ->get();

        return collect($r->items);
    }

    public function updateLeagueScoreboard($leagueId, $week = null)
    {

        // Get the league's schedule info
        $url = $this->espnBase . "/ffl/api/v2/scoreboard2?leagueId={$leagueId}&scoringPeriodId=" . ($week ?: $this->getWeek()) . "&includeTopScorer=true&rand=" . random_int(11111111, 999999999);
        \Log::info($url);

        $r = Curl::to($url)
            ->allowRedirect(true)
            ->withHeader('Cookie: ' . $this->cookie)
        // ->asJson(true)
            ->get();

        // If people have emoji in their name, this fixes the encoding
        $r = json_decode(utf8_encode($r), true);

        $leagueScoreboard = $r['scoreboard'];
        foreach ($leagueScoreboard['matchups'] as $matchup) {
            $tmpMatchup = array();
            $tmpMatchup['leagueId'] = $leagueId;
            $tmpMatchup['matchupPeriodId'] = $leagueScoreboard['matchupPeriodId'];
            $tmpMatchup['isBye'] = $matchup['bye'];
            $tmpMatchup['winner'] = $matchup['winner'];

            foreach ($matchup['teams'] as $team) {
                $homeAway = $team['home'] === true ? "home" : "away";
                $tmpMatchup[$homeAway . 'TeamId'] = $team['teamId'];
                $tmpMatchup[$homeAway . 'Score'] = $team['score'];
                $tmpMatchup[$homeAway . 'GamesInProgress'] = $team['gamesInProgress'];
                $tmpTopScorer = isset($team['topScorer']) ? $team['topScorer'] : null;
                $tmpMatchup[$homeAway . 'TopScorerId'] = $tmpTopScorer['player']['playerId'];
                $tmpMatchup[$homeAway . 'TopScorerScore'] = array_get($tmpTopScorer, 'currentPeriodRealStats.appliedStatTotal');
                $tmpMatchup[$homeAway . 'GamesYetToPlay'] = $team['gamesYetToPlay'];
                $tmpMatchup[$homeAway . 'MinutesRemaining'] = $team['minutesRemaining'];
                $tmpMatchup[$homeAway . 'IsFinal'] = $team['isFinal'];
                $tmpMatchup[$homeAway . 'ProjectedPoints'] = $team['projectedPoints'];
            }
            $this->logMatchupInfo($tmpMatchup);

            if (
                (
                    ($tmpMatchup['homeIsFinal'] == 1)
                    && ($tmpMatchup['awayIsFinal'] == 1)
                    && ($tmpMatchup['winner'] != 'undecided')
                )
                || (
                    ($tmpMatchup['homeMinutesRemaining'] == 0)
                    && ($tmpMatchup['awayMinutesRemaining'] == 0)
                    && ($tmpMatchup['homeProjectedPoints'] > 0)
                    && ($tmpMatchup['awayProjectedPoints'] > 0)
                    && ($tmpMatchup['homeScore'] > 0)
                    && ($tmpMatchup['awayScore'] > 0)
                    && ($tmpMatchup['homeGamesYetToPlay'] == 0)
                    && ($tmpMatchup['awayGamesYetToPlay'] == 0)
                    && ($tmpMatchup['homeGamesInProgress'] == 0)
                    && ($tmpMatchup['awayGamesInProgress'] == 0)
                    && ($tmpMatchup['isBye'] == false)
                )
            ) {
                \Log::info("End of matchup for " . $tmpMatchup['homeTeamId'] . " vs " . $tmpMatchup['awayTeamId']);
                $this->sendEndOfMatchupNotification($leagueId, "matchupEnd", $tmpMatchup);
            }
        }

        $this->logToRedis(__FUNCTION__);
        return $leagueScoreboard['matchups'];
    }

    public function getMatchupPeriodId($leagueId, $getPrior = false)
    {
        $this->updateLeagueInfo($leagueId);
        $matchup = League::where('leagueId', '=', $leagueId)->pluck('currentMatchupPeriodId')->first();
        if ($getPrior) {
            return max($matchup - 1, 1);
        }
        return $matchup;
    }

    public function getMatchup($leagueId, $teamId, $matchupPeriodId = null, $boolIncludeImage = true)
    {
        if ($matchupPeriodId === null) {
            $matchupPeriodId = $this->getMatchupPeriodId($leagueId);
        }
        \Log::info("MatchupPeriodId: $matchupPeriodId");
        \Log::info("teamId: $teamId");
        $matchup = Matchup::where('leagueId', '=', $leagueId)
            ->where('matchupPeriodId', '=', $matchupPeriodId)
            ->where(function ($query) use ($teamId) {
                $query->where('homeTeamId', '=', $teamId)
                    ->orWhere('awayTeamId', '=', $teamId);
            })
            ->with(['awayTeam' => function ($query) use ($leagueId) {
                $query->where('leagueId', '=', $leagueId);
            }])
            ->with(['homeTeam' => function ($query) use ($leagueId) {
                $query->where('leagueId', '=', $leagueId);
            }])
            ->first();

        \Log::info("matchup:");
        \Log::info($matchup);

        if ($boolIncludeImage === true) {
            $matchup['imagePath'] = $this->createMatchupImage($matchup);
            $matchup['imageUrl'] = $matchup['imagePath'] ? "https://trybot2000.com/img/ff/" . $matchup['imagePath'] : null;
        }

        return $matchup;

    }

    public function getMatchups($leagueId, $matchupPeriodId = null)
    {
        if ($matchupPeriodId === null) {
            $matchupPeriodId = $this->getMatchupPeriodId($leagueId);
        }

        $matchups = Matchup::where('leagueId', '=', $leagueId)
            ->where('matchupPeriodId', '=', $matchupPeriodId)
            ->with(['awayTeam' => function ($query) use ($leagueId) {
                $query->where('leagueId', '=', $leagueId);
            }])
            ->with(['homeTeam' => function ($query) use ($leagueId) {
                $query->where('leagueId', '=', $leagueId);
            }])
            ->get();

        return $matchups;

    }

    public function updateSinglePlayerStats($leagueId, $playerId)
    {
        $url = "http://games.espn.go.com/ffl/api/v2/playerInfo?leagueId={$leagueId}&playerId={$playerId}&useCurrentSeasonRealStats=true&useCurrentSeasonProjectedStats=true&usePreviousSeasonRealStats=false&useCurrentPeriodRealStats=true&useCurrentPeriodProjectedStats=true&usePreviousPeriodRealStats=true&useGameLog=true&includeProjectionText=true&include=news|projections|playerInfos&rand=9022398354299";
        $r = Curl::to($url)
            ->allowRedirect(true)
            ->withHeader('Cookie: ' . $this->cookie)
            ->asJson(true)
            ->get();

        $arrPlayerInfo = $r['playerInfo']['players'][0];
        // return $arrPlayerInfo;
        if (! empty($arrPlayerInfo)) {
            $this->insertPlayerInfo($arrPlayerInfo);
        }
        return $arrPlayerInfo;
    }

    public function getPlayerStats($playerName, $leagueId = "111799", $boolUpdateStats = false)
    {

        if (stripos(trim($playerName), "the") === 0) {
            $playerName = trim(str_ireplace("the ", "", $playerName)) . " D/ST";
        }
        if ((stripos($playerName, "dst") !== false) || (stripos($playerName, "defense") !== false) || (stripos($playerName, "d/st") !== false)) {
            $name = explode(" ", $playerName);
            $firstName = array_shift($name);
            $lastName = "D/ST";
            $fullName = $firstName . " " . $lastName;
        } elseif (count(explode(" ", $playerName)) > 1) {
            $name = explode(" ", $playerName);
            $fullName = trim($playerName);
            $firstName = array_shift($name);
            $lastName = implode($name);
        } else {
            $fullName = " " . $playerName;
            $firstName = $playerName;
            $lastName = $playerName;
        }

        // echo "fullName: $fullName <br />";
        // echo "firstName: $firstName <br />";
        // echo "lastName: $lastName <br />";
        $jaroMinScore = .65;

        // $rosters      = Roster::where('leagueId', '=', $leagueId)
        //     ->with('player')
        //     ->with(['team' => function ($query) use ($leagueId) {
        //         $query->where('leagueId', '=', $leagueId);
        //     }])
        //     ->get();

        $players = EspnAllPlayers::select('firstName', 'lastName', 'fullName', 'playerId', 'currentPeriodRealStats', 'currentPeriodProjectedStats', 'position', 'team', 'totalPoints', 'healthStatus', 'percentOwned')
        // ->with(['roster' => function ($query) use ($leagueId) {
        //     $query->where('leagueId', '=', $leagueId);
        // }])
        // ->with('rosterWithTeam')
            ->get();
        // ->toSql();
        // return $players;
        // return($players->take(100)->toArray());

        $players = $players->filter(function ($value, $key) use ($fullName, $lastName, $firstName, $jaroMinScore) {
            try {
                $scoreFull = \FuzzyMatch::jaroWinkler(strtolower($fullName), strtolower($value->fullName));
                $scoreLast = \FuzzyMatch::jaroWinkler(strtolower($lastName), strtolower($value->lastName));

                $value->matchScore = max([$scoreFull, $scoreLast]);
                $value->avgMatchScore = ($scoreFull + $scoreLast) / 2;

                $value->sortScore = ($value->matchScore * 2) + $value->avgMatchScore + ($value->percentOwned / 200);
            } catch (\Throwable $e) {

                $t = explode("\n", $e->getTraceAsString());
                dd([
                    'message' => $e->getMessage(),
                    'code' => $e->getCode(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $t,
                    'value' => $value,
                ]);
            }

            return ($scoreFull > $jaroMinScore) || ($scoreLast > $jaroMinScore);
        })
        // ->pluck('player')
            ->sortByDesc('sortScore')
            ->tap(function ($collection) {
                // dd($collection->toArray());
            })
            ->first();
        // return $players->toArray();

        if (! $players) {
            return "Sorry, I'm not sure which player you're asking about.";
        }

        // Update this players' stats
        $this->updateSinglePlayerStats($leagueId, $players->playerId);

        $playerStats = EspnAllPlayers::where('playerId', '=', $players->playerId)->get()->map(function (&$item, $key) {
            // If this player is on a team, get that team's info
            if (isset($item->roster)) {
                $item->teamId = $item->roster->teamId;
                $item->slotId = $item->roster->slotId;
                if (isset($item->roster->team)) {
                    $item->teamName = $item->roster->team->teamName;
                }
            }
            return $item;
        })->first();
        $arrStats = [];

        // return $playerStats;

        $pronouns = ["single" => ["he" => "he", "past" => "scored", "present" => "has scored", "future" => "is projected to get", "has not" => "hasn't", "has" => "has", "is" => "is"], "plural" => ["he" => "they", "past" => "scored", "present" => "have scored", "future" => "are projected to get", "has not" => "haven't", "has" => "have", "is" => "are"]];
        $br = "\n";
        if (empty($playerStats)) {
            $strResponse = "Sorry, I'm not sure which player you're asking about.";
        } else {
            $pronoun = "single";
            $tense = "future";
            if ($playerStats['position'] == 16) {
                $pronoun = "plural";
            }
            $arrFullStats = json_decode($playerStats['currentPeriodRealStats'], true);
            $arrFullStatsProjected = json_decode($playerStats['currentPeriodProjectedStats'], true);

            if (array_get($playerStats, 'roster.team.teamId') == null) {
                $strResponse = $playerStats['fullName'] . ($playerStats['position'] != 16 ? " (" . config('espn.defaultPositionId')[$playerStats['position']] . ($playerStats['team'] ? ", " . $playerStats['team'] : "") . ")" : "") . " " . $pronouns[$pronoun]["is"] . " a Free Agent. ";
            } else {
                $intSeasonTotal = (isset($arrFullStats['appliedStatTotal']) ? floor($arrFullStats['appliedStatTotal']) + $playerStats['totalPoints'] : $playerStats['totalPoints']);
                $strResponse = $playerStats['fullName'] . ($playerStats['position'] != 16 ? " (" . config('espn.defaultPositionId')[$playerStats['position']] . ($playerStats['team'] ? ", " . $playerStats['team'] : "") . ")" : "") . " " . $pronouns[$pronoun]["is"] . " on the " . ($playerStats['slotId'] < 9 ? "active roster" : "bench") . ($playerStats['healthStatus'] != 0 ? " (" . config('espn.healthStatus')[$playerStats['healthStatus']] . ")" : "") . " for *" . $playerStats['teamName'] . "*. So far this season, " . $pronouns[$pronoun]["he"] . " " . $pronouns[$pronoun]["has"] . " scored *" . $intSeasonTotal . " " . (abs($intSeasonTotal) == 1 ? "point" : "points") . "* total. ";
            }

            if ($playerStats['team'] == null) {
                $strResponse .= ucfirst($pronouns[$pronoun]["he"]) . " " . $pronouns[$pronoun]["is"] . " not signed to an NFL team.";
            } else if (! empty($arrFullStats)) {

                // They have some live stats, so let's see if they're still in a game
                $playerInfo = $this->getPlayerInfo($playerStats['playerId'], $leagueId);

                if (($playerInfo['statusId'] == 2) || ($playerInfo['statusState'] == "in")) {

                    // Game is live
                    // "He scored 12 points and is projected to get 21 (11:28, 2nd period)"
                    $strResponse .= $br . $br . "This week, " . $pronouns[$pronoun]["he"] . " " . $pronouns[$pronoun]["present"] . " " . floor($arrFullStats['appliedStatTotal']) . " " . (abs(floor($arrFullStats['appliedStatTotal'])) == 1 ? "point" : "points") . " and " . $pronouns[$pronoun]["future"] . " " . floor($arrFullStatsProjected['appliedStatTotal']) . " (" . $playerInfo['statusDetail'] . ")";
                } else {

                    // Game is complete
                    $strResponse .= $br . $br . "This week, " . $pronouns[$pronoun]["he"] . " " . $pronouns[$pronoun]["past"] . " *" . floor($arrFullStats['appliedStatTotal']) . " " . (abs(floor($arrFullStats['appliedStatTotal'])) == 1 ? "point" : "points") . "*. ";
                }

                $strResponse .= $br . $br;
                switch ($playerStats['position']) {
                    case 1:

                        // QB
                        $arrStats["PASS"] = array_get($arrFullStats, 'rawStats.1', 0) . " of " . array_get($arrFullStats, 'rawStats.0', 0) . " (" . round((array_get($arrFullStats, 'rawStats.1', 0) / array_get($arrFullStats, 'rawStats.0', 0)) * 100, 1) . "%) for " . array_get($arrFullStats, 'rawStats.3', 0) . (abs(array_get($arrFullStats, 'rawStats.3', 0)) == 1 ? " yd" : " yds");
                        $arrStats["RUSH"] = array_get($arrFullStats, 'rawStats.23', 0) . " att. for " . array_get($arrFullStats, 'rawStats.24', 0) . (abs(array_get($arrFullStats, 'rawStats.24', 0)) == 1 ? " yd" : " yds");

                        // $arrStats["YDS"] = array_get($arrFullStats,'rawStats.3',0);
                        $arrStats["TD"] = array_get($arrFullStats, 'rawStats.4', 0) + array_get($arrFullStats, 'rawStats.25', 0) + array_get($arrFullStats, 'rawStats.43', 0);
                        if (array_get($arrFullStats, 'rawStats.20', 0)) {
                            $arrStats["INT"] = array_get($arrFullStats, 'rawStats.20', 0);
                        }
                        if (array_get($arrFullStats, 'rawStats.72', 0)) {
                            $arrStats["FUML"] = array_get($arrFullStats, 'rawStats.72', 0);
                        }

                        $arrStats["QBR"] = $this->calculateQBR($arrFullStats);
                        break;

                    case 2:
                    case 3:
                    case 4:
                        // RB/WR/TE

                        // Yards (total)
                        $arrStats["YDS"] = array_get($arrFullStats, 'rawStats.3', 0) + array_get($arrFullStats, 'rawStats.24', 0) + array_get($arrFullStats, 'rawStats.42', 0);
                        if ((array_get($arrFullStats, 'rawStats.24', 0) && array_get($arrFullStats, 'rawStats.42', 0)) && ((array_get($arrFullStats, 'rawStats.24', 0) >= 10) && (array_get($arrFullStats, 'rawStats.42', 0) >= 10))) {
                            $arrStats["RSHYDS"] = array_get($arrFullStats, 'rawStats.24', 0);
                            $arrStats["RECYDS"] = array_get($arrFullStats, 'rawStats.42', 0);
                        }

                        // Receptions (only shown in PPR leagues)
                        if (array_get($arrFullStats, 'appliedStats.53')) {
                            $arrStats["REC"] = $arrFullStats['appliedStats'][53];
                        }

                        // Touchdowns
                        $numTDs = array_get($arrFullStats, 'rawStats.4', 0) + array_get($arrFullStats, 'rawStats.25', 0) + array_get($arrFullStats, 'rawStats.43', 0);
                        if ($numTDs > 0) {
                            $arrStats["TD"] = array_get($arrFullStats, 'rawStats.4', 0) + array_get($arrFullStats, 'rawStats.25', 0) + array_get($arrFullStats, 'rawStats.43', 0);
                        }

                        // Fumbles
                        if (array_get($arrFullStats, 'rawStats.72', 0)) {
                            $arrStats["FUML"] = array_get($arrFullStats, 'rawStats.72', 0);
                        }

                        break;

                    case 5:

                        // K
                        if (array_get($arrFullStats, 'rawStats.84', 0)) {
                            $arrStats["FG"] = (array_get($arrFullStats, 'rawStats.83', 0) ?: 0) . "/" . array_get($arrFullStats, 'rawStats.84', 0) . " (" . round(100 * array_get($arrFullStats, 'rawStats.83', 0) / array_get($arrFullStats, 'rawStats.84', 0)) . "%)";
                        }
                        if (array_get($arrFullStats, 'rawStats.87', 0)) {
                            $arrStats["PAT"] = (array_get($arrFullStats, 'rawStats.86', 0) ?: 0) . "/" . array_get($arrFullStats, 'rawStats.87', 0) . " (" . round(100 * array_get($arrFullStats, 'rawStats.86', 0) / array_get($arrFullStats, 'rawStats.87', 0)) . "%)";
                        }

                        if (array_get($arrFullStats, 'rawStats.74', 0)) {
                            $arrStats["extra"] = "Fun stat: he made " . array_get($arrFullStats, 'rawStats.74', 0) . " of his Field Goals from 50+ yards";
                        }

                        break;

                    case 16:

                        // D/ST
                        if (array_get($arrFullStats, 'rawStats.120', 0)) {
                            $arrStats['Yards allowed'] = array_get($arrFullStats, 'rawStats.120', 0);
                        }
                        if (array_get($arrFullStats, 'rawStats.127', 0)) {
                            $arrStats['Yards allowed'] = array_get($arrFullStats, 'rawStats.127', 0);
                        }
                        if (array_get($arrFullStats, 'rawStats.97', 0)) {
                            $arrStats['Block'] = array_get($arrFullStats, 'rawStats.97', 0);
                        }
                        if (array_get($arrFullStats, 'rawStats.99', 0)) {
                            $arrStats['Sack'] = array_get($arrFullStats, 'rawStats.99', 0);
                        }
                        if (array_get($arrFullStats, 'rawStats.93', 0)) {
                            $arrStats['Block for TD'] = array_get($arrFullStats, 'rawStats.93', 0);
                        }
                        if (array_get($arrFullStats, 'rawStats.95', 0)) {
                            $arrStats['INT'] = array_get($arrFullStats, 'rawStats.95', 0);
                        }
                        if (array_get($arrFullStats, 'rawStats.103', 0)) {
                            $arrStats['Pick six'] = array_get($arrFullStats, 'rawStats.103', 0);
                        }
                        if (array_get($arrFullStats, 'rawStats.96', 0)) {
                            $arrStats['Fumble recovered'] = array_get($arrFullStats, 'rawStats.96', 0);
                        }
                        if (array_get($arrFullStats, 'rawStats.98', 0)) {
                            $arrStats['Safety'] = array_get($arrFullStats, 'rawStats.98', 0);
                        }
                        $otherTdTotal = array_get($arrFullStats, 'rawStats.104', 0) + array_get($arrFullStats, 'rawStats.101', 0) + array_get($arrFullStats, 'rawStats.102', 0);
                        if ($otherTdTotal > 0) {
                            $arrStats['Other TD'] = $otherTdTotal;
                        }
                        break;

                    default:

                        break;
                }
                foreach ($arrStats as $k => $v) {
                    if ($k == 'extra') {
                        $strResponse .= $v . $br;
                        continue;
                    }
                    $strResponse .= $k . ": " . $v . $br;
                }
            } else {
                $strResponse .= ucfirst($pronouns[$pronoun]["he"]) . " " . $pronouns[$pronoun]['has not'] . " played yet, but " . $pronouns[$pronoun]["future"] . " " . floor($arrFullStatsProjected['appliedStatTotal']) . " " . (abs(floor($arrFullStatsProjected['appliedStatTotal'])) == 1 ? "point" : "points") . " this week.";
            }
        }

        $m = new Message();
        $m->messageVisibleToChannel();
        $m->setText($strResponse);

        return $strResponse;
    }

    public function getPlayerInfo($playerId)
    {
        $week = $this->getWeek();
        $player = EspnAllPlayers::where('playerId', '=', $playerId)->with('nflTeam')->first();
        return $player;
        // $PDOSelect = $this->db->prepare('SELECT S.statusId, S.statusDetail,S.statusState,E.* FROM `espnAllPlayers` as E
        // LEFT JOIN nflSchedule as S
        // ON S.homeTeamId = E.proTeamId OR S.awayTeamId = E.proTeamId
        // WHERE E.`playerId` LIKE :playerId AND S.week = :week');
        // $PDOSelect->bindParam(':playerId', $playerId);
        // $PDOSelect->bindParam(':week', $week);
        // $PDOSelect->execute();
        // return $PDOSelect->fetchAll(\PDO::FETCH_ASSOC)[0];
    }

    public function updateSalaryInfo()
    {}
    public function updatePlayerInfo($leagueId, $limit = 100)
    {
        $sTime = microtime(true);
        $totalCurlSize = 0;

        $proTeamIds = config('espn.proTeamIds');
        $arrPlayerProTeamChanges = [];

        // Get all players and send to database

        for ($intOffset = 0; $intOffset < 100; $intOffset++) {
            $url = "http://games.espn.go.com/ffl/api/v2/playerInfo?leagueId={$leagueId}&fromTeamId=1&availabilityFilter=-1&slotCategoryFilter=-1&s1category=playerinfo&s1column=percentOwned&s1direction=descending&s2category=playerinfo&s2column=percentOwned&s2direction=descending&useCurrentSeasonRealStats=true&usePreviousSeasonRealStats=true&offset={$intOffset}&limit={$limit}&useCurrentPeriodRealStats=true&useCurrentPeriodProjectedStats=true&usePreviousPeriodRealStats=false&includeProjectionText=true&includeRankings=true&includeLatestNews=true&top3=false&rand=902239874713";

            $r = Curl::to($url)
                ->allowRedirect(true)
                ->withHeader('Cookie: ' . $this->cookie)
                ->asJson(true)
                ->get();

            $allPlayers = $r['playerInfo'];
            // dd($allPlayers);

            if (count($allPlayers['players']) == 0) {
                break;
            }

            $currentHealthStatus = $this->getCurrentHealthStatus();
            $currentProTeam = $this->getCurrentProTeam();
            $currentRosterStatus = $this->getCurrentRosterStatus();

            foreach ($allPlayers['players'] as $k => $v) {

                // Check for a change in health status from the last update
                if (isset($currentHealthStatus[$v['player']['playerId']]) && $currentHealthStatus[$v['player']['playerId']] != $v['player']['healthStatus']) {
                    $arrPlayerStatusChanges[] = array(
                        "playerId" => $v['player']['playerId'],
                        "currentStatus" => $v['player']['healthStatus'],
                        "priorStatus" => $currentHealthStatus[$v['player']['playerId']],
                        "isBetter" => $currentHealthStatus[$v['player']['playerId']] > $v['player']['healthStatus'],
                    );
                    $this->logPlayerStatusChange($v['player']['playerId'], $v['player']['healthStatus'], $currentHealthStatus[$v['player']['playerId']]);
                }

                // Check for a change in roster status from the last update (by looking at proTeamId)
                if (isset($currentProTeam[$v['player']['playerId']]) && $currentProTeam[$v['player']['playerId']] != $v['player']['proTeamId']) {

                    $tmpMoveType = null;
                    if ($v['player']['proTeamId'] == null) {
                        $tmpMoveType = "2";
                    } else if ($currentProTeam[$v['player']['playerId']] == null) {
                        $tmpMoveType = "1";
                    } else {
                        $tmpMoveType = "3";
                    }

                    $tmp = array(
                        "playerId" => $v['player']['playerId'],
                        "currentProTeamId" => $v['player']['proTeamId'],
                        "priorProTeamId" => $currentProTeam[$v['player']['playerId']],
                        "priorRosterStatus" => $currentRosterStatus[$v['player']['playerId']],
                        "currentRosterStatus" => $v['rosterStatus'],
                        "proTeamMoveType" => $tmpMoveType,
                    );
                    $arrPlayerProTeamChanges[] = $tmp;
                    $this->logPlayerProTeamChange($tmp);
                }
                if (EspnAllPlayersLog::where('hash', '=', md5(json_encode($v)))->count() == 0) {

                    EspnAllPlayersLog::firstOrCreate([
                        'hash' => md5(json_encode($v)),
                        'playerId' => $v['player']['playerId'],
                        'proTeamId' => $v['player']['proTeamId'],
                        'percentOwned' => $v['player']['percentOwned'],
                        'percentStarted' => $v['player']['percentStarted'],
                        'latestNewsTenWords' => array_get($v, 'player.latestNews.tenWords'),
                        'totalPoints' => array_get($v, 'player.totalPoints'),
                        'currentPeriodProjectedPoints' => $v['currentPeriodProjectedStats']['appliedStatTotal'],
                        'currentPeriodRealPoints' => array_get($v, 'currentPeriodRealStats.appliedStatTotal'),
                        'positionRank' => array_get($v, 'player.positionRank'),
                        'position' => array_get($v, 'player.defaultPositionId'),
                        'eligibleSlotCategoryIds' => json_encode(array_get($v, 'player.eligibleSlotCategoryIds')),
                        'rosterStatus' => array_get($v, 'rosterStatus'),
                        'healthStatus' => array_get($v, 'player.healthStatus'),
                        'defaultPositionId' => array_get($v, 'player.defaultPositionId'),
                        'pvoRank' => array_get($v, 'pvoRank'),
                        'droppable' => array_get($v, 'player.droppable'),
                    ]);
                }

                // Update player in EspnAllPlayers table
                $this->insertPlayerInfo($v);
            }
        }

        return $arrPlayerProTeamChanges;
    }

    public function insertPlayerInfo($data)
    {
        $proTeamIds = config('espn.proTeamIds');

        $player = EspnAllPlayers::firstOrNew(['playerId' => $data['player']['playerId']]);
        $player->fill([
            'playerId' => array_get($data, 'player.playerId'),
            'firstName' => array_get($data, 'player.firstName'),
            'lastName' => array_get($data, 'player.lastName'),
            'proTeamId' => array_get($data, 'player.proTeamId'),
            'team' => array_get($proTeamIds, array_get($data, 'player.proTeamId')),
            'percentOwned' => array_get($data, 'player.percentOwned'),
            'percentStarted' => array_get($data, 'player.percentStarted'),
            'latestNewsTenWords' => array_get($data, 'player.latestNews.tenWords'),
            'latestNewsEvaluation' => array_get($data, 'player.latestNews.evaluation'),
            'totalPoints' => array_get($data, 'player.totalPoints'),
            'positionRank' => array_get($data, 'player.positionRank'),
            'position' => array_get($data, 'player.defaultPositionId'),
            'rosterStatus' => array_get($data, 'rosterStatus'),
            'eligibleSlotCategoryIds' => json_encode(array_get($data, 'player.eligibleSlotCategoryIds')),
            'currentPeriodProjectedStats' => json_encode(array_get($data, 'currentPeriodProjectedStats')),
            'proGameIds' => json_encode(array_get($data, 'proGameIds')),
            'previousSeasonRealStats' => json_encode(array_get($data, 'previousSeasonRealStats')),
            'currentSeasonRealStats' => json_encode(array_get($data, 'currentSeasonRealStats')),
            'currentPeriodRealStats' => json_encode(array_get($data, 'currentPeriodRealStats')),
            'healthStatus' => array_get($data, 'player.healthStatus'),
            'defaultPositionId' => array_get($data, 'player.defaultPositionId'),
            'universeId' => array_get($data, 'player.universeId'),
            'opponentProTeamId' => array_get($data, 'opponentProTeamId'),
            'pvoRank' => array_get($data, 'pvoRank'),
            'droppable' => array_get($data, 'player.droppable'),
        ])->save();
    }
    public function getLeagueSize($leagueId)
    {}

    public function getCurrentHealthStatus()
    {
        $r = EspnAllPlayers::all();
        foreach ($r as $k => $v) {
            $p[$v['playerId']] = $v['healthStatus'];
        }
        return $p;
    }

    public function getCurrentProTeam()
    {
        $r = EspnAllPlayers::all();

        foreach ($r as $k => $v) {
            $p[$v['playerId']] = $v['proTeamId'];
        }
        return $p;
    }

    public function getCurrentRosterStatus()
    {
        $r = EspnAllPlayers::all();

        foreach ($r as $k => $v) {
            $p[$v['playerId']] = $v['rosterStatus'];
        }
        return $p;
    }

    public function processPlayerStatusChangeNotifications($leagueId = null)
    {
        if (is_null($leagueId)) {
            $leagueIds = config('espn.regularUpdates');
        } else {
            $leagueIds = array_wrap($leagueId);
        }

        $dateOneDayAgo = (new \DateTime())->sub(new \DateInterval('P1D'));

        $r = PlayerStatusChange::where('IsProcessed', '=', '0')->where('timestamp', '>', $dateOneDayAgo)
            ->with('player')
            ->with(['roster' => function ($query) use ($leagueId) {
                $query->where('leagueId', '=', $leagueId);
            }])
            ->get();

        return $r;

        $teamsYetToPlay = $this->getProTeamsYetToPlay();

        foreach ($r as $k => $v) {
            if (is_null($v->roster)) {
                continue;
            }
            $tone = "negative";
            $oldStatus = config('espn.healthStatus')[$v['priorStatus']];
            $newStatus = config('espn.healthStatus')[$v['currentStatus']];

            // Exclude
            // Players only going between OUT and INACTIVE
            if ((($v['priorStatus'] == 11) && ($v['currentStatus'] == 4)) || (($v['priorStatus'] == 4) && ($v['currentStatus'] == 11))) {

                // Skip out<-->inactive
                continue;
            }

            // Exclude
            // Prior status was ACTIVE
            // Current status is QUESTIONABLE
            if (($v['priorStatus'] == 0) && ($v['currentStatus'] == 2)) {

                // Skip active-->questionable
                continue;
            }

            // Exclude
            // Prior status was OUT or INACTIVE, or
            // Current status is PROBABLE or ACTIVE
            //      AND
            // This NFL team has already played this week
            if ((($v['priorStatus'] == 4) || ($v['priorStatus'] == 11)) || ($v['currentStatus'] <= 1)) {

                // See if their team hasn't played yet this week, otherwise skip them
                if (in_array($v['proTeamId'], $teamsYetToPlay->toArray()) === false) {
                    continue;
                }
            }

            // Exclude
            // A player on a fantasy team's bench (inactive)
            //      AND
            // His condition has gotten worse
            //      AND
            // The new condition is not OUT, IR, or SSPD
            if (($v['slotId'] >= 9) && ($v['IsBetter'] != 1) && (in_array($v['currentStatus'], ['4', '5', '13']) === false)) {
                continue;
            }

            if ($v['IsBetter'] == 1) {
                if (($v['priorStatus'] == 11) && ($v['currentStatus'] < 4)) {
                    $tone = "positive";
                } else {
                    $tone = "neutral";
                }
            } else if ($v['currentStatus'] < 3) {
                $tone = "neutral";
            } else if ($v['currentStatus'] >= 4) {
                $tone = "veryNegative";
            }
            dd($v);
            $strMessage = config('espn.phrases')[$tone][array_rand(config('espn.phrases')[$tone])] . " " . $v['player']['fullName'] . " (" . $v['player']['team'] . ") has gone from " . $oldStatus . " to " . $newStatus . ($v['latestNewsTenWords'] != null ? '. According to ESPN, "' . $v['latestNewsTenWords'] . '"' : ".") . " He's " . ($v['slotId'] < 9 ? "active" : "inactive") . " for " . $v->roster->team->teamName;

            $slack = new Slack;
            $channelId = $this->getSlackChannelFromLeague($v->roster, true);
            $m = new Message();
            $m->messageVisibleToChannel();
            $m->setText($strMessage);
            $slack->postMessage($m, $channelId);

            // Update as posted
            PlayerStatusChange::find($v->primary)->update(['IsProcessed' => '1']);
        }
    }

    public function processPlayerProTeamIdNotifications($leagueId = null)
    {}
    public function processTransactionNotifications($leagueId = null)
    {
        $dateUtc = new \DateTime(null, new \DateTimeZone("UTC"));
        $dateLastFiveMin = (clone $dateUtc)->sub(new \DateInterval('PT5M'));
        $dateLastFiveMin = (clone $dateUtc)->sub(new \DateInterval('P5D'));

        if (is_null($leagueId)) {
            $leagueIds = config('espn.regularUpdates');
        } else {
            $leagueIds = array_wrap($leagueId);
        }

        $positions = config('espn.defaultPositionId');

        $trades = Transaction::whereIn('leagueId', $leagueIds)->where('dateToProcess', '>', $dateUtc)->whereNull('sentInitialNotification')->whereIn('transactionLogItemTypeId', [4, 11, 15])->get();
        $waivers = Transaction::whereIn('leagueId', $leagueIds)->where('date', '>', $dateLastFiveMin)->whereNull('sentInitialNotification')->whereIn('transactionLogItemTypeId', [1])->get();

        // return compact('trades','waivers');

        $slack = new Slack;
        // trades
        foreach ($trades as $k => $transaction) {

            $channelId = $this->getSlackChannelFromLeague($transaction['leagueId']);

            $teams = $this->getTeams($transaction['leagueId']);

            // Get all transaction details
            // dd($transaction);

            $transactionDetails = $this->getTransactionDetails($transaction['hash']);

            $proposingTeamName = $teams[$transaction['proposingTeamId']]['teamLocation'] . " " . $teams[$transaction['proposingTeamId']]['teamNickname'];
            foreach (json_decode($transaction['teamsInvolved'], true) as $v) {
                if ($v != $transaction['proposingTeamId']) {
                    $receivingTeamId = $v;
                    break;
                }
            }

            $proposingTeamId = $transaction['proposingTeamId'];
            $receivingTeamName = $teams[$receivingTeamId]['teamLocation'] . " " . $teams[$receivingTeamId]['teamNickname'];
            $giving = array();
            $getting = array();
            $dropping = array();
            foreach ($transactionDetails as $k => $v) {
                if ($v['toTeamId'] == -1) {
                    $dropping[$v['fromTeamId']][] = $v->player->fullName . " (" . $positions[$v->player->position] . ", " . $v->player->team . ")";
                    continue;
                } else if ($v['fromTeamId'] == $transaction['proposingTeamId']) {
                    $giving[] = $v->player->fullName . " (" . $positions[$v->player->position] . ", " . $v->player->team . ")";
                    continue;
                }
                $getting[] = $v->player->fullName . " (" . $positions[$v->player->position] . ", " . $v->player->team . ")";
            }

            $strMessage = "*" . $proposingTeamName . "* wants to trade " . Helper::implodeNice($giving) . " to *" . $receivingTeamName . "* in exchange for " . Helper::implodeNice($getting) . ". " . (! empty($dropping[$proposingTeamId]) ? $proposingTeamName . " is also dropping " . Helper::implodeNice($dropping[$proposingTeamId]) : "") . (! empty($dropping[$receivingTeamId]) ? (! empty($dropping[$proposingTeamId]) ? ", and " : "") . $receivingTeamName . " is also dropping " . Helper::implodeNice($dropping[$receivingTeamId]) : "") . ((! empty($dropping[$proposingTeamId]) || ! empty($dropping[$receivingTeamId])) ? ". " : "") . "League members can review this trade (and approve or veto) here: http://games.espn.go.com/ffl/pendingtrades?leagueId=" . $transaction['leagueId'];
            echo $strMessage;
            echo "<br />";
            echo "<br />";

            // echo json_encode($attachmentArray);

            echo "Sending to Slack--->";

            $m = new Message();
            $m->messageVisibleToChannel();
            $m->setText($strMessage);

            $slack->postMessage($m, $channelId);

            // $resultGroupMePost = $this->sendToGroupMe(array(
            //     "message"  => $strMessage,
            //     "leagueId" => $transaction['leagueId'],
            // ), $attachmentArray);
            // echo (int) $resultGroupMePost;
            echo "<br />";
            echo "<br />";
            // sleep(1);
            // $PDOUpdate = $this->db->prepare('UPDATE `transactions` SET `sentInitialNotification` = :sentInitialNotification WHERE `primary` = :primary');
            // $PDOUpdate->bindParam(':sentInitialNotification', $dateUtc->format('Y-m-d H:i:s'));
            // $PDOUpdate->bindParam(':primary', $transaction['primary']);
            // $PDOUpdate->execute();
            Transaction::find($transaction->primary)->update(['sentInitialNotification' => $dateUtc]);
        }

        // return false;

        // echo json_encode($waivers);

        //waivers
        foreach ($waivers as $k => $transaction) {
            $teams = $this->getTeams($transaction['leagueId']);
            $channelId = $this->getSlackChannelFromLeague($transaction['leagueId']);

            // Get all transaction details

            $transactionDetails = $this->getTransactionDetails($transaction['hash']);
            $proposingTeamId = json_decode($transaction['teamsInvolved'], true)[0];
            $proposingTeamName = $teams[$proposingTeamId]['teamLocation'] . " " . $teams[$proposingTeamId]['teamNickname'];

            $getting = array();
            foreach ($transactionDetails as $k => $v) {
                if ($v['fromSlotCategoryId'] != 1002) {
                    continue;
                }
                $getting[] = $v->player->fullName . " (" . $positions[$v->player->position] . ", " . $v->player->team . ")";
            }
            if (count($getting) == 0) {

                // No automatic waiver pickups
                continue;
            }

            $strMessage = "*" . $proposingTeamName . "* picked up on waivers: " . Helper::implodeNice($getting) . ".";
            echo $strMessage;
            echo "<br />";
            echo "<br />";

            $m = new Message();
            $m->messageVisibleToChannel();
            $m->setText($strMessage);

            $slack->postMessage($m, $channelId);

            Transaction::find($transaction->primary)->update(['sentInitialNotification' => $dateUtc]);
        }
    }

    public function getRosters($leagueId, $returnType = self::RETURN_ALL)
    {}
    public function getRostersWithHoles($leagueId = false)
    {}

    public function getProTeamIds()
    {

    }

    public function getWaiverOrder($leagueId = "111799")
    {}

    public function updateLeagueStandings($leagueId)
    {

        $teams = $this->getTeams($leagueId);

        return $teams;

        // TO-DO: refactor the rest of this method (maybe full re-write?)
        $PDOSelect = $this->db->prepare('SELECT S.matchupTypeId,S.matchupPeriodId, IF((S.homeTeamId = :teamId AND S.outcome = 1) OR (S.awayTeamId = :teamId AND S.outcome = 2),1,0) as Win, IF((S.homeTeamScores = S.awayTeamScores) AND (S.homeTeamAdjustment = S.awayTeamAdjustment) AND S.outcome!=0,1,0) as Tie,IF(S.outcome = 0,0,1) as Complete,IF(S.homeTeamId = @teamId,S.homeTeamScores,S.awayTeamScores) as PF,IF(S.homeTeamId = @teamId,S.awayTeamScores,S.homeTeamScores) as PA FROM scheduleItems as S
        WHERE (S.homeTeamId = :teamId OR S.awayTeamId = :teamId)
        AND S.leagueId = :leagueId
        ORDER BY S.matchupPeriodId ASC');

        $PDOSelect->bindParam(':leagueId', $leagueId);
        $teamStandings = array();

        foreach ($teams as $k => $v) {
            $teamId = $v['teamId'];
            $PDOSelect->bindParam(':teamId', $teamId);
            $PDOSelect->execute();
            $results = $PDOSelect->fetchAll(\PDO::FETCH_ASSOC);

            $overallWins = 0;
            $overallLosses = 0;
            $overallTies = 0;

            $streakLength = 0;
            $streakType = 0;
            $overallStanding = 0;

            $pf = 0;
            $pa = 0;

            $lastResult = 0;
            foreach ($results as $k => $v) {
                if ($v['Complete'] == 0) {
                    continue;
                }
                $pf += json_decode($v['PF'], true)[0];
                $pa += json_decode($v['PA'], true)[0];

                if ($v['Win'] == 1) {
                    $overallWins += 1;
                    $thisResult = 1;
                } elseif ($v['Tie'] == 1) {
                    $overallTies += 1;
                    $thisResult = 3;
                } else {
                    $overallLosses += 1;
                    $thisResult = 2;
                }

                if ($thisResult == $lastResult) {
                    $streakLength++;
                    $streakType = $thisResult;
                } else {
                    $streakLength = 0;
                    $streakType = 0;
                }
                $lastResult = $thisResult;
            }
            $teamStandings[] = array(
                'leagueId' => $leagueId,
                'teamId' => $teamId,
                'overallWins' => $overallWins,
                'overallLosses' => $overallLosses,
                'overallTies' => $overallTies,
                'streakLength' => $streakLength,
                'streakType' => $streakType,
                'pointsFor' => $pf,
                'pointsAgainst' => $pa,
                'overallStanding' => null,
            );
        }
        foreach ($teamStandings as $k => $v) {
            $wins[$k] = $v['overallWins'];
            $losses[$k] = $v['overallWins'];
            $ties[$k] = $v['overallWins'];
            $pointsFor[$k] = $v['pointsFor'];
            $pointsAgainst[$k] = $v['pointsAgainst'];
        }
        array_multisort($wins, SORT_DESC, $losses, SORT_ASC, $ties, SORT_DESC, $pointsFor, SORT_DESC, $pointsAgainst, SORT_ASC, $teamStandings);
        $i = 0;
        foreach ($teamStandings as &$v) {
            $i++;
            $v['overallStanding'] = $i;
        }

        // Update the teams table with this info
        $PDOUpdate = $this->db->prepare('UPDATE `teams` SET `overallWins` = :overallWins, `overallLosses` = :overallLosses, `overallTies` = :overallTies, `streakLength` = :streakLength, `streakType` = :streakType, `pointsFor` = :pointsFor, `pointsAgainst` = :pointsAgainst, `overallStanding` = :overallStanding  WHERE `teamId` = :teamId AND `leagueId` = :leagueId');
        foreach ($teamStandings as $k => $v) {
            $PDOUpdate->bindParam(':leagueId', $v['leagueId']);
            $PDOUpdate->bindParam(':teamId', $v['teamId']);
            $PDOUpdate->bindParam(':overallWins', $v['overallWins']);
            $PDOUpdate->bindParam(':overallLosses', $v['overallLosses']);
            $PDOUpdate->bindParam(':overallTies', $v['overallTies']);
            $PDOUpdate->bindParam(':streakLength', $v['streakLength']);
            $PDOUpdate->bindParam(':streakType', $v['streakType']);
            $PDOUpdate->bindParam(':pointsFor', $v['pointsFor']);
            $PDOUpdate->bindParam(':pointsAgainst', $v['pointsAgainst']);
            $PDOUpdate->bindParam(':overallStanding', $v['overallStanding']);
            $PDOUpdate->execute();
        }
        $this->logToRedis(__FUNCTION__);

        return $teamStandings;

    }

    public function getLeagueStandings($leagueId = "111799")
    {}
    public function getPlayerOwner($playerName, $leagueId = "111799")
    {}

    /**
     *
     *  Analysis
     *
     */
    public function getRecords($leagueId)
    {}
    public function sendEndOfMatchupNotification($leagueId, $notificationType, $matchupInfo)
    {
        // Check to see if this has already been sent
        $hash = md5($leagueId . "-" . $matchupInfo['matchupPeriodId'] . "-" . $matchupInfo['homeTeamId'] . "-" . $matchupInfo['awayTeamId']);
        $n = Notification::where('hash', '=', $hash)->where('isProcessed', '=', '1')->count();

        if ($n > 0 && ! $this->debug) {
            \Log::info("Skipping...");
            return false;
        }

        $teams = $this->getTeams($leagueId);

        // Not yet sent, so build it, send it, and log it
        $matchup = $this->getMatchup($leagueId, $matchupInfo['homeTeamId'], $matchupInfo['matchupPeriodId'], false);

        $data = [];
        $data['hash'] = $hash;
        $homeName = $matchup->homeTeam->getName();
        $awayName = $matchup->awayTeam->getName();

        if ($matchup['homeScore'] == $matchup['awayScore']) {
            // tie game!
            $strMessage = "It's a tie! *" . $homeName . "* and *" . $awayName . "* both scored " . $matchup['homeScore'] . " points this week.";
            $winner = $homeName;
            $loser = $awayName;
            $winnerHA = "home";
            $loserHA = "away";
        } else {
            if (($matchup['homeScore'] > $matchup['awayScore']) || ($matchup['homeScore'] == $matchup['awayScore'])) {
                $winner = $homeName;
                $loser = $awayName;
                $winnerHA = "home";
                $loserHA = "away";
            } else {
                $winner = $awayName;
                $loser = $homeName;
                $winnerHA = "away";
                $loserHA = "home";
            }
            $strMessage = config('espn.phrases.matchupEnd')[array_rand(config('espn.phrases.matchupEnd'))] . " *" . $winner . "* has beaten *" . $loser . "* by " . abs($matchup['homeScore'] - $matchup['awayScore']) . " points.";
        }
        $winnerTopPlayer = $this->getPlayerInfo($matchup[$winnerHA . 'TopScorerId']);
        $loserTopPlayer = $this->getPlayerInfo($matchup[$loserHA . 'TopScorerId']);

        // Post to the slack channel for this league
        $channelId = $this->getSlackChannelFromLeague($leagueId);
        // $channelId = "G1LKKBAQN";

        $message = new \App\Http\Controllers\Slack\Helpers\Message();
        $message->messageVisibleToChannel();
        $message->setText($strMessage);

        $attachment = new \App\Http\Controllers\Slack\Helpers\Attachment();
        $attachment->setPretext('Top players:');
        $attachment->setFields([[
            'title' => $winner,
            'value' => "*" . $matchup[$winnerHA . 'TopScorerScore'] . " pts.*\n" . $winnerTopPlayer['fullName'] . ($winnerTopPlayer['position'] == "16" ? " " : " (" . config('espn.defaultPositionId')[$winnerTopPlayer['position']] . ", " . $winnerTopPlayer['team'] . ") "),
            'short' => true,
        ],
            [
                'title' => $loser,
                'value' => "*" . $matchup[$loserHA . 'TopScorerScore'] . " pts.*\n" . $loserTopPlayer['fullName'] . ($loserTopPlayer['position'] == "16" ? " " : " (" . config('espn.defaultPositionId')[$loserTopPlayer['position']] . ", " . $loserTopPlayer['team'] . ") "),
                'short' => true,
            ]]);
        $attachment->processMarkdownForFields();

        $message->addAttachment($attachment->build());
        $slack = new \App\Http\Controllers\Slack\Slack;
        $slack->postMessage($message, $channelId, 'fantasybot');

        $notification = Notification::firstOrNew([
            'leagueId' => $leagueId,
            'type' => $notificationType,
            'hash' => $hash,
        ]);

        $notification->isProcessed = 1;
        $notification->save();

        return $strMessage;

    }

    public function analyzeTeam($team)
    {}
    public function rankAnalysis($analyses)
    {}

    public function getTeamIdFromGroupMeId($leagueId, $groupMeId)
    {}
    public function getTeams($leagueId)
    {
        $teams = Team::with('teamNotificationId')->where('leagueId', '=', $leagueId)->get();
        $return = [];
        foreach ($teams as $team) {
            $return[$team['teamId']] = $team;
        }
        return $return;
    }

    /**
     *
     *  Support Functions
     *
     */

    /**
     * Get an imploded list of team numbers for a given league
     *
     * @param      string  $leagueId  The league ID
     */
    public function getLeagueTeamNumbers($leagueId)
    {
        $league = League::where('leagueId', '=', $leagueId)->first();
        return $league->getTeamIds();
    }

    public function getWeek($boolIncludeSeasonType = false)
    {

        $seasontype = null;
        $week = null;

        // Figure out which week to use
        $nflScheduleWeeks = config('espn.nflScheduleWeeks');
        $dateNow = new \DateTime(null, new \DateTimeZone('UTC'));
        foreach ($nflScheduleWeeks as $k => $v) {
            $dateWeekStart = \DateTime::createFromFormat('Y-m-d H:i:sO', $k, new \DateTimeZone('UTC'));
            if ($dateNow > $dateWeekStart) {
                $seasontype = $v['seasontype'];
                $week = $v['week'];
            } else {
                break;
            }
        }
        if ($boolIncludeSeasonType) {
            $week = array(
                "week" => $week,
                "seasontype" => $seasontype,
            );
        }
        \Log::info("Week:");
        \Log::info(json_encode($week));
        return $week;

    }

    public function getTransactionDetails($hash)
    {
        return TransactionDetail::where('hash', '=', $hash)->with('player')->get();
        // $PDOSelect = $this->db->prepare('SELECT D.*, E.fullName, E.position, E.team FROM `transactionDetails` as D
        // LEFT JOIN espnAllPlayers as E
        // ON E.playerId = D.playerId
        // WHERE D.`hash` LIKE :hash');
        // $PDOSelect->bindParam(':hash', $hash);
        // $PDOSelect->execute();
        // return $PDOSelect->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function logTransactionDetails($data)
    {
        foreach ($data->pendingMoveItems as $k => $v) {

            $t = TransactionDetail::firstOrNew([
                'hash' => $data->hash,
                'playerId' => $v->playerId,
            ]);
            $this->results[__FUNCTION__][$data->hash][] = $t->fill([
                'hash' => $data->hash,
                'leagueId' => $data->leagueId,
                'draftOverallSelection' => $v->draftOverallSelection,
                'playerId' => $v->playerId,
                'fromTeamId' => $v->fromTeamId,
                'fromSlotCategoryId' => $v->fromSlotCategoryId,
                'toTeamId' => $v->toTeamId,
                'rating' => $v->rating,
                'toSlotCategoryId' => $v->toSlotCategoryId,
                'keeper' => $v->keeper,
                'moveTypeId' => $v->moveTypeId,
            ])->save();
        }

    }

    public function logMatchupInfo($data)
    {
        $hash = md5($data['leagueId'] . "-" . $data['matchupPeriodId'] . "-" . $data['homeTeamId'] . "-" . $data['awayTeamId']);

        // Get or instantiate a model instance
        $matchup = Matchup::firstOrNew([
            'leagueId' => $data['leagueId'],
            'hash' => $hash,
        ]);

        // Insert the data
        $matchup->fill([
            'leagueId' => $data['leagueId'],
            'matchupPeriodId' => $data['matchupPeriodId'],
            'isBye' => $data['isBye'],
            'homeTeamId' => $data['homeTeamId'],
            'homeScore' => $data['homeScore'],
            'homeGamesInProgress' => $data['homeGamesInProgress'],
            'homeTopScorerId' => $data['homeTopScorerId'],
            'homeTopScorerScore' => $data['homeTopScorerScore'],
            'homeGamesYetToPlay' => $data['homeGamesYetToPlay'],
            'homeMinutesRemaining' => $data['homeMinutesRemaining'],
            'homeIsFinal' => $data['homeIsFinal'],
            'homeProjectedPoints' => $data['homeProjectedPoints'],
            'awayTeamId' => $data['awayTeamId'],
            'awayScore' => $data['awayScore'],
            'awayGamesInProgress' => $data['awayGamesInProgress'],
            'awayTopScorerId' => $data['awayTopScorerId'],
            'awayTopScorerScore' => $data['awayTopScorerScore'],
            'awayGamesYetToPlay' => $data['awayGamesYetToPlay'],
            'awayMinutesRemaining' => $data['awayMinutesRemaining'],
            'awayIsFinal' => $data['awayIsFinal'],
            'awayProjectedPoints' => $data['awayProjectedPoints'],
            'winner' => $data['winner'],
            'homePointsDifferential' => ($data['homeScore'] - $data['awayScore']),
            'awayPointsDifferential' => ($data['awayScore'] - $data['homeScore']),
            'hash' => $hash,
        ]);

        // Save the record
        $matchup->save();
    }
    public function logPlayerStatusChange($playerId, $currentStatus, $priorStatus)
    {
        $IsBetter = $priorStatus > $currentStatus;
        PlayerStatusChange::create([
            'playerId' => $playerId,
            'currentStatus' => $currentStatus,
            'priorStatus' => $priorStatus,
            'IsBetter' => $IsBetter,
        ]);
    }
    public function logPlayerProTeamChange($data)
    {
        PlayerProTeamChange::create($data);
    }

    public function log($data)
    {
        // Log::create([
        //     'result'              => $data['result'],
        //     'leagueId'            => $data['meta']['leagueId'],
        //     'error'               => json_encode($data['error']),
        //     'meta'                => json_encode($data['meta']),
        //     'headers'             => json_encode($data['headers']),
        //     'playerStatusChanges' => json_encode($data['meta']['playerStatusChanges']),
        // ]);
    }

    public function getSacko($leagueId = "111799")
    {}

    public function sendToGroupMe($data, $attachmentArray = null)
    {}

    public function calculateQBR($data)
    {
        $attempted = array_get($data, 'rawStats.0', 0);
        $completed = array_get($data, 'rawStats.1', 0);
        $passYards = array_get($data, 'rawStats.3', 0);
        $passTouchdowns = array_get($data, 'rawStats.4', 0);
        $interceptions = array_get($data, 'rawStats.20', 0);

        $a = (($completed / $attempted) - .3) * 5;
        $b = (($passYards / $attempted) - 3) * .25;
        $c = ($passTouchdowns / $attempted) * 20;
        $d = 2.375 - (($interceptions / $attempted) * 25);

        $a = ($a < 0 ? 0 : ($a > 2.375 ? 2.375 : $a));
        $b = ($b < 0 ? 0 : ($b > 2.375 ? 2.375 : $b));
        $c = ($c < 0 ? 0 : ($c > 2.375 ? 2.375 : $c));
        $d = ($d < 0 ? 0 : ($d > 2.375 ? 2.375 : $d));

        return round((($a + $b + $c + $d) / 6) * 100, 1);
    }

    public function createMatchupImage($data, $boolShowImage = false)
    {
        $root = $this->root;

        // echo json_encode($data);
        // exit();

        // create an image manager instance with favored driver
        $manager = new ImageManager(array(
            'driver' => 'imagick',
        ));

        $homeTextBottom = "projected";
        $awayTextBottom = "projected";
        $homeTextBottomPoints = "points";
        $awayTextBottomPoints = "points";
        $matchupStatus = "Preview";
        $boolShowLiveScores = false;
        $boolShowFinalScores = false;
        $winner = null;

        $cGreen = "#1FAF4F";
        $cRed = "#BB0000";
        $cOrange = "#FF790D";
        $cGrey = "#F2F2F2";

        $circleBorderRadius = 5;
        $homeCircleColor = $cGrey;
        $awayCircleColor = $cGrey;
        $nflGamesPlayed = $this->getNflGamesPlayed();

        // dd($data->toArray());

        if ($nflGamesPlayed == 0) {
            $matchupStatus = "Preview";
            $boolShowLiveScores = false;
        } elseif ($data->winner == 'home') {
            $homeCircleColor = $cGreen;
            $awayCircleColor = $cRed;
            $circleBorderRadius = 10;
        } else if ($data->winner == 'away') {
            $homeCircleColor = $cRed;
            $awayCircleColor = $cGreen;
            $circleBorderRadius = 10;
        } else if (($data->homeMinutesRemaining < 540) || ($data->awayMinutesRemaining < 540)) {
            $boolShowLiveScores = true;
            $matchupStatus = "Live";
        }

        \Log::info($data);

        foreach (['away' => 'awayTeam', 'home' => 'homeTeam'] as $k => $v) {
            if (pathinfo($data->$v->logoUrl, PATHINFO_EXTENSION) == "svg") {
                $data->$v->logoUrl .= "_160.png";
            }
            if ($data->$v->logoUrl == null) {
                $data->$v->logoUrl = $root . "i/default.png";
            }
            if (($data[$k . 'IsFinal'] == "1") && (($data->homeMinutesRemaining < 540) || ($data->awayMinutesRemaining < 540)) && (($data->homeScore > 0) || ($data->awayScore > 0))) {
                $boolShowLiveScores = true;
                $matchupStatus = "Report";
                $tmp = $k . "TextBottom";
                $$tmp = "Final";
            }
        }

        if (($homeTextBottom == "Final") && ($awayTextBottom == "Final")) {

            if (($data->winner == 'undecided') && (($data->winner != 'home') && ($data->winner != 'away'))) {
                $circleBorderRadius = 10;

                // It's done, but not marked yet
                if ($data->homeScore == $data->awayScore) {
                    $homeCircleColor = $cOrange;
                    $awayCircleColor = $cOrange;
                    $winner = "tie";
                } else if ($data->homeScore > $data->awayScore) {
                    $homeCircleColor = $cGreen;
                    $awayCircleColor = $cRed;
                    $winner = "home";
                } else {
                    $homeCircleColor = $cRed;
                    $awayCircleColor = $cGreen;
                    $winner = "away";
                }
            }

            $matchupStatus = "Summary";
            $boolShowLiveScores = false;
            $boolShowFinalScores = true;
        }
        $imgBackground = $manager->make($root . 'i/background.png');
        $imgVersus = $manager->make($root . 'i/vs.png');

        try {
            $imgLeft = $manager->make($data->homeTeam->logoUrl)->fit(200);
        } catch (\Exception $e) {
            $imgLeft = $manager->make($root . "i/default.png")->fit(200);
        }

        try {
            $imgRight = $manager->make($data->awayTeam->logoUrl)->fit(200);
        } catch (\Exception $e) {
            $imgRight = $manager->make($root . "i/default.png")->fit(200);
        }

        $imgLeft->mask($root . 'i/mask.png', true)->fit(130);
        $imgRight->mask($root . 'i/mask.png', true)->fit(130);

        $wCenter = round(($imgBackground->width() / 2) - ($imgVersus->width() / 2));
        $wLeft = round(((($imgBackground->width() / 2) - ($imgVersus->width() / 2)) / 2) - ($imgLeft->width() / 2));
        $wRight = round(((($imgBackground->width() / 2) - ($imgVersus->width() / 2)) * 2) - ($imgRight->width() / 2));

        $circleRadiusOffset = 45;
        $centerBottomOffset = 70;
        $hCenterOffset = -5;
        $wLeftCircle = round($wLeft + (($imgLeft->width()) / 2));
        $wRightCircle = round($wRight + (($imgRight->width()) / 2));
        $hCircle = round((($imgBackground->height() / 2)) - ($centerBottomOffset / 2) + $hCenterOffset);

        $hCenterBottom = round(($imgBackground->height() / 2) - ($imgVersus->height() / 2) + $centerBottomOffset);
        $hCenter = round(($imgBackground->height() / 2) - ($imgVersus->height() / 2) + $hCenterOffset);

        $imgBackground
            ->insert($imgVersus, null, $wCenter, $hCenterBottom);
        $imgBackground
            ->insert($imgLeft, null, $wLeft, $hCenter);
        $imgBackground
            ->insert($imgRight, null, $wRight, $hCenter);
        $imgBackground->circle($imgLeft->width() + $circleRadiusOffset, $wLeftCircle, $hCircle, function ($draw) use ($homeCircleColor, $circleBorderRadius) {
            $draw->border($circleBorderRadius, $homeCircleColor);
        });
        $imgBackground->circle($imgRight->width() + $circleRadiusOffset, $wRightCircle, $hCircle, function ($draw) use ($awayCircleColor, $circleBorderRadius) {
            $draw->border($circleBorderRadius, $awayCircleColor);
        });

        $imgBackground->resize(400, null, function ($constraint) {
            $constraint->aspectRatio();
        });

        $imgBackground->text('Matchup ' . $matchupStatus, 80, 23, function ($font) {
            $font->file(public_path() . '/fonts/arialbd.ttf');
            $font->size(14);
            $font->color('#fff');
            $font->align('center');
            $font->valign('middle');
        });

        $imgBackground->text('Week ' . $data->matchupPeriodId, 325, 23, function ($font) {
            $font->file(public_path() . '/fonts/arialbd.ttf');
            $font->size(14);
            $font->color('#fff');
            $font->align('center');
            $font->valign('middle');
        });

        $textOffsetTop = 0;
        $fontHomeName = 18;
        $fontAwayName = 18;
        $fonts['team'] = public_path() . '/fonts/calibri.ttf';
        // $fonts['team']      = public_path() . '/fonts/Symbola.ttf';
        $fonts['score'] = public_path() . '/fonts/Roboto-Bold.ttf';
        $fonts['projected'] = public_path() . '/fonts/arialbd.ttf';

        if ($this->getTextWidth($data->homeTeam->getName(), $fonts['team'], $fontHomeName) > 150) {
            $fontHomeName = 16;
            if ($this->getTextWidth($data->homeTeam->getName(), $fonts['team'], $fontHomeName) > 150) {
                $fontHomeName = 13;
            }
        }
        if ($this->getTextWidth($data->awayTeam->getName(), $fonts['team'], $fontAwayName) > 150) {
            $fontAwayName = 16;
            if ($this->getTextWidth($data->awayTeam->getName(), $fonts['team'], $fontAwayName) > 150) {
                $fontAwayName = 13;
            }
        }

        // echo $fontHomeName;
        // echo "<br />";
        // echo $fontAwayName;
        // exit();
        $imgBackground->text($data->homeTeam->getName(), 80, 130, function ($font) use ($fonts, $fontHomeName) {
            $font->file($fonts['team']);
            $font->size($fontHomeName);
            $font->color('#888');
            $font->align('center');
            $font->valign('middle');
        });
        $imgBackground->text($data->awayTeam->getName(), 320, 130, function ($font) use ($fonts, $fontAwayName) {
            $font->file($fonts['team']);
            $font->size($fontAwayName);
            $font->color('#888');
            $font->align('center');
            $font->valign('middle');
        });

        $awayPosLivePoints = 265;
        $awayPosProjPoints = 320;
        $awayPosPercDone = 370;

        $homePosLivePoints = 30;
        $homePosProjPoints = 85;
        $homePosPercDone = 135;

        $homeTextBottomPercDone = "done";
        $awayTextBottomPercDone = "done";

        $fontSize['projPoints'] = 43;
        $fontSize['livePoints'] = 37;
        $fontSize['percDone'] = 20;
        $fontColor['projPoints'] = "#AAAAAA";
        $fontColor['livePoints'] = "#111111";
        $fontColor['percDone'] = "#CCCCCC";

        if ($boolShowLiveScores === true) {
            $fontSize['projPoints'] = 30;

            if ($data->homeIsFinal) {
                $homePosProjPoints = 999;
                $homePosLivePoints = 55;
                $homePosPercDone = 115;
            } else if ($data->awayIsFinal) {
                $awayPosProjPoints = 999;
                $awayPosLivePoints = 285;
                $awayPosPercDone = 355;
            }

            // Live points
            $imgBackground->text($data->homeScore, $homePosLivePoints, 175, function ($font) use ($fonts, $fontSize, $fontColor) {
                $font->file($fonts['score']);
                $font->size($fontSize['livePoints']);
                $font->color($fontColor['livePoints']);
                $font->align('center');
                $font->valign('bottom');
            });

            $imgBackground->text($data->awayScore, $awayPosLivePoints, 175, function ($font) use ($fonts, $fontSize, $fontColor) {
                $font->file($fonts['score']);
                $font->size($fontSize['livePoints']);
                $font->color($fontColor['livePoints']);
                $font->align('center');
                $font->valign('bottom');
            });

            $imgBackground->text($homeTextBottomPoints, $homePosLivePoints, 190, function ($font) use ($fonts, $fontSize, $fontColor) {
                $font->file($fonts['projected']);
                $font->size(11);
                $font->color($fontColor['livePoints']);
                $font->align('center');
                $font->valign('bottom');
            });
            $imgBackground->text($awayTextBottomPoints, $awayPosLivePoints, 190, function ($font) use ($fonts, $fontSize, $fontColor) {
                $font->file($fonts['projected']);
                $font->size(11);
                $font->color($fontColor['livePoints']);
                $font->align('center');
                $font->valign('bottom');
            });

            // % finished with week
            $homePercDone = floor((540 - $data->homeMinutesRemaining) / 5.4) . "%";
            $awayPercDone = floor((540 - $data->awayMinutesRemaining) / 5.4) . "%";
            $imgBackground->text($homePercDone, $homePosPercDone, 175, function ($font) use ($fonts, $fontSize, $fontColor) {
                $font->file($fonts['score']);
                $font->size($fontSize['percDone']);
                $font->color($fontColor['percDone']);
                $font->align('center');
                $font->valign('bottom');
            });

            $imgBackground->text($awayPercDone, $awayPosPercDone, 175, function ($font) use ($fonts, $fontSize, $fontColor) {
                $font->file($fonts['score']);
                $font->size($fontSize['percDone']);
                $font->color($fontColor['percDone']);
                $font->align('center');
                $font->valign('bottom');
            });

            $imgBackground->text($homeTextBottomPercDone, $homePosPercDone, 190, function ($font) use ($fonts, $fontSize, $fontColor) {
                $font->file($fonts['projected']);
                $font->size(11);
                $font->color($fontColor['percDone']);
                $font->align('center');
                $font->valign('bottom');
            });
            $imgBackground->text($awayTextBottomPercDone, $awayPosPercDone, 190, function ($font) use ($fonts, $fontSize, $fontColor) {
                $font->file($fonts['projected']);
                $font->size(11);
                $font->color($fontColor['percDone']);
                $font->align('center');
                $font->valign('bottom');
            });
        }
        if ($boolShowFinalScores === true) {
            $homePoints = $data->homeScore;
            $awayPoints = $data->awayScore;
            $fontColor['projPoints'] = $fontColor['livePoints'];
            $imgBackground->text($winner == "tie" ? "TIE" : "WINNER", 200 + ($winner == "tie" ? 0 : ($winner == "home" ? -52 : 52)), 120, function ($font) use ($fonts, $fontSize, $fontColor, $winner, $cOrange, $cGreen) {
                \Log::info("adding text");
                $font->file($fonts['score']);
                $font->size($winner == "tie" ? 60 : 30);
                $font->color($winner == "tie" ? $cOrange : $cGreen);
                $font->align('center');
                $font->valign('bottom');
                $font->angle($winner == "tie" ? 0 : ($winner == "home" ? -60 : 60));
            });
        } else {
            $homePoints = $data->homeProjectedPoints;
            $awayPoints = $data->awayProjectedPoints;
        }
        $imgBackground->text($homePoints, $homePosProjPoints, 175, function ($font) use ($fonts, $fontSize, $fontColor) {
            $font->file($fonts['score']);
            $font->size($fontSize['projPoints']);
            $font->color($fontColor['projPoints']);
            $font->align('center');
            $font->valign('bottom');
        });

        $imgBackground->text($awayPoints, $awayPosProjPoints, 175, function ($font) use ($fonts, $fontSize, $fontColor) {
            $font->file($fonts['score']);
            $font->size($fontSize['projPoints']);
            $font->color($fontColor['projPoints']);
            $font->align('center');
            $font->valign('bottom');
        });

        $imgBackground->text($homeTextBottom, $homePosProjPoints, 190, function ($font) use ($fonts, $fontSize, $fontColor) {
            $font->file($fonts['projected']);
            $font->size(11);
            $font->color($fontColor['projPoints']);
            $font->align('center');
            $font->valign('bottom');
        });
        $imgBackground->text($awayTextBottom, $awayPosProjPoints, 190, function ($font) use ($fonts, $fontSize, $fontColor) {
            $font->file($fonts['projected']);
            $font->size(11);
            $font->color($fontColor['projPoints']);
            $font->align('center');
            $font->valign('bottom');
        });

        // exit();
        $filename = 'i/matchups/' . $data->hash . '.png';
        try
        {
            unlink($root . $filename);
        } catch (\Throwable $e) {
        }
        $imgBackground->save($root . $filename, 100);
        if ($boolShowImage === true) {
            header('Content-Type:image/png');
            readfile($root . $filename);
        } else {
            return $filename;
        }
    }

    public function createAllMatchupImages($leagueId, $boolShowImage = false, $matchupPeriodId = null)
    {
        $matchups = $this->getMatchups($leagueId, $matchupPeriodId);
        $images = array();
        foreach ($matchups as $k => $v) {
            if ($v['isBye'] != 1) {
                $images[] = $this->root . $this->createMatchupImage($v);
            }
        }
        return $this->combineImages($images, 'i/matchups/all_' . md5(json_encode($images)) . '.png', 2, $boolShowImage);
    }

    public function createNflMatchupImage($liveGames = false, $boolShowImage = false, $debug = false)
    {

        $this->updateNflBroadcastInfo();
        if ($liveGames != false) {
            // Get active games
            $games = $this->getLiveNflGames();
        } else {
            // Get games from the last 6 days and within one day
            $games = $this->getUpcomingGames(6, 1);
        }
        $images = [];

        // echo json_encode($games);
        // exit();
        foreach ($games as $game) {
            $homeColor = "#" . $game->homeTeam->getColor();
            $awayColor = "#" . $game->awayTeam->getColor();

            $weatherConditions = $game['weatherConditions'];
            $weatherTemperature = $game['weatherTemperature'];

            $homeLogo = $game->homeTeam->logo;
            $awayLogo = $game->awayTeam->logo;

            $manager = new ImageManager(array(
                'driver' => 'imagick',
            ));

            $width = 250;
            $height = 70;
            $positionTeamsV = 20;
            $positionTimeV = 50;
            $positionScoreOffsetH = 10;

            $dateGameStart = \DateTime::createFromFormat('Y-m-d H:i:s', $game['dateStart'], new \DateTimeZone('UTC'));
            $dateGameStart->setTimezone(new \DateTimeZone('America/Chicago'));
            $dateFormat = 'D \a\t g:i a T';
            $dateFormatFuture = 'n/j \a\t g:i a T';

            $dateNow = new \DateTime(null, new \DateTimeZone('America/Chicago'));
            $interval = $dateGameStart->diff($dateNow);

            if ($interval->format('%a') > 7) {
                $dateFormat = $dateFormatFuture;
            }

            $img = $manager->canvas($width, $height, $homeColor);
            $points = [0, 0, ($width / 2) + 15, 0, ($width / 2) - 15, $height, 0, $height];

            $img->polygon($points, function ($draw) use ($awayColor) {
                $draw->background($awayColor);
            });

            $homeLogoImage = $manager->make($homeLogo)->heighten(round($height * 1.5))->opacity(13);
            $awayLogoImage = $manager->make($awayLogo)->heighten(round($height * 1.5))->opacity(13);

            $img
                ->insert($awayLogoImage, 'left', -1 * (round($awayLogoImage->width() / 3, 0) - 20));
            $img
                ->insert($homeLogoImage, 'right', -1 * (round($homeLogoImage->width() / 3, 0) - 20));

            // TEAMS
            $img->text($game->homeTeam->abbreviation, ($width / 2) + 20, $positionTeamsV, function ($font) {
                $font->file(public_path() . '/fonts/SF-UI-Display-Light.otf');
                $font->size(23);
                $font->color('#FFF');
                $font->align('left');
                $font->valign('middle');
            });
            $img->text("@", ($width / 2) + 5, $positionTeamsV, function ($font) {
                $font->file(public_path() . '/fonts/SF-UI-Display-Thin.otf');
                $font->size(16);
                $font->color('#FFF');
                $font->align('center');
                $font->valign('middle');
            });
            $img->text($game->awayTeam->abbreviation, ($width / 2) - 15, $positionTeamsV, function ($font) {
                $font->file(public_path() . '/fonts/SF-UI-Display-Light.otf');
                $font->size(23);
                $font->color('#FFF');
                $font->align('right');
                $font->valign('middle');
            });

            // DATE TIME/CLOCK
            if ($game['statusState'] == "pre") {

                // BEFORE a game
                $img->text($dateGameStart->format($dateFormat), ($width / 2), $positionTimeV, function ($font) {
                    $font->file(public_path() . '/fonts/SF-UI-Text-Light.otf');
                    $font->size(16);
                    $font->color('#FFF');
                    $font->align('center');
                    $font->valign('middle');
                });

                $img->text("(" . $game->awayTeam->record . ")", $positionScoreOffsetH, $positionTeamsV, function ($font) {
                    $font->file(public_path() . '/fonts/SF-UI-Text-Regular.otf');
                    $font->size(18);
                    $font->color('#FFF');
                    $font->align('left');
                    $font->valign('middle');
                });
                $img->text("(" . $game->homeTeam->record . ")", $width - $positionScoreOffsetH, $positionTeamsV, function ($font) {
                    $font->file(public_path() . '/fonts/SF-UI-Text-Regular.otf');
                    $font->size(18);
                    $font->color('#FFF');
                    $font->align('right');
                    $font->valign('middle');
                });
            } elseif ($game['statusState'] == "post") {

                $img->text($game['statusDetail'], ($width / 2), $positionTimeV, function ($font) {
                    $font->file(public_path() . '/fonts/SF-UI-Text-Light.otf');
                    $font->size(16);
                    $font->color('#FFF');
                    $font->align('center');
                    $font->valign('middle');
                });

                // AFTER a game

                $textColorScoreHome = "#FFFFFF";
                $textColorScoreAway = "#FFFFFF";
                if ($game['awayTeamScore'] > $game['homeTeamScore']) {
                    $textColorScoreHome = "#CCCCCC";
                } elseif ($game['awayTeamScore'] < $game['homeTeamScore']) {
                    $textColorScoreAway = "#CCCCCC";
                }
                $img->text($game['awayTeamScore'], $positionScoreOffsetH, $positionTeamsV, function ($font) use ($textColorScoreAway) {
                    $font->file(public_path() . '/fonts/SF-UI-Text-Regular.otf');
                    $font->size(24);
                    $font->color($textColorScoreAway);
                    $font->align('left');
                    $font->valign('middle');
                });
                $img->text($game['homeTeamScore'], $width - $positionScoreOffsetH, $positionTeamsV, function ($font) use ($textColorScoreHome) {
                    $font->file(public_path() . '/fonts/SF-UI-Text-Regular.otf');
                    $font->size(24);
                    $font->color($textColorScoreHome);
                    $font->align('right');
                    $font->valign('middle');
                });
            } else {
                if (($weatherConditions != null) && ($weatherTemperature != null) && ($game['venueIndoor'] != 1)) {

                    // Add temp and weather conditions
                    $positionWeatherV = $positionTimeV + 50;
                    $positionTimeV -= 10;

                    try {
                        $imgWeatherConditions = $manager->make($this->root . 'i/wx/' . strtolower($weatherConditions) . '.png')->heighten(20)
                            ->opacity(80);
                        $img->insert($imgWeatherConditions, 'bottom-left', 94, 2);

                    } catch (\Throwable $e) {
                        \Log::info("Can't get weather image for $weatherConditions");
                    }

                    $img->text($weatherTemperature . " °F", ($width / 2) + 15, 58, function ($font) {
                        $font->file(public_path() . '/fonts/SF-UI-Text-Light.otf');
                        $font->size(12);
                        $font->color('#DDD');
                        $font->align('center');
                        $font->valign('middle');
                    });
                }
                $img->text($game['statusDetail'], ($width / 2), $positionTimeV, function ($font) {
                    $font->file(public_path() . '/fonts/SF-UI-Text-Light.otf');
                    $font->size(16);
                    $font->color('#FFF');
                    $font->align('center');
                    $font->valign('middle');
                });

                // DURING a game
                $img->text($game['awayTeamScore'], $positionScoreOffsetH, $positionTeamsV, function ($font) {
                    $font->file(public_path() . '/fonts/SF-UI-Text-Regular.otf');
                    $font->size(24);
                    $font->color('#FFF');
                    $font->align('left');
                    $font->valign('middle');
                });
                $img->text($game['homeTeamScore'], $width - $positionScoreOffsetH, $positionTeamsV, function ($font) {
                    $font->file(public_path() . '/fonts/SF-UI-Text-Regular.otf');
                    $font->size(24);
                    $font->color('#FFF');
                    $font->align('right');
                    $font->valign('middle');
                });
            }
            $filename = 'i/nfl/' . md5(json_encode($game)) . '.png';
            try
            {
                if (file_exists($this->root . $filename)) {
                    unlink($this->root . $filename);
                }
            } catch (Exception $e) {
            }
            \Log::info("Saving NFL matchup image to $filename");
            $saveResult = $img->save($this->root . $filename, 100);

            $images[] = $this->root . $filename;
        }
        $filenameAll = 'i/nfl/' . md5(json_encode($games)) . '.png';

        // Figure out the number of columns to use (to make a square-ish grid)
        $columns = 2;

        if (count($images) % 4 == 0 && count($images) > 8) {
            $columns = 4;
        } elseif (count($images) % 3 == 0) {
            $columns = 3;
        } elseif (count($images) % 2 == 0) {
            $columns = 2;
        }

        $result = $this->combineImages($images, $filenameAll, $columns, false, $debug);
        if ($boolShowImage) {
            header('Content-Type:image/png');
            readfile($this->root . $filenameAll);
        }
        return $filenameAll;

        // exit();
        $filename = 'i/matchups/' . $data->hash . '.png';
        try
        {
            unlink($root . $filename);
        } catch (\Throwable $e) {
        }
        $imgBackground->save($root . $filename, 100);
        if ($boolShowImage === true) {
            header('Content-Type:image/png');
            readfile($root . $filename);
        } else {
            return $filename;
        }

    }

    public function getTextWidth($text, $font, $fontSize)
    {
        /* Create a new Imagick object */
        $im = new \Imagick();

        /* Create an ImagickDraw object */
        $draw = new \ImagickDraw();

        /* Set the font */
        $draw->setFont($font);
        $draw->setFontSize($fontSize);

        /* Dump the font metrics, autodetect multiline */
        $info = $im->queryFontMetrics($draw, $text);
        return $info['textWidth'];
    }

    public function combineImages($images, $filename, $columns = 2, $boolShowImage = false, $debug = false)
    {
        $root = public_path() . '/img/ff/';

        if (! is_array($images)) {
            return false;
        }

        if (count($images) == 0) {
            return null;
        }

        $manager = new ImageManager(array(
            'driver' => 'imagick',
        ));

        if (count($images) == 1) {
            $manager->make($images[0])->save($this->root . $filename);
            return $filename;
        }

        $wImg = $manager->make($images[0])->width();
        $w = ($wImg) * $columns;
        $hImg = $manager->make($images[0])->height();
        $h = ceil(count($images) / $columns) * ($hImg);
        $tmpHeight = 0;

        if (count($images) % $columns != 0) {
            $blank = $manager->canvas($wImg, $hImg);
            $blankFileName = 'i/blank' . $wImg . $hImg . '.png';
            $blank->save($root . $blankFileName);
            $images[] = "https://trybot2000.com/img/ff/" . $blankFileName;
        }

        for ($i = 0; $i < count($images); $i++) {
            if (($i % $columns == 0) && ($i > 0)) {
                $tmpHeight += $hImg;
            }

            $stitches[] = ["x" => ($i % $columns * $wImg), "y" => $tmpHeight];
        }
        // dd($stitches);
        if ($debug) {
            echo json_encode($stitches);
            exit();
        }

        $background = $manager->canvas($w, $h);

        foreach ($images as $k => $v) {
            $background
                ->insert($manager->make($v), "top-left", $stitches[$k]['x'], $stitches[$k]['y']);
            $background->rectangle($stitches[$k]['x'], $stitches[$k]['y'], $stitches[$k]['x'] + $wImg, $stitches[$k]['y'] + $hImg, function ($draw) {
                $draw->background('rgba(255, 255, 255, 0)');
                $draw->border(2, '#fff');
            }
            );
        }

        $background->rectangle($stitches[0]['x'], $stitches[0]['y'], $stitches[count($images) - 1]['x'] + ($wImg - 1), $stitches[count($images) - 1]['y'] + $hImg - 1, function ($draw) {
            $draw->background('rgba(255, 255, 255, 0)');
            $draw->border(4, '#fff');
        }
        );
        try
        {
            if (file_exists($this->root . $filename)) {
                unlink($this->root . $filename);
            }
        } catch (\Throwable $e) {
            die("Can't unlink! <br />" . $e);
        }

        $background->save($this->root . $filename, 100);

        if ($boolShowImage) {
            header('Content-Type:image/png');
            readfile($this->root . $filename);
        }

        return $filename;
    }

    public function logToRedis($itemName)
    {
        \Log::info("Logging to redis: $itemName");
        Redis::set("FantasyFootball:log:" . $itemName, time());
    }

    public function getSlackChannelFromLeague($leagueId, $debug = false)
    {
        if ($this->debug) {
            return "slash-tester-bathman"; // G1LKKBAQN
        }
        return config("espn.slack.$leagueId.channelId");
    }

    /**
     *
     *  Deprecated?
     *
     */
    public function login()
    {}
    public function getPickEmStandings()
    {}

    public function getSlackChannelWebhook($channel = null)
    {
        switch ($channel) {
            case 'G1LKZN988':
            // No break
            case 'fantasyfootball':
                return env('SLACK_WEBHOOK_FANTASYFOOTBALL');
                break;

            case "G6HPHCCKW":
            // No break
            case "tryhard-football":
                return env('SLACK_WEBHOOK_FANTASYFOOTBALL');
                break;

            default:
                return env('SLACK_WEBHOOK_TESTCHANNEL');
                break;
        }
    }

}
