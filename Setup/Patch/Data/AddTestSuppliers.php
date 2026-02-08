<?php
declare(strict_types=1);

namespace Cyper\PriceIntelligent\Setup\Patch\Data;

use Cyper\PriceIntelligent\Model\SupplierFactory;
use Cyper\PriceIntelligent\Model\ResourceModel\Supplier as SupplierResource;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class AddTestSuppliers implements DataPatchInterface
{
    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup,
        private readonly SupplierFactory $supplierFactory,
        private readonly SupplierResource $supplierResource
    ) {
    }

    public function apply()
    {
        $this->moduleDataSetup->startSetup();

        $suppliers = [
            [
                'name' => 'Fornitore Locale Test',
                'source_type' => 'local',
                'source_config' => json_encode([
                    'file_path' => 'supplier_local.csv',
                    'delimiter' => ',',
                    'columns' => [
                        'sku' => 'sku',
                        'title' => 'titolo_prodotto',
                        'price' => 'prezzo'
                    ]
                ]),
                'is_active' => 1
            ],
            [
                'name' => 'Fornitore FTP Test',
                'source_type' => 'ftp',
                'source_config' => json_encode([
                    'host' => 'ftp.example.com',
                    'username' => 'user',
                    'password' => 'pass',
                    'path' => '/supplier_ftp.csv',
                    'delimiter' => ',',
                    'columns' => [
                        'sku' => 'codice',
                        'title' => 'titolo',
                        'price' => 'price'
                    ]
                ]),
                'is_active' => 1
            ],
            [
                'name' => 'Fornitore HTTP Test',
                'source_type' => 'http',
                'source_config' => json_encode([
                    'url' => 'https://example.com/supplier_http.csv',
                    'delimiter' => ',',
                    'columns' => [
                        'sku' => 'cod',
                        'title' => 'title',
                        'price' => 'prezzo_vendita'
                    ]
                ]),
                'is_active' => 1
            ]
        ];

        foreach ($suppliers as $data) {
            $supplier = $this->supplierFactory->create();
            $this->supplierResource->load($supplier, $data['name'], 'name');
            
            if (!$supplier->getId()) {
                $supplier->setData($data);
                $this->supplierResource->save($supplier);
            }
        }

        $this->moduleDataSetup->endSetup();
    }

    public static function getDependencies()
    {
        return [];
    }

    public function getAliases()
    {
        return [];
    }
}
