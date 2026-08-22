#!/usr/bin/env bash
# Series: I_H_MUUT_LUETTELOT
# Archive item (aineistoId): 1298503012
# Source: astia.narc.fi (Kansallisarkisto)

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
SERIES_DIR="$SCRIPT_DIR/../books/I_H_MUUT_LUETTELOT"
mkdir -p "$SERIES_DIR"

TMP_DIR="$(mktemp -d)"
cd "$TMP_DIR"

curl -o '1.jpg' 'https://astia.narc.fi/uusiastia/ws/getFile.php?fileId=9171357170&aineistoId=1298503012';
curl -o '2.jpg' 'https://astia.narc.fi/uusiastia/ws/getFile.php?fileId=9171357177&aineistoId=1298503012';
curl -o '3.jpg' 'https://astia.narc.fi/uusiastia/ws/getFile.php?fileId=9171357184&aineistoId=1298503012';
curl -o '4.jpg' 'https://astia.narc.fi/uusiastia/ws/getFile.php?fileId=9171357190&aineistoId=1298503012';
curl -o '5.jpg' 'https://astia.narc.fi/uusiastia/ws/getFile.php?fileId=9171357196&aineistoId=1298503012';
curl -o '6.jpg' 'https://astia.narc.fi/uusiastia/ws/getFile.php?fileId=9171357202&aineistoId=1298503012';
curl -o '7.jpg' 'https://astia.narc.fi/uusiastia/ws/getFile.php?fileId=9171357208&aineistoId=1298503012';
curl -o '8.jpg' 'https://astia.narc.fi/uusiastia/ws/getFile.php?fileId=9171357214&aineistoId=1298503012';

img2pdf $(ls -1 *.jpg | sort -V) -o "$SERIES_DIR/1298503012.pdf"
cd - > /dev/null
rm -rf "$TMP_DIR"
