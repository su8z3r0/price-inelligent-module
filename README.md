# Price Intelligent - Magento 2 Module

Modulo Magento 2 per il monitoraggio dei prezzi dei competitor e l'analisi della competitività.

## 📋 Indice

- [Caratteristiche](#caratteristiche)
- [Requisiti](#requisiti)
- [Installazione](#installazione)
- [Configurazione](#configurazione)
- [Utilizzo](#utilizzo)
- [Comandi Console](#comandi-console)
- [Struttura Modulo](#struttura-modulo)
- [API & Estensibilità](#api--estensibilità)

---

## ✨ Caratteristiche

### 🔍 Scraping Competitor
- Scraping automatico prezzi competitor
- Rate limiting configurabile
- Supporto proxy rotation per evitare ban
- Estrazione intelligente EAN (JSON-LD, meta tags, data attributes)
- Retry logic con failover automatico

### 📊 Gestione Fornitori
- Import prodotti da CSV (Local, FTP, HTTP)
- Normalizzazione automatica header CSV
- Matching prodotti tramite SKU/EAN
- Parsing intelligente prezzi (formati EU/US)

### 📈 Analisi Competitività
- Confronto prezzi fornitore vs competitor
- Calcolo differenza e percentuale
- Identificazione best price per SKU/EAN
- Dashboard admin con export CSV

### 🔄 Proxy Rotation
- Rotazione automatica proxy (round-robin/random)
- Health check e failover
- Configurabile via admin
- Retry configurabile

---

## 📦 Requisiti

- **Magento**: 2.4.x
- **PHP**: 8.1+
- **Composer packages**:
  - `symfony/dom-crawler`
  - `symfony/console`

---

## 🚀 Installazione

### 1. Clona il Repository

```bash
cd <magento_root>/app/code
mkdir -p Cyper
cd Cyper
git clone https://github.com/su8z3r0/price-inelligent-module.git PriceIntelligent
```

### 2. Installa Dipendenze

```bash
cd <magento_root>
composer require symfony/dom-crawler
```

### 3. Abilita il Modulo

```bash
php bin/magento module:enable Cyper_PriceIntelligent
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento cache:flush
```

### 4. Crea Directory per CSV

```bash
mkdir -p var/suppliers
chmod 775 var/suppliers
```

---

## ⚙️ Configurazione

### Admin Panel

**Percorso**: `Stores > Configuration > Price Intelligent`

#### Proxy Settings

```
Enabled: Yes/No
Strategy: round_robin | random
Max Retries: 3
Proxies: (formato: url|username|password)
```

**Esempio Configurazione Proxy**:
```
http://proxy1.example.com:8080|user1|pass1
http://proxy2.example.com:8080
socks5://proxy3.example.com:1080|user3|pass3
```

### Database Schema

Il modulo crea automaticamente queste tabelle:

- `cyper_competitors` - Competitor e configurazioni scraping
- `cyper_competitor_prices` - Prezzi scraped
- `cyper_suppliers` - Fornitori
- `cyper_supplier_products` - Prodotti fornitori
- `cyper_price_comparisons` - Analisi competitività

---

## 📖 Utilizzo

### 1. Aggiungi Competitor

**Admin** > **Price Intelligent** > **Competitors** > **Add New**

**Configurazione scraping** (JSON):
```json
{
  "product_urls": [
    "https://competitor.com/product1",
    "https://competitor.com/product2"
  ],
  "selectors": {
    "sku": ".product-sku",
    "title": "h1.product-title",
    "price": ".price-value",
    "ean": "meta[itemprop='gtin13']"
  }
}
```

### 2. Aggiungi Fornitore

**Admin** > **Price Intelligent** > **Suppliers** > **Add New**

**Tipi Sorgente**:

#### Local CSV
```json
{
  "file_path": "fornitore1.csv"
}
```
File va in: `var/suppliers/fornitore1.csv`

#### FTP
```json
{
  "host": "ftp.supplier.com",
  "port": 21,
  "username": "user",
  "password": "pass",
  "remote_path": "/exports/products.csv"
}
```

#### HTTP
```json
{
  "url": "https://supplier.com/feed/products.csv"
}
```

### 3. Formato CSV Fornitore

Il parser normalizza automaticamente gli header. Supporta varie nomenclature:

| Campo Normalizzato | Header Supportati |
|-------------------|-------------------|
| `sku` | sku, codice, cod |
| `title` | titolo_prodotto, titolo, title |
| `price` | prezzo, price, prezzo_vendita |
| `ean` | ean, ean13, barcode |

**Esempio CSV**:
```csv
codice,titolo_prodotto,prezzo,ean
PROD001,Prodotto 1,99.90,1234567890123
PROD002,Prodotto 2,149.50,9876543210987
```

---

## 🖥️ Comandi Console

### Scraping Competitor

```bash
# Scrape tutti i competitor attivi
php bin/magento cyper:crawler:scrape

# Scrape un competitor specifico
php bin/magento cyper:crawler:scrape --competitor=1
```

### Import Fornitori

```bash
# Import tutti i fornitori attivi
php bin/magento cyper:supplier:match

# Import fornitore specifico
php bin/magento cyper:supplier:match --supplier=1
```

### Trova Miglior Prezzo

```bash
# Identifica il miglior prezzo competitor per ogni SKU/EAN
php bin/magento cyper:competitor:find-best
```

### Analisi Competitività

```bash
# Confronta prezzi fornitore vs competitor
php bin/magento cyper:analysis:competitiveness
```

### Cron Jobs (Raccomandato)

Aggiungi in `crontab.xml`:
```xml
<group id="default">
    <job name="cyper_scrape_competitors" instance="Cyper\PriceIntelligent\Cron\ScrapeCompetitors" method="execute">
        <schedule>0 2 * * *</schedule>
    </job>
    <job name="cyper_import_suppliers" instance="Cyper\PriceIntelligent\Cron\ImportSuppliers" method="execute">
        <schedule>0 3 * * *</schedule>
    </job>
    <job name="cyper_analyze_competitiveness" instance="Cyper\PriceIntelligent\Cron\AnalyzeCompetitiveness" method="execute">
        <schedule>0 4 * * *</schedule>
    </job>
</group>
```

---

## 🏗️ Struttura Modulo

```
Cyper/PriceIntelligent/
├── Api/
│   ├── CrawlerInterface.php          # Interfaccia scraping
│   ├── ParserInterface.php            # Interfaccia parser CSV
│   ├── PriceParserInterface.php       # Interfaccia parsing prezzi
│   └── ProxyRotatorInterface.php      # Interfaccia proxy rotation
│
├── Console/Command/
│   ├── CrawlerScrapeCommand.php       # Comando scraping
│   ├── SupplierMatchCommand.php       # Comando import fornitori
│   ├── CompetitorFindBestCommand.php  # Trova best price
│   └── AnalysisCompetitivenessCommand.php
│
├── Model/
│   ├── Competitor.php                 # Model competitor
│   ├── Supplier.php                   # Model fornitore
│   ├── ParserFactory.php              # Factory parser (estensibile via di.xml)
│   │
│   ├── Parser/
│   │   ├── LocalParser.php            # Parser CSV locale
│   │   ├── FtpParser.php              # Parser FTP
│   │   └── HttpParser.php             # Parser HTTP
│   │
│   └── Service/
│       ├── Crawler.php                # Servizio scraping con proxy
│       ├── PriceParser.php            # Parsing prezzi multi-formato
│       ├── ProxyPool.php              # Gestione pool proxy
│       ├── ProxyRotator.php           # Rotazione proxy
│       ├── SupplierImportService.php  # Import fornitori
│       └── CompetitivenessAnalysisService.php
│
├── etc/
│   ├── module.xml                     # Definizione modulo
│   ├── di.xml                         # Dependency Injection
│   ├── config.xml                     # Configurazioni default (proxy)
│   ├── db_schema.xml                  # Schema database
│   │
│   └── adminhtml/
│       ├── routes.xml                 # Routes admin
│       ├── menu.xml                   # Menu admin
│       └── system.xml                 # Configurazioni admin
│
└── view/adminhtml/
    ├── layout/
    │   ├── competitors_competitors_index.xml
    │   ├── competitor_prices_competitorprices_index.xml
    │   └── price_comparisons_pricecomparisons_index.xml
    │
    └── ui_component/
        ├── cyper_competitors_listing.xml
        ├── cyper_competitor_prices_listing.xml
        └── cyper_price_comparisons_listing.xml
```

---

## 🔌 API & Estensibilità

### Aggiungere un Nuovo Parser

**1. Crea la classe Parser**:
```php
<?php
namespace Vendor\Module\Model\Parser;

use Cyper\PriceIntelligent\Api\ParserInterface;

class CustomParser implements ParserInterface
{
    public function parse(array $config): array
    {
        // Implementazione custom
        return $products;
    }
    
    public function getType(): string
    {
        return 'custom';
    }
}
```

**2. Registra in `di.xml`**:
```xml
<type name="Cyper\PriceIntelligent\Model\ParserFactory">
    <arguments>
        <argument name="parsers" xsi:type="array">
            <item name="custom" xsi:type="object">Vendor\Module\Model\Parser\CustomParser</item>
        </argument>
    </arguments>
</type>
```

### Override Crawler

```xml
<preference for="Cyper\PriceIntelligent\Api\CrawlerInterface" 
            type="Vendor\Module\Model\MyCrawler"/>
```

---

## 🐛 Troubleshooting

### Errore: "Cannot instantiate interface"

```bash
rm -rf generated/code/* generated/metadata/*
php bin/magento setup:di:compile
```

### Proxy non funzionano

1. Verifica configurazione in Admin
2. Controlla log: `var/log/system.log`
3. Testa proxy manualmente
4. Aumenta `max_retries`

### CSV non viene importato

1. Verifica path: `var/suppliers/<file>`
2. Controlla permessi: `chmod 664 var/suppliers/*.csv`
3. Verifica formato header CSV
4. Controlla log: `var/log/system.log`

---

## 📝 License

Proprietario

## 👥 Contributors

- **Developer**: Cyper Development Team

## 🔗 Links

- **Repository**: https://github.com/su8z3r0/price-inelligent-module
- **Laravel Version**: https://github.com/su8z3r0/price-monitoring-system

---

## 📞 Support

Per supporto tecnico, apri una issue su GitHub.
