<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Requests ;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;

Use App\Tweet;
use App\Http\Controllers\TweetController;

use Phpml\FeatureExtraction\TokenCountVectorizer;
use Phpml\Tokenization\WhitespaceTokenizer;
use Phpml\FeatureExtraction\TfIdfTransformer;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

//Route::get('articles', 'TweetController@index');

Route::get('tweets', function() {
    if(!empty($_GET['start']) && !empty($_GET['end']) && !empty($_GET['lang']) && !empty($_GET['limit'])) {
	if(!empty($_GET['location'])) {
		$data = Tweet::select('json')->whereRaw("(created_at >= ? AND created_at <= ?)", [$_GET['start']." 00:00:00", $_GET['end']." 23:59:59"])->where('json->lang','=',$_GET['lang'])->where('json->user->location','like','%'.$_GET['location'].'%')->skip(0)->take($_GET['limit'])->get();
	} else {
		$data = Tweet::select('json')->whereRaw("(created_at >= ? AND created_at <= ?)", [$_GET['start']." 00:00:00", $_GET['end']." 23:59:59"])->where('json->lang','=',$_GET['lang'])->where('json->user->location','!=','null')->skip(0)->take($_GET['limit'])->get();
	}
	//$data = Tweet::select('json')->whereRaw("(created_at >= ? AND created_at <= ?)", [$_GET['start']." 00:00:00", $_GET['end']." 23:59:59"])->where('json->lang','=',$_GET['lang'])->where('json->user->location','!=','null')->skip(0)->take($_GET['limit'])->get();
    } else {
	$data = Tweet::select('json')->where('json->lang','=','in')->where('json->user->location','!=','null')->skip(0)->take(1000)->get();
    }
    //$data = Tweet::select('json')->where('json->lang','=','in')->where('json->user->location','!=','null')->skip(0)->take(1000)->get();
    $newdata = json_decode($data);
    $stream = array();
    foreach($newdata as $d) {
	$json = json_decode($d->json);
	$stream[] = array(
		'id' => $json->id_str,
		'text' => $json->text,
		'created_at' => $json->created_at,
		'retweet_count' => $json->retweet_count,
		'favorite_count' => $json->favorite_count,
		'lang' => $json->lang,
		'geo' => $json->geo,
		'coordinates' => $json->coordinates,
		'place' => $json->place,
		'user' => array(
                        'id' => $json->user->id,
                        'screen_name' => $json->user->screen_name,
                        'location' => $json->user->location,
                        'verified' => $json->user->verified,
                        'followers_count' => $json->user->followers_count,
                        'friends_count' => $json->user->friends_count,
                        'listed_count' => $json->user->listed_count,
                        'favourites_count' => $json->user->favourites_count,
                        'statuses_count' => $json->user->statuses_count,
                        'profile_image_url' => $json->user->profile_image_url
  	      	),
	);
    }
    return $stream;
});

