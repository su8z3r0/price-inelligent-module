<?php
declare(strict_types=1);

namespace Cyper\PriceIntelligent\Console\Command;

use Cyper\PriceIntelligent\Model\Competitor;
use Cyper\PriceIntelligent\Model\CompetitorPrices;
use Cyper\PriceIntelligent\Model\ResourceModel\Competitor\CollectionFactory as CompetitorCollectionFactory;
use Cyper\PriceIntelligent\Model\Service\Crawler;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Console\Cli;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CrawlerScrapeCommand extends Command
{
    private const OPTION_COMPETITOR = 'competitor';
    private const RATE_LIMIT_SECONDS = 60;

    public function __construct(
        private readonly Crawler $crawler,
        private readonly CompetitorCollectionFactory $competitorCollectionFactory,
        private readonly CompetitorPrices $competitorPricesFactory,
        private readonly State $state,
        private readonly LoggerInterface $logger,
        string $name = null
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->setName('cyper:crawler:scrape');
        $this->setDescription('Esegue lo scraping dei prezzi dai competitor configurati');
        $this->addOption(
            self::OPTION_COMPETITOR,
            'c',
            InputOption::VALUE_OPTIONAL,
            'ID del competitor specifico da scrapare'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $this->state->setAreaCode(Area::AREA_ADMINHTML);
        } catch (\Exception $e) {
            // Area già impostata
        }

        $output->writeln('<info>Inizio scraping competitor...</info>');
        $output->writeln('<comment>⚠️  Rate limit: 1 richiesta al minuto</comment>');

        $competitorId = $input->getOption(self::OPTION_COMPETITOR);

        if ($competitorId) {
            return $this->scrapeSingleCompetitor((int)$competitorId, $output);
        }

        return $this->scrapeAllCompetitors($output);
    }

    private function scrapeSingleCompetitor(int $competitorId, OutputInterface $output): int
    {
        $collection = $this->competitorCollectionFactory->create();
        $competitor = $collection->getItemById($competitorId);

        if (!$competitor || !$competitor->getId()) {
            $output->writeln("<error>Competitor con ID {$competitorId} non trovato</error>");
            return Cli::RETURN_FAILURE;
        }

        $output->writeln("<info>Scraping: {$competitor->getName()}</info>");

        try {
            $count = $this->scrapeCompetitor($competitor, $output);
            $output->writeln("<info>✓ Scraped {$count} prodotti da {$competitor->getName()}</info>");
            return Cli::RETURN_SUCCESS;
        } catch (\Exception $e) {
            $output->writeln("<error>✗ Errore durante lo scraping di {$competitor->getName()}: {$e->getMessage()}</error>");
            $this->logger->error('Scraping failed', [
                'competitor_id' => $competitorId,
                'error' => $e->getMessage()
            ]);
            return Cli::RETURN_FAILURE;
        }
    }

    private function scrapeAllCompetitors(OutputInterface $output): int
    {
        $collection = $this->competitorCollectionFactory->create();
        $collection->addFieldToFilter('is_active', 1);

        if ($collection->getSize() === 0) {
            $output->writeln('<comment>Nessun competitor attivo trovato</comment>');
            return Cli::RETURN_SUCCESS;
        }

        $results = [];
        foreach ($collection as $competitor) {
            try {
                $count = $this->scrapeCompetitor($competitor, $output);
                $results[] = [
                    'competitor' => $competitor->getName(),
                    'status' => '✓ Success',
                    'products' => $count,
                    'error' => ''
                ];
            } catch (\Exception $e) {
                $results[] = [
                    'competitor' => $competitor->getName(),
                    'status' => '✗ Failed',
                    'products' => '-',
                    'error' => $e->getMessage()
                ];
                $this->logger->error('Scraping failed', [
                    'competitor' => $competitor->getName(),
                    'error' => $e->getMessage()
                ]);
            }
        }

        $this->displayResults($results, $output);
        return Cli::RETURN_SUCCESS;
    }

    private function scrapeCompetitor(Competitor $competitor, OutputInterface $output): int
    {
        $config = $competitor->getCrawlerConfig();
        
        if (!isset($config['product_urls'])) {
            throw new \InvalidArgumentException('Configurazione crawler mancante o invalida');
        }

        $productUrls = $config['product_urls'];
        $count = 0;

        foreach ($productUrls as $url) {
            try {
                $output->writeln("<comment>Scraping: {$url}</comment>");
                
                $productData = $this->crawler->scrapeProduct($config, $url);

                if ($productData) {
                    $competitorPrice = $this->competitorPricesFactory;
                    $competitorPrice->setData([
                        'competitor_id' => $competitor->getId(),
                        'sku' => $productData['ean'] ?? 'UNKNOWN',
                        'ean' => $productData['ean'],
                        'normalized_sku' => $this->normalizeSku($productData['ean'] ?? ''),
                        'product_title' => $productData['product_title'],
                        'sale_price' => $productData['sale_price'],
                        'product_url' => $productData['product_url'],
                        'scraped_at' => date('Y-m-d H:i:s')
                    ]);
                    $competitorPrice->save();
                    $count++;
                }

                // Rate limiting
                $output->writeln('<comment>Attesa 60 secondi...</comment>');
                sleep(self::RATE_LIMIT_SECONDS);

            } catch (\Exception $e) {
                $output->writeln("<error>Errore su {$url}: {$e->getMessage()}</error>");
                continue;
            }
        }

        return $count;
    }

    private function normalizeSku(string $sku): string
    {
        return strtoupper(preg_replace('/[^A-Z0-9]/', '', strtoupper($sku)));
    }

    private function displayResults(array $results, OutputInterface $output): void
    {
        $output->writeln('');
        $output->writeln('<info>=== Riepilogo Scraping ===</info>');
        
        foreach ($results as $result) {
            $output->writeln(sprintf(
                '%-30s | %s | Prodotti: %s %s',
                $result['competitor'],
                $result['status'],
                $result['products'],
                $result['error'] ? "| Errore: {$result['error']}" : ''
            ));
        }
    }
}
