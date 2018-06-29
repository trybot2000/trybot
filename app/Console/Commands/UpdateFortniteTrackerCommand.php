<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Ixudra\Curl\Facades\Curl;

class UpdateFortniteTrackerCommand extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'fortnite:update';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Update stats for users on FortniteTracker.com';

  public $curl;

  /**
   * Create a new command instance.
   *
   * @return void
   */
  public function __construct()
  {
    parent::__construct();
    $this->curl = new Curl();
  }

  /**
   * Execute the console command.
   *
   * @return mixed
   */
  public function handle()
  {
    $users = [
      'jakebathman' => ['psn','pc'], 
      'GTfanat1c2010' => ['psn','pc'], 
      'scnoi' => ['psn','pc'], 
      'supertravtastic1' => ['psn','pc'], 
      'roboknees16' => ['psn','pc'], 
      'originalhavoc95' => ['psn','pc'], 
    ];

    foreach($users as $user => $platforms){
      foreach($platforms as $platform){
        \Log::info("Updating Fortnite stats for $user on $platform");

        $response = Curl::to("https://fortnitetracker.com/profile/$platform/$user")
          ->withTimeout(15)
          ->allowRedirect()
          ->returnResponseObject()
          ->get();

        \Log::info("Result: " . $response->status);

      }
    }
  }
}
