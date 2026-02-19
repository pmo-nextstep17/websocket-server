# Iframe Clean Viewer (WordPress plugin)

Plugin WordPress per incorporare un sito esterno in un iframe e coprire visivamente header/footer con due maschere (sopra/sotto).

## Perché non occupa sempre tutto lo spazio?

Sì: è spesso il tema che limita la larghezza (`--wp--style--global--content-size`, `--wp--style--global--wide-size`).
Ora il plugin espone opzioni backend per controllare larghezza e altezza in modo responsive.

## Nuova opzione: forzare altezza frame

Da backend puoi scegliere **Modalità altezza iframe**:

- `fixed` = altezza configurata (`height` desktop + `mobile_height` mobile)
- `fill_viewport` = prova a riempire lo schermo (`100vh` / `100dvh`) sottraendo le maschere top/bottom

## Configurazione backend disponibile

In **Impostazioni > Iframe Clean Viewer**:

- URL predefinito
- Modalità altezza (`fixed` / `fill_viewport`)
- Altezza desktop
- Altezza mobile
- hide header/footer (px)
- border radius
- layout width (`content`, `wide`, `full`)
- custom max-width

## Priorità override

1. Shortcode
2. Widget
3. Default admin

## Shortcode completo

```text
[iframe_clean_viewer url="https://esempio.com" height_mode="fill_viewport" height="85vh" mobile_height="80vh" layout_mode="full" custom_max_width="1400px" hide_top="96" hide_bottom="72" border_radius="16"]
```

Parametri supportati:

- `url`
- `height_mode` (`fixed|fill_viewport`)
- `height`
- `mobile_height`
- `layout_mode` (`content|wide|full`)
- `custom_max_width`
- `hide_top`
- `hide_bottom`
- `border_radius`

## Parametri inviati al dominio remoto

Il plugin aggiunge all'URL iframe:

- `source_domain`
- `hide_header`
- `hide_footer`
- `height_mode`
- `height`
- `mobile_height`
- `layout_mode`
- `custom_max_width`
- `border_radius`

## ZIP installabile

```bash
./scripts/package-iframe-plugin.sh
```

Output:

```text
wordpress-plugin/iframe-clean-viewer.zip
```
