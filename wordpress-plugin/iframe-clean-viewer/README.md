# Iframe Clean Viewer (WordPress plugin)

Plugin WordPress per incorporare un sito esterno in un iframe e coprire visivamente header/footer con due maschere (sopra/sotto).

## Installazione

1. Copia la cartella `iframe-clean-viewer` dentro `wp-content/plugins/`.
2. Attiva il plugin da **Plugin > Plugin installati**.
3. Vai in **Impostazioni > Iframe Clean Viewer** e salva l'URL predefinito.

## Configurazione amministrazione

Nella pagina **Impostazioni > Iframe Clean Viewer** puoi:

- impostare l'URL predefinito da caricare nell'iframe;
- salvare il valore nel database WordPress (`option_name`: `icv_default_url`).

## Dove posso mostrarlo?

Puoi mostrarlo in **entrambi** i modi:

1. **Dentro una pagina/articolo** tramite shortcode.
2. **Dentro un widget** da Aspetto > Widget (widget "Iframe Clean Viewer").

## Uso shortcode (pagina/articolo)

### Uso semplice (prende URL salvato in admin)

```text
[iframe_clean_viewer]
```

### Uso con override URL manuale

```text
[iframe_clean_viewer url="https://esempio.com" height="85vh" hide_top="96" hide_bottom="72" border_radius="16"]
```

### Attributi

- `url` (opzionale): se lo passi, ha priorità su quello salvato in amministrazione.
- `height` (default `80vh`): altezza contenitore.
- `hide_top` (default `80`): altezza maschera superiore in px.
- `hide_bottom` (default `80`): altezza maschera inferiore in px.
- `border_radius` (default `12`): arrotondamento angoli in px.

## Uso come widget

Vai in **Aspetto > Widget**, aggiungi il widget **Iframe Clean Viewer** nell'area desiderata (sidebar/footer ecc.) e configura:

- Titolo (opzionale)
- URL (opzionale, se vuoto usa quello salvato in Impostazioni)
- Altezza iframe
- Maschera top/bottom
- Border radius


## Parametri inviati automaticamente al dominio remoto

Quando il plugin costruisce l'URL dell'iframe aggiunge sempre questi query params:

- `source_domain`: dominio del tuo sito WordPress (es. `miosito.it`)
- `hide_header`: valore numerico (px) della maschera top
- `hide_footer`: valore numerico (px) della maschera bottom

Esempio finale generato:

```text
https://dominio-remoto.com/pagina?source_domain=miosito.it&hide_header=80&hide_footer=80
```

Sul dominio remoto puoi leggere questi parametri in backend/frontend e decidere come comportarti (es. nascondere header/footer server-side o client-side).

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
