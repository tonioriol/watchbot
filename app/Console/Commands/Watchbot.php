<?php

namespace App\Console\Commands;

use Guzzle\Http\Client;
use Illuminate\Console\Command;
use Symfony\Component\DomCrawler\Crawler;

class Watchbot extends Command {
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'watch:bot';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Watches things';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle() {
		$url = 'http://www.instagram.com/tonioriol';

		$crawler = new Crawler;
		$crawler->addHtmlContent((new Client())->get($url)->send()->getBody());
		
		$instagram = json_decode(str_replace_last(';','', str_replace_first('window._sharedData = ', '', $crawler->filter('body > script:nth-child(4)')->html())));

		$user = $instagram->entry_data->ProfilePage[0]->user;
		$name = $user->username;
		$followers = $user->followed_by->count;
		$id = $user->id;

		$this->comment("$name $followers $id");

		$posts = $instagram->entry_data->ProfilePage[0]->user->media->nodes;

		foreach ($posts as $post) {
			$this->comment($post->likes->count);
			$this->comment($post->comments->count);
			$this->comment($post->display_src);
		}

	}
}