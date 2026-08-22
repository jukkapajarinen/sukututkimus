#!/usr/bin/env bash
# Series: I_B_MUUTTANEIDEN_LUETTELOT_(1878-1919)
# Archive item (aineistoId): 7824655331
# Source: astia.narc.fi (Kansallisarkisto)

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
SERIES_DIR="$SCRIPT_DIR/../books/I_B_MUUTTANEIDEN_LUETTELOT_(1878-1919)"
mkdir -p "$SERIES_DIR"

TMP_DIR="$(mktemp -d)"
cd "$TMP_DIR"

curl -o '1.jpg' 'https://astia.narc.fi/uusiastia/ws/getFile.php?fileId=9158050396&aineistoId=7824655331';
curl -o '2.jpg' 'https://astia.narc.fi/uusiastia/ws/getFile.php?fileId=9158050399&aineistoId=7824655331';
curl -o '3.jpg' 'https://astia.narc.fi/uusiastia/ws/getFile.php?fileId=9158050402&aineistoId=7824655331';
curl -o '4.jpg' 'https://astia.narc.fi/uusiastia/ws/getFile.php?fileId=9158050405&aineistoId=7824655331';
curl -o '5.jpg' 'https://astia.narc.fi/uusiastia/ws/getFile.php?fileId=9158050408&aineistoId=7824655331';
curl -o '6.jpg' 'https://astia.narc.fi/uusiastia/ws/getFile.php?fileId=9158050411&aineistoId=7824655331';
curl -o '7.jpg' 'https://astia.narc.fi/uusiastia/ws/getFile.php?fileId=9158050414&aineistoId=7824655331';
curl -o '8.jpg' 'https://astia.narc.fi/uusiastia/ws/getFile.php?fileId=9158050417&aineistoId=7824655331';
curl -o '9.jpg' 'https://astia.narc.fi/uusiastia/ws/getFile.php?fileId=9158050420&aineistoId=7824655331';
curl -o '10.jpg' 'https://astia.narc.fi/uusiastia/ws/getFile.php?fileId=9158050423&aineistoId=7824655331';
curl -o '11.jpg' 'https://astia.narc.fi/uusiastia/ws/getFile.php?fileId=9158050426&aineistoId=7824655331';
curl -o '12.jpg' 'https://astia.narc.fi/uusiastia/ws/getFile.php?fileId=9158050429&aineistoId=7824655331';
curl -o '13.jpg' 'https://astia.narc.fi/uusiastia/ws/getFile.php?fileId=9158050432&aineistoId=7824655331';
curl -o '14.jpg' 'https://astia.narc.fi/uusiastia/ws/getFile.php?fileId=9158050435&aineistoId=7824655331';
curl -o '15.jpg' 'https://astia.narc.fi/uusiastia/ws/getFile.php?fileId=9158050438&aineistoId=7824655331';
curl -o '16.jpg' 'https://astia.narc.fi/uusiastia/ws/getFile.php?fileId=9158050441&aineistoId=7824655331';
curl -o '17.jpg' 'https://astia.narc.fi/uusiastia/ws/getFile.php?fileId=9158050444&aineistoId=7824655331';
curl -o '18.jpg' 'https://astia.narc.fi/uusiastia/ws/getFile.php?fileId=9158050447&aineistoId=7824655331';
curl -o '19.jpg' 'https://astia.narc.fi/uusiastia/ws/getFile.php?fileId=9158050450&aineistoId=7824655331';
curl -o '20.jpg' 'https://astia.narc.fi/uusiastia/ws/getFile.php?fileId=9158050450&aineistoId=7824655331';

img2pdf $(ls -1 *.jpg | sort -V) -o "$SERIES_DIR/7824655331.pdf"
cd - > /dev/null
rm -rf "$TMP_DIR"
