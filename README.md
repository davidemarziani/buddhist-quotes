# Buddhist Quotes

Un plugin WordPress che aggiunge un widget alla dashboard di amministrazione per mostrare una citazione buddhista presa dalla mia API su `https://api.davidemarziani.com/buddhist-quotes/`.

## Funzionalità

- aggiunge un widget nella schermata principale della dashboard
- aggiunge una pagina `Impostazioni > Buddhist Quotes` per configurare la API key
- esegue una richiesta HTTP autenticata al servizio per recuperare una citazione in formato JSON
- mostra la citazione, l’autore e l’immagine dell’autore se disponibili
- gestisce errori di richiesta, risposte non valide o JSON non corretto
- carica uno stile CSS personalizzato per il widget

## Installazione

1. Copia la cartella `buddhist-quotes` in `wp-content/plugins/`.
2. Vai in `Plugin` nella dashboard di WordPress.
3. Attiva il plugin `Buddhist Quotes`.
4. Vai in `Impostazioni > Buddhist Quotes` e inserisci la API key condivisa con `api.davidemarziani.com`.
5. Apri la dashboard e verifica la presenza del widget `Buddhist Quotes`.

## Uso

Il widget recupera automaticamente una citazione dal servizio esterno quando la dashboard viene caricata, inviando la API key nell'header `X-Api-Key`. Senza una chiave configurata, il widget mostra un messaggio con il link alla pagina delle impostazioni invece di eseguire la richiesta.

### URL di API

Il plugin usa la costante definita in `buddhist-quotes.php`:

- `BQ_API_BASE_URL` = `https://api.davidemarziani.com/buddhist-quotes/`

La API key viene invece salvata come opzione di WordPress (`bq_api_key`), non hardcoded nel codice.

## Note tecniche

- la richiesta HTTP utilizza `wp_remote_get()` con header `X-Api-Key`.
- se la risposta non è un JSON valido o non contiene la proprietà `text`, il plugin mostra un messaggio alternativo.
- lo stile viene caricato solo nella pagina `index.php` dell’admin.

## Licenza

GPL2+
