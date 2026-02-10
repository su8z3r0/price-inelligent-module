# Price Intelligent - Modulo Magento 2

Modulo avanzato per il monitoraggio dei prezzi dei competitor, l'importazione dei listini fornitori e l'analisi della competitività dei prezzi.

## 📋 Indice

- [Caratteristiche Principali](#caratteristiche-principali)
- [Requisiti di Sistema](#requisiti-di-sistema)
- [Installazione](#installazione)
- [Configurazione](#configurazione)
- [Utilizzo](#utilizzo)
  - [Gestione Competitor](#gestione-competitor)
  - [Gestione Fornitori](#gestione-fornitori)
  - [Analisi Prezzi](#analisi-prezzi)
- [Comandi Console](#comandi-console)
- [Struttura e Estensibilità](#struttura-e-estensibilità)

---

## ✨ Caratteristiche Principali

### 🔍 Scraping Competitor
- **Monitoraggio Prezzi**: Estrazione automatica dei prezzi dai siti competitor.
- **Proxy Rotation**: Supporto nativo per rotazione proxy per evitare blocchi IP.
- **Validazione Proattiva**: Verifica automatica dei proxy prima dell'uso (tramite ping paralleli).
- **Estrazione Intelligente**: Supporta selettori CSS, Meta Tags (OpenGraph), JSON-LD e Data Attributes per trovare EAN e Prezzi.
- **Rate Limiting**: Configurazione dei ritardi tra le richieste per simulare il comportamento umano.

### 📊 Gestione Fornitori
- **Importazione Multi-Sorgente**: Supporta file CSV locali, remoti via FTP e HTTP.
- **Mapping Flessibile**: Mappatura personalizzabile delle colonne CSV per ogni fornitore tramite configurazione JSON.
- **Normalizzazione Automatica**: Riconoscimento automatico degli header standard (es. `sku`, `price`, `ean`).

### 📈 Analisi Competitività
- **Confronto Diretto**: Dashboard dedicata per confrontare i tuoi prezzi di acquisto/vendita con i competitor.
- **Best Price**: Identificazione automatica del miglior prezzo sul mercato per SKU.
- **Reportistica**: Visualizzazione immediata della differenza di prezzo e percentuale di competitività.

---

## 📦 Requisiti di Sistema

- **Magento**: 2.4.x
- **PHP**: 8.1 o superiore
- **Librerie Richieste**:
  - `symfony/dom-crawler`: Per il parsing HTML
  - `symfony/css-selector`: Per i selettori CSS

---

## 🚀 Installazione

### 1. Copia i file del modulo
Copia il modulo nella directory `app/code/Cyper/PriceIntelligent`.

### 2. Installa le dipendenze
Esegui il comando composer dalla root di Magento:
```bash
composer require symfony/dom-crawler symfony/css-selector
```

### 3. Abilita il modulo
```bash
php bin/magento module:enable Cyper_PriceIntelligent
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento cache:flush
```

---

## ⚙️ Configurazione

### Configurazione Generale
Vai su **Stores > Configuration > Cyper > Price Intelligent**.

#### Proxy Configuration
Configura qui i proxy per lo scraping:
- **Enable Proxy Rotation**: `Yes` / `No`
- **Manual Proxy List**: Inserisci i proxy nel formato `url|username|password` (uno per riga).
  - Esempio: `http://proxy.example.com:8080|user|pass`
- **Max Latency**: Tempo massimo in ms (opzionale).

---

## 📖 Utilizzo

### Gestione Competitor
1. Vai su **Price Intelligent > Competitors**.
2. Clicca su **Add Competitor**.
3. Compila i dati generali (Nome, Website).
4. Nel campo **Crawler Configuration (JSON)**, inserisci i selettori CSS per lo scraping:
   ```json
   {
       "product_urls": [
           "https://competitor.com/prodotto-1",
           "https://competitor.com/prodotto-2"
       ],
       "selectors": {
           "title": "h1.product-name",
           "price": ".final-price .price",
           "ean": {
               "method": "json_ld",
               "field": "gtin13"
           }
       }
   }
   ```

### Gestione Fornitori
1. Vai su **Price Intelligent > Suppliers**.
2. Clicca su **Add New Supplier**.
3. Scegli il **Source Type**:
   - **Local**: File CSV presente sul server (in `var/suppliers/`).
   - **FTP**: File CSV su server FTP remoto.
   - **HTTP**: File CSV accessibile via URL pubblico.
4. Configura la sorgente nel campo **Source Configuration (JSON)**:

   **Esempio Local (con mapping manuale):**
   ```json
   {
       "path": "listino_fornitore.csv",
       "delimiter": ";",
       "columns": {
           "sku": "codice_articolo",
           "title": "descrizione",
           "price": "prezzo_netto"
       }
   }
   ```
   *Nota: Se `columns` viene omesso, il sistema tenterà di individuare automaticamente le colonne `sku`, `price`, `title`.*

### Analisi Prezzi
1. Vai su **Price Intelligent > Price Comparisons**.
2. Qui vedrai una tabella con il confronto tra il tuo "Nostro Prezzo" (dal fornitore importato) e il "Prezzo Competitor" (dallo scraping).
3. La colonna **Competitivo** indica se il tuo prezzo è uguale o inferiore a quello del competitor.

---

## 🖥️ Comandi Console

Il modulo fornisce diversi comandi CLI per eseguire le operazioni manualmente o via cron.

### 1. Scraping Competitor
Esegue lo scraping dei prezzi per i competitor attivi.
```bash
php bin/magento cyper:crawler:scrape
# Opzionale: --competitor=<id> per scrapare un solo competitor
```

### 2. Import Fornitori
Scarica e importa i listini dai fornitori configurati.
```bash
php bin/magento cyper:supplier:match
# Opzionale: --supplier=<id> per importare un solo fornitore
```

### 3. Calcolo Best Price
Analizza i prezzi importati e determina il miglior prezzo competitor per ogni SKU.
```bash
php bin/magento cyper:competitor:find-best
```

### 4. Analisi Competitività
Genera il report di confronto prezzi.
```bash
php bin/magento cyper:analysis:competitiveness
```

---

## 🏗️ Struttura e Estensibilità

Il modulo è progettato per essere facilmente estensibile.

### Aggiungere un nuovo Parser
È possibile implementare nuovi parser per sorgenti dati diverse (es. API JSON, XML, SOAP) implementando l'interfaccia `Cyper\PriceIntelligent\Api\ParserInterface` e registrandoli in `di.xml`.

### Cron Jobs
Per automatizzare il processo, si consiglia di aggiungere i comandi al crontab di sistema o configurare i cron group di Magento.

Esempio `crontab.xml` (da implementare nel proprio tema/modulo se si desidera automazione nativa):
```xml
<job name="cyper_scrape_competitors" instance="Cyper\PriceIntelligent\Cron\ScrapeCompetitors" method="execute">
    <schedule>0 2 * * *</schedule> <!-- Ogni notte alle 02:00 -->
</job>
```
