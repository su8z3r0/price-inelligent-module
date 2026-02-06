<?php
declare(strict_types=1);

namespace Cyper\PriceIntelligent\Model\Service;

use Cyper\PriceIntelligent\Api\PriceComparisonsRepositoryInterface;
use Cyper\PriceIntelligent\Model\PriceComparisonsFactory;
use Cyper\PriceIntelligent\Model\ResourceModel\BestSupplierProducts\CollectionFactory as BestSupplierCollectionFactory;
use Cyper\PriceIntelligent\Model\ResourceModel\BestCompetitorPrices\CollectionFactory as BestCompetitorCollectionFactory;
use Cyper\PriceIntelligent\Model\ResourceModel\PriceComparisons as PriceComparisonsResource;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CompetitivenessAnalysisService
{
    public function __construct(
        private readonly BestSupplierCollectionFactory $bestSupplierCollectionFactory,
        private readonly BestCompetitorCollectionFactory $bestCompetitorCollectionFactory,
        private readonly PriceComparisonsFactory $priceComparisonsFactory,
        private readonly PriceComparisonsRepositoryInterface $priceComparisonsRepository,
        private readonly PriceComparisonsResource $priceComparisonsResource,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * Analizza la competitività confrontando i nostri prezzi con quelli dei competitor
     *
     * @param OutputInterface|null $output
     * @return array Statistiche analisi
     */
    public function analyze(?OutputInterface $output = null): array
    {
        if ($output) {
            $output->writeln('<comment>Caricamento dati fornitori e competitor...</comment>');
        }

        $supplierProducts = $this->bestSupplierCollectionFactory->create();
        $competitorPrices = $this->bestCompetitorCollectionFactory->create();

        // Indicizza competitor prices per SKU/EAN
        $competitorIndex = [];
        foreach ($competitorPrices as $competitorPrice) {
            $key = $competitorPrice->getEan() ?: $competitorPrice->getNormalizedSku();
            $competitorIndex[$key] = $competitorPrice;
        }

        if ($output) {
            $output->writeln("<comment>Trovati {$supplierProducts->getSize()} prodotti nostri e {$competitorPrices->getSize()} prezzi competitor</comment>");
        }

        // Cancella analisi precedenti
        $connection = $this->priceComparisonsResource->getConnection();
        $connection->truncateTable($connection->getTableName('cyper_price_comparisons'));

        $stats = [
            'total' => 0,
            'competitive' => 0,
            'not_competitive' => 0
        ];

        foreach ($supplierProducts as $supplierProduct) {
            $key = $supplierProduct->getEan() ?: $supplierProduct->getNormalizedSku();
            
            if (!isset($competitorIndex[$key])) {
                continue; // Nessun competitor per questo prodotto
            }

            $competitorPrice = $competitorIndex[$key];
            
            $ourPrice = (float)$supplierProduct->getPrice();
            $theirPrice = (float)$competitorPrice->getSalePrice();
            $difference = $ourPrice - $theirPrice;
            $isCompetitive = $ourPrice <= $theirPrice;
            
            // Calcola percentuale competitività
            $percentage = $theirPrice > 0 ? (($difference / $theirPrice) * 100) : 0;

            try {
                $comparison = $this->priceComparisonsFactory->create();
                $comparison->setData([
                    'sku' => $supplierProduct->getSku(),
                    'ean' => $supplierProduct->getEan(),
                    'normalized_sku' => $supplierProduct->getNormalizedSku(),
                    'product_title' => $supplierProduct->getTitle(),
                    'our_price' => $ourPrice,
                    'competitor_price' => $theirPrice,
                    'price_difference' => $difference,
                    'is_competitive' => $isCompetitive,
                    'competitiveness_percentage' => round($percentage, 2)
                ]);
                $this->priceComparisonsRepository->save($comparison);

                $stats['total']++;
                if ($isCompetitive) {
                    $stats['competitive']++;
                } else {
                    $stats['not_competitive']++;
                }

            } catch (\Exception $e) {
                $this->logger->error('Failed to save price comparison', [
                    'sku' => $supplierProduct->getSku(),
                    'error' => $e->getMessage()
                ]);
            }
        }

        if ($output) {
            $output->writeln('<comment>Analisi completata!</comment>');
        }

        return $stats;
    }
}
