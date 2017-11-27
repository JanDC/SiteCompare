<?php


namespace App\Service;

use Error;
use Exception;
use League\Csv\AbstractCsv;
use League\Csv\Writer;
use Psr\Http\Message\ResponseInterface;
use Spatie\Crawler\CrawlObserver;
use Spatie\Crawler\Url;
use SplTempFileObject;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

class Scraper implements CrawlObserver
{

    /** @var string */
    private $priceSelector;

    /** @var string */
    private $titleSelector;

    /**
     * @var AbstractCsv
     */
    private $csv;

    /** @var string */
    private $outputFile;

    /** @var Filesystem */
    private $fileSystem;

    /** @var int */
    private $depth;

    public function __construct(string $titleSelector, string $priceSelector, string $outputFile, int $depth)
    {
        $this->titleSelector = $titleSelector;
        $this->priceSelector = $priceSelector;
        $this->outputFile = $outputFile;
        $this->csv = Writer::createFromFileObject(new SplTempFileObject());
        $this->fileSystem = new Filesystem();
        $this->depth = $depth;
    }


    /**
     * Called when the crawler will crawl the url.
     *
     * @param Url $url
     *
     * @return void
     */
    public function willCrawl(Url $url)
    {

    }

    /**
     * Called when the crawler has crawled the given url.
     *
     * @param Url                    $url
     * @param ResponseInterface|null $response
     * @param Url                    $foundOnUrl
     *
     * @return void
     */
    public function hasBeenCrawled(Url $url, $response, Url $foundOnUrl = null)
    {
        try {
            $domCrawler = new DomCrawler($response->getBody()->getContents());

            $titleBlock = $domCrawler->filter($this->titleSelector)->extract(['_text']);
            $priceBlock = $domCrawler->filter($this->priceSelector)->extract(['_text']);

            if (count($titleBlock) && count($priceBlock)) {
                $this->csv->insertOne(
                    [
                        'name' => trim(reset($titleBlock)),
                        'price' => trim(reset($priceBlock)),
                    ]
                );
            }

            if ($this->depth > 1) {
                $subprocessDepth = $this->depth - 1;

                $subprocess = "php ./bin/console scraper:scrape --base_url={$url->scheme}{$url->host}{$url->path}";
                $subprocess .= " --title_selector={$this->titleSelector} --price_selector={$this->priceSelector}";
                $subprocess .= " --outputFile={$this->outputFile} --depth={$subprocessDepth}";

                $subCrawler = new Process($subprocess, null, null, null, null);
                $subCrawler->start();
            }


        } catch (Exception $exception) {
            // Carry on
        } catch (Error $error) {
            // Carry on
        }
    }

    /**
     * Called when the crawl has ended.
     *
     * @return void
     */
    public function finishedCrawling()
    {
        $this->fileSystem->appendToFile($this->outputFile, $this->csv->getContent());

    }
}