Route::get('report', function() {
    $start = (empty($_GET['start'])) ? "2021-05-26" : $_GET['start'];
    $end = (empty($_GET['end'])) ? "2021-06-07" : $_GET['end'];
    $limit = (empty($_GET['limit'])) ? "all" : $_GET['limit'];
    $lang = (empty($_GET['lang'])) ? "in" : $_GET['lang'];
    $location = (empty($_GET['location'])) ? "Indonesia" : $_GET['location'];

    if($lang == "all") {
	if($limit == "all") {
    		$data = Tweet::select('json')
			->orderBy('created_at', 'desc')
			->whereRaw("(created_at >= ? AND created_at <= ?)", [$start." 00:00:00", $end." 23:59:59"])
			->where('json->user->location','like','%'.$location.'%')
			->get();
	} else {
		$data = Tweet::select('json')
                	->orderBy('created_at', 'desc')
                	->whereRaw("(created_at >= ? AND created_at <= ?)", [$start." 00:00:00", $end." 23:59:59"])
                	->where('json->user->location','like','%'.$location.'%')
                	->skip(0)->take($limit)
                	->get();
	}
    } else {
	if($limit == "all") {
		$data = Tweet::select('json')
                	->orderBy('created_at', 'desc')
                	->whereRaw("(created_at >= ? AND created_at <= ?)", [$start." 00:00:00", $end." 23:59:59"])
                	->where('json->lang','=',$lang)
                	->where('json->user->location','like','%'.$location.'%')
			->get();
	} else {
		$data = Tweet::select('json')
                	->orderBy('created_at', 'desc')
             		->whereRaw("(created_at >= ? AND created_at <= ?)", [$start." 00:00:00", $end." 23:59:59"])
               	 	->where('json->lang','=',$lang)
                	->where('json->user->location','like','%'.$location.'%')
                	->skip(0)->take($limit)
			->get();
	}	
    }

    //$data = Tweet::select('json')->where('json->lang','=','in')->where('json->user->location','!=','null')->skip(0)->take(1000)->get();
    $newdata = json_decode($data);
    $reach = 0;
    $location = array();
    $user = array();
    $text = array();
    $voc = array();
    $vectorizer = new TokenCountVectorizer(new WhitespaceTokenizer());
    foreach($newdata as $d) {
        $json = json_decode($d->json);
	$reach += $json->user->followers_count;
	$location[] = array(
		'location' => $json->user->location,
		'number_of_tweets' => 1,
		'reach' => $json->user->followers_count
        );
	$user[] = array(
		'screen_name' => strtolower($json->user->screen_name),
		'followers_count' => $json->user->followers_count,
		'profile_image_url' => $json->user->profile_image_url,
		'number_of_tweets' => 1
	);
	/* $active[] = array(
                'screen_name' => strtolower($json->user->screen_name),
                'number_of_tweets' => 1
        );*/

	$created = explode(" ",$json->created_at);
        $new_created = $created[0]." ".$created[1]." ".$created[2]." ".$created[5];

	$popular[] = array(
		'id' => $json->id_str,
                'text' => $json->text,
		'screen_name' => strtolower($json->user->screen_name),
                'reach' => $json->user->followers_count,
		'created_at' => $new_created,
		'profile_image_url' => $json->user->profile_image_url,
		'number_of_tweets' => 1
        );
	/* $latest[] = array(
                'text' => $json->text,
                'screen_name' => strtolower($json->user->screen_name),
                'created_at' => $json->created_at
        );*/
	//$text[] = $json->text;
	//$vectorizer->fit(explode(' ',$json->text));
	//$voc[] = $vectorizer->getVocabulary();
    }

    $resultz = [];
    foreach($user as $v){
        $key = $v['screen_name'];
        if(isset($resultz[$key])){
                $resultz[$key]["followers_count"] = $v["followers_count"];
		$resultz[$key]["number_of_tweets"] += $v["number_of_tweets"];
        } else {
                $resultz[$key] = $v;
        }
    }
    $users = array();
    foreach ($resultz as $key => $row){
        $users[$key] = $row['followers_count'];
    }
    array_multisort($users, SORT_DESC, $resultz);
    $top_user = array_unique($resultz, SORT_REGULAR);

    /* $resultv = [];
    foreach($active as $v){
        $key = $v['screen_name'];
        if(isset($resultv[$key])){
                $resultv[$key]["number_of_tweets"] += $v["number_of_tweets"];
        } else {
                $resultv[$key] = $v;
        }
    }*/
    $actives = array();
    foreach ($resultz as $key => $row){
        $actives[$key] = $row['number_of_tweets'];
    }
    array_multisort($actives, SORT_DESC, $resultz);
    $active_user = array_unique($resultz, SORT_REGULAR);

    //$latest = array_slice($popular, 0 ,10);
    $resulty = [];
    foreach($popular as $v){
        $key = $v['id'];
        if(isset($resulty[$key])){
                $resulty[$key]["reach"] = $v["reach"];
        } else {
                $resulty[$key] = $v;
        }
    }
    $populars = array();
    foreach ($resulty as $key => $row){
        $populars[$key] = $row['reach'];
    }
    array_multisort($populars, SORT_DESC, $resulty);
    $popular_tweet = array_unique($resulty, SORT_REGULAR);

    $resultl = [];
    foreach($popular as $v){
        $key = $v['id'];
        if(isset($resultl[$key])){
                $resultl[$key]["reach"] = $v["reach"];
        } else {
                $resultl[$key] = $v;
        }
    }
    $resultw = [];
    foreach($popular as $v){
        $key = $v['created_at'];
        if(isset($resultw[$key])){
                $resultw[$key]["reach"] += $v["reach"];
		$resultw[$key]["number_of_tweets"] += $v["number_of_tweets"];
        } else {
                $resultw[$key] = $v;
        }
    }
 
    $result = [];
    foreach($location as $v){
    	$key = $v['location'];
    	if(isset($result[$key])){
        	$result[$key]["number_of_tweets"] += $v["number_of_tweets"];
		$result[$key]["reach"] += $v["reach"];
    	} else {
       		$result[$key] = $v;
   	}
    }
    $reachs = array();
    foreach ($result as $key => $row){
        $reachs[$key] = $row['number_of_tweets'];
    }
    array_multisort($reachs, SORT_DESC, $result);
    $top_location = array_unique($result, SORT_REGULAR);

    //$vectorizer->fit($text);
    //$vocabulary = $vectorizer->getVocabulary();

    $stop_words = array('dengan','ada','banget','hari','sama','apa','jadi','gak','saya','aku','gue','dah','udah','dari','tapi','paling','the','i','we','you','what','is','di','ke','rt','aku','bisa','ini','','dan','ya','yg','yang','aja','ga','lagi','kalo'); 
    foreach($popular as $t) {
	$word = explode(' ',$t['text']); 
	foreach($word as $w) {
		$kata = str_replace(' ', '-', $w);
		$kata = preg_replace('/[^A-Za-z0-9\-]/', '', $kata);
		$kata = strtolower(preg_replace('/-+/', '-', $kata));
		if(!in_array($kata,$stop_words)) {
			$voc[] = array(
				'word' => $kata,
				'count'	=> 1
			);
		}
	}
	//$voc[] = array_unique(explode(' ', $t));
    }

    $resultx = [];
    foreach($voc as $v){
        $key = $v['word'];
        if(isset($resultx[$key])){
                $resultx[$key]["count"] += $v["count"];
        } else {
                $resultx[$key] = $v;
        }
    }
    $counts = array();
    foreach ($resultx as $key => $row){
        $counts[$key] = $row['count'];
    }
    array_multisort($counts, SORT_DESC, $resultx);
    $top_word = array_unique($resultx, SORT_REGULAR);

    //$transformer = new TfIdfTransformer($newvoc);
    //$transformer->transform($newvoc);
    $stream = array (
	'number_of_tweets' => count($newdata),
	'number_of_users' => count($resultz),
	'total_reach'	=> $reach,
	'top_location'	=> array_slice($top_location, 0 ,10),
	'top_user' => array_slice($top_user, 0 ,10),
	'active_user' => array_slice($active_user, 0 ,10),
	'popular_tweets' => array_slice($popular_tweet, 0 ,10),
	'latest_tweets' => array_slice($resultl, 0 ,10),
	'top_word' => array_slice($top_word, 0 ,10),
	'per_day' => $resultw,
	//'samples' => $samples,
	//'vocabulary' => $vocabulary
    );
    return $stream;
});


//Route::get('tweets', 'TweetController@index');
