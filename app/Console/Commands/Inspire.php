<?php

namespace App\Console\Commands;

use Guzzle\Http\Client;
use Illuminate\Console\Command;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Collection;
use Cache;
use Mail;
use Symfony\Component\DomCrawler\Crawler;

class Inspire extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'inspire';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Display an inspiring quote';

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{
		$url = 'http://www.viagogo.es/Entradas-Festivales/Festivales-Internacionales/Primavera-Sound-Entradas/E-1557979?currency=EUR&lcid=1033';

		$crawler = new Crawler;
		$crawler->addContent((new Client())->get($url)->send()->getBody(true), 'text/html; charset=utf-8');

		$tickets = $crawler->filter('#clientgridtable > table > tbody > tr')->each(function (Crawler $node, $i) {
//			return [
//				'amount' => trim($node->filter('td:nth-child(4)')->text()),
//				'price' => $node->filter('td:nth-child(5) strong')->text(),
//			];
			return $node->filter('td:nth-child(5) strong')->text();
		});

		$tickets = new Collection($tickets);


		$oldTickets = Cache::get('tickets');

		if ($oldTickets and $tickets->diff($oldTickets)->isEmpty()) {
			Mail::raw('new changes on tickets: ' . print_r($tickets, true), function ($message) {
				$message->to('tonioriol@gmail.com')->subject('Changes on PS tickets');
			});
		} else {
			Cache::forever('tickets', $tickets);
		}
		
		dd($tickets);

	}
}
