<?php
use Magento\Framework\App\Bootstrap;

require __DIR__ . '/../../app/bootstrap.php';

$bootstrap = Bootstrap::create(BP, $_SERVER);
$obj = $bootstrap->getObjectManager();

$state = $obj->get(\Magento\Framework\App\State::class);
try {
    $state->setAreaCode('adminhtml');
} catch (\Exception $e) {}

echo "Testing GeoNodeProxyProvider...\n";

try {
    $provider = $obj->get(\Cyper\PriceIntelligent\Model\Service\GeoNodeProxyProvider::class);
    $provider->updateProxies();
    $proxies = $provider->getProxies();
    
    echo "Found " . count($proxies) . " proxies.\n";
    if (count($proxies) > 0) {
        $first = $proxies[0];
        echo "Example proxy: " . $first['url'] . " (" . $first['protocol'] . ")\n";
    }

    $pool = $obj->get(\Cyper\PriceIntelligent\Model\Service\ProxyPool::class);
    echo "ProxyPool Count: " . $pool->getTotalCount() . "\n";

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
