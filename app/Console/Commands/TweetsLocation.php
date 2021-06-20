<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
Use App\Tweet;

class TweetsLocation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tweets:location';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
	$data = Tweet::select('json')
                ->orderBy('created_at', 'desc')
		->where('places','=',NULL)
                ->skip(0)->take(100)
                ->get();
    	$newdata = json_decode($data);
   	foreach($newdata as $d) {
        	$json = json_decode($d->json);
		$places = isset($json->user->location) ? $json->user->location : "-";
		Tweet::where('id','=',$json->id_str)->update(array('places' => $places));
		var_dump($json->id_str);
                //'location' => $json->user->location,
        }
    }
}
