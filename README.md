# Buddhist Quotes

Un plugin WordPress che aggiunge un widget alla dashboard di amministrazione per mostrare una citazione buddhista presa da `https://buddha-api.com/api/random`.

## Funzionalità

- aggiunge un widget nella schermata principale della dashboard
- esegue una richiesta HTTP a un servizio esterno per recuperare una citazione in formato JSON
- mostra la citazione, l’autore e l’immagine dell’autore se disponibili
- gestisce errori di richiesta, risposte non valide o JSON non corretto
- carica uno stile CSS personalizzato per il widget

## Installazione

1. Copia la cartella `buddhist-quotes` in `wp-content/plugins/`.
2. Vai in `Plugin` nella dashboard di WordPress.
3. Attiva il plugin `Buddhist Quotes`.
4. Apri la dashboard e verifica la presenza del widget `Buddhist Quotes`.

## Uso

Il widget recupera automaticamente una citazione dal servizio esterno quando la dashboard viene caricata.

### URL di API

Il plugin usa le costanti definite in `buddhist-quotes.php`:

- `BQ_API_BASE_URL` = `https://buddha-api.com/api/`
- `BQ_API_MODE` = `random`

## Note tecniche

- la richiesta HTTP utilizza `wp_remote_get()`.
- se la risposta non è un JSON valido o non contiene la proprietà `text`, il plugin mostra un messaggio alternativo.
- lo stile viene caricato solo nella pagina `index.php` dell’admin.

## Licenza

GPL2+
