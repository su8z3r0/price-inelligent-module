<?php
declare(strict_types=1);

namespace Cyper\PriceIntelligent\Model\Service;

use Cyper\PriceIntelligent\Api\PriceParserInterface;

class PriceParser implements PriceParserInterface
{
    /**
     * Parse price from text string
     * Handles European (1.200,50) and American (1,200.50) formats
     *
     * @param string $priceText
     * @return float
     */
    public function parse(string $priceText): float
    {
        // Remove currency symbols and whitespace
        $clean = preg_replace('/[^0-9,.]/', '', $priceText);
        
        if (empty($clean)) {
            return 0.0;
        }

        // Detect format based on comma/dot positions
        $lastCommaPos = strrpos($clean, ',');
        $lastDotPos = strrpos($clean, '.');
        
        // European format: 1.200,50 (dot as thousands separator, comma as decimal)
        if ($lastCommaPos !== false && $lastCommaPos > $lastDotPos) {
            $clean = str_replace('.', '', $clean); // Remove thousands separator
            $clean = str_replace(',', '.', $clean); // Convert decimal separator
        }
        // American format: 1,200.50 (comma as thousands separator, dot as decimal)
        elseif ($lastDotPos !== false && $lastDotPos > $lastCommaPos) {
            $clean = str_replace(',', '', $clean); // Remove thousands separator
        }
        // Only comma present: could be decimal (12,50) or thousands (1,200)
        elseif ($lastCommaPos !== false && $lastDotPos === false) {
            // If comma is in last 3 positions, it's likely a decimal separator
            if (strlen($clean) - $lastCommaPos <= 3) {
                $clean = str_replace(',', '.', $clean);
            } else {
                $clean = str_replace(',', '', $clean);
            }
        }

        return (float) $clean;
    }
}
