<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
Use App\Tweet;

class TweetsUnique extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tweets:unique';

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
	$data = Tweet::select('id','idx')
                ->orderBy('created_at', 'desc')
		->where('check_id', '0')
                ->skip(0)->take(100)
                ->get();
	foreach($data as $d) {
		$newdata = Tweet::select('id','idx')->where('id',$d['id'])->get();
		if(count($newdata) > 1) {
			//$dontDelete = Tweet::where('id', $d['id'])->first();
			Tweet::where('id', $d['id'])->where('idx', '!=', $newdata[0]['idx'])->delete();
			//var_dump($dontDelete['id']);
			//var_dump($dontDelete['idx']);
			var_dump($newdata[0]['idx']);
			var_dump($newdata[0]['id']);
		}
		Tweet::where('id','=',$d['id'])->update(array('check_id' => '1'));
	}
	//var_dump($data);
    }
}
