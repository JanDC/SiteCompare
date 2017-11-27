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
use Symfony\Component\Filesystem\Filesystem;
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

        $fileSystem = new Filesystem();

        $baseUrl = $input->getOption('base_url');

        if (is_null($baseUrl)) {
            $baseUrl = $helper->ask($input, $output, new Question('What is the base url of the site?', null));
        }

        $titleselector = $input->getOption('title_selector');

        if (is_null($titleselector)) {
            $titleselector = $helper->ask($input, $output, new Question('Which is the css selector of the productname/title?', null));
        }

        $priceselector = $input->getOption('price_selector');
        if (is_null($priceselector)) {
            $priceselector = $helper->ask($input, $output, new Question('Which is the css selector of the price of said product?', null));
        }

        $outputFile = $input->getOption('outputFile');
        if (is_null($outputFile)) {
            $outputFile = $helper->ask($input, $output, new Question('Whats the desired filename of the results?', null));
        }

        if (!$fileSystem->exists($outputFile)) {
            $fileSystem->touch($outputFile);
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