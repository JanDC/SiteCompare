<?php

namespace App\Command;

use App\Service\Scraper;
use Spatie\Crawler\Crawler;
use Spatie\Crawler\CrawlInternalUrls;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class ScrapeCommand extends Command
{
    public $input;
    public $output;

    protected function configure()
    {
        $this
            ->setName('scraper:scrape');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');

        $baseUrl = $helper->ask($input, $output, new Question('What is the base url of the site?', null));
        $titleselector = $helper->ask($input, $output, new Question('Which is the css selector of the productname/title?', null));
        $priceselector = $helper->ask($input, $output, new Question('Which is the css selector of the price of said product?', null));
        $outputFile = $helper->ask($input, $output, new Question('Whats the desired filename of the results?', null));

        Crawler::create()
            ->setCrawlObserver(new Scraper(
                $titleselector,
                $priceselector,
                $outputFile
            ))
            ->setMaximumDepth(3)
            ->setCrawlProfile(new CrawlInternalUrls($baseUrl))
            ->startCrawling($baseUrl);

    }
}