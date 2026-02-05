<?php
declare(strict_types=1);

namespace Cyper\PriceIntelligent\Console\Command;

use Cyper\PriceIntelligent\Model\Service\CompetitivenessAnalysisService;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Console\Cli;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AnalysisCompetitivenessCommand extends Command
{
    public function __construct(
        private readonly CompetitivenessAnalysisService $analysisService,
        private readonly State $state,
        private readonly LoggerInterface $logger,
        string $name = null
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->setName('cyper:analysis:competitiveness');
        $this->setDescription('Analizza la competitività dei prezzi confrontando fornitori e competitor');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $this->state->setAreaCode(Area::AREA_ADMINHTML);
        } catch (\Exception $e) {
            // Area già impostata
        }

        $output->writeln('<info>Inizio analisi competitività...</info>');

        try {
            $result = $this->analysisService->analyze($output);
            
            $output->writeln('');
            $output->writeln('<info>=== Riepilogo Analisi ===</info>');
            $output->writeln("<info>Prodotti analizzati: {$result['total']}</info>");
            $output->writeln("<info>Competitivi: {$result['competitive']}</info>");
            $output->writeln("<error>Non competitivi: {$result['not_competitive']}</error>");
            
            if ($result['competitive'] > 0) {
                $percentage = round(($result['competitive'] / $result['total']) * 100, 2);
                $output->writeln("<info>Percentuale competitività: {$percentage}%</info>");
            }
            
            return Cli::RETURN_SUCCESS;
            
        } catch (\Exception $e) {
            $output->writeln("<error>Errore durante l'analisi: {$e->getMessage()}</error>");
            $this->logger->error('Competitiveness analysis failed', ['error' => $e->getMessage()]);
            return Cli::RETURN_FAILURE;
        }
    }
}
