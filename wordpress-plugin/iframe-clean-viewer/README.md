# Iframe Clean Viewer (WordPress plugin)

Plugin WordPress per incorporare un sito esterno in un iframe e coprire visivamente header/footer con due maschere (sopra/sotto).

## Perché a volte non occupa tutta la larghezza?

Sì, è spesso dovuto al tema WordPress (specie block themes) che imposta limiti come:

- `--wp--style--global--content-size`
- `--wp--style--global--wide-size`

Per questo nel plugin ora puoi scegliere il layout direttamente da backend.

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
- Layout larghezza (`content`, `wide`, `full`)
- Max-width custom (es. `1200px` o `100%`)

## Responsive di default

Il contenitore iframe è responsive di default:

- larghezza responsive
- altezza default `70vh`
- media query mobile con riduzione altezza/maschere

## Priorità / overwrite valori

Ordine di priorità:

1. **Shortcode** (valori passati nello shortcode)
2. **Widget** (valori del singolo widget)
3. **Default amministrativi**

Quindi shortcode/widget fanno overwrite dei default admin.

## Uso shortcode (pagina/articolo)

### Uso semplice (usa default admin)

```text
[iframe_clean_viewer]
```

### Uso con override completi

```text
[iframe_clean_viewer url="https://esempio.com" height="85vh" hide_top="96" hide_bottom="72" border_radius="16" layout_mode="full" custom_max_width="1400px"]
```

### Attributi shortcode

- `url`
- `height`
- `hide_top`
- `hide_bottom`
- `border_radius`
- `layout_mode` (`content|wide|full`)
- `custom_max_width`

## Uso come widget

In **Aspetto > Widget**, widget **Iframe Clean Viewer**:

- puoi impostare gli stessi parametri principali (inclusi `layout_mode` e `custom_max_width`)
- i campi non compilati usano i default amministrativi

## Parametri inviati automaticamente al dominio remoto

Quando il plugin costruisce l'URL dell'iframe aggiunge:

- `source_domain`
- `hide_header`
- `hide_footer`

Esempio:

```text
https://dominio-remoto.com/pagina?source_domain=miosito.it&hide_header=80&hide_footer=80
```

## Creare ZIP pronto da installare

Dalla root del progetto esegui:

```bash
./scripts/package-iframe-plugin.sh
```

File generato:

```text
wordpress-plugin/iframe-clean-viewer.zip
```
