#!/usr/bin/env bash

mkdir -p 7824655331_tmp;
cd 7824655331_tmp || exit 1;

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

img2pdf $(ls -1 *.jpg | sort -V) -o "../7824655331.pdf";
cd ..;
rm -rf 7824655331_tmp;