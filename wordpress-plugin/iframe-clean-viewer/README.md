# Iframe Clean Viewer (WordPress plugin)

Plugin WordPress per incorporare un sito esterno in un iframe e coprire visivamente header/footer con due maschere (sopra/sotto).

## Installazione

1. Copia la cartella `iframe-clean-viewer` dentro `wp-content/plugins/`.
2. Attiva il plugin da **Plugin > Plugin installati**.
3. Vai in **Impostazioni > Iframe Clean Viewer** e salva i valori di default.

## Configurazione amministrazione (default globali)

In **Impostazioni > Iframe Clean Viewer** puoi salvare:

- URL predefinito iframe
- Altezza predefinita iframe (consigliato `70vh`)
- Nascondi header (px) predefinito
- Nascondi footer (px) predefinito
- Border radius (px) predefinito

Questi valori vengono usati sia in pagina che nel widget se non specifichi override.

## Responsive di default

Il contenitore iframe è responsive di default:

- larghezza sempre `100%`
- altezza default `70vh`
- adattamento su mobile con media query (altezza e maschere ridotte)

## Priorità / overwrite valori

Ordine di priorità (dal più forte al più debole):

1. **Valori passati nello shortcode** (quando usi una pagina/articolo)
2. **Valori impostati nel singolo widget** (quando usi un widget)
3. **Valori di default amministrativi** salvati in Impostazioni

In pratica: shortcode e widget fanno overwrite dei default admin.

## Uso shortcode (pagina/articolo)

### Uso semplice (usa tutti i default admin)

```text
[iframe_clean_viewer]
```

### Uso con override specifici

```text
[iframe_clean_viewer url="https://esempio.com" height="85vh" hide_top="96" hide_bottom="72" border_radius="16"]
```

### Attributi shortcode

- `url` (opzionale)
- `height` (opzionale)
- `hide_top` (opzionale)
- `hide_bottom` (opzionale)
- `border_radius` (opzionale)

## Uso come widget

Vai in **Aspetto > Widget**, aggiungi il widget **Iframe Clean Viewer** e compila i campi che vuoi sovrascrivere.
I campi lasciati vuoti prendono il default amministrativo.

## Parametri inviati automaticamente al dominio remoto

Quando il plugin costruisce l'URL dell'iframe aggiunge sempre questi query params:

- `source_domain`: dominio del tuo sito WordPress (es. `miosito.it`)
- `hide_header`: valore numerico (px) della maschera top
- `hide_footer`: valore numerico (px) della maschera bottom

Esempio finale generato:

```text
https://dominio-remoto.com/pagina?source_domain=miosito.it&hide_header=80&hide_footer=80
```

## Creare ZIP pronto da installare

Dalla root del progetto esegui:

```bash
./scripts/package-iframe-plugin.sh
```

Troverai il file pronto qui:

```text
wordpress-plugin/iframe-clean-viewer.zip
```

Poi in WordPress vai su **Plugin > Aggiungi nuovo > Carica plugin** e carica lo ZIP.

## Note tecniche

- Il plugin **non modifica il sito remoto**: header/footer vengono nascosti solo visivamente con overlay locali.
- Alcuni siti bloccano l'iframe via header HTTP (`X-Frame-Options` / `CSP frame-ancestors`). In quel caso il contenuto non può essere incorporato.
