<?php

namespace App\Command;

use App\Service\Scraper;
use Spatie\Crawler\Crawler;
use Spatie\Crawler\CrawlInternalUrls;
use Spatie\Crawler\EmptyCrawlObserver;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Validator\Constraints\Optional;

class ScrapeCommand extends Command
{
    public $input;
    public $output;

    protected function configure()
    {
        $this
            ->setName('scraper:scrape')
            ->addOption('base_url', 'b', InputOption::VALUE_OPTIONAL)
            ->addOption('title_selector', 'ts', InputOption::VALUE_OPTIONAL)
            ->addOption('price_selector', 'ps', InputOption::VALUE_OPTIONAL)
            ->addOption('outputFile', 'of', InputOption::VALUE_OPTIONAL)
            ->addOption('depth', 'd', InputOption::VALUE_OPTIONAL, '', 3);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');

        if ($input->hasOption('base_url')) {
            $baseUrl = $input->getOption('base_url');
        } else {
            $baseUrl = $helper->ask($input, $output, new Question('What is the base url of the site?', null));
        }

        if ($input->hasOption('title_selector')) {
            $titleselector = $input->getOption('title_selector');
        } else {
            $titleselector = $helper->ask($input, $output, new Question('Which is the css selector of the productname/title?', null));
        }

        if ($input->hasOption('price_selector')) {
            $priceselector = $input->getOption('price_selector');
        } else {
            $priceselector = $helper->ask($input, $output, new Question('Which is the css selector of the price of said product?', null));
        }

        if ($input->hasOption('outputFile')) {
            $outputFile = $input->getOption('outputFile');
        } else {
            $outputFile = $helper->ask($input, $output, new Question('Whats the desired filename of the results?', null));
        }


        Crawler::create()->setCrawlObserver(new EmptyCrawlObserver());

        Crawler::create()
            ->setCrawlObserver(
                new Scraper(
                    $titleselector,
                    $priceselector,
                    $outputFile,
                    $input->getOption('depth')
                )
            )
            ->setMaximumDepth(1)
            ->setCrawlProfile(new CrawlInternalUrls($baseUrl))
            ->startCrawling($baseUrl);

    }
}