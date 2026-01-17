#!/usr/bin/env bash

mkdir -p 1298498187_tmp;
cd 1298498187_tmp || exit 1;

curl -o '1.jpg' 'https://astia.narc.fi/uusiastia/ws/getFile.php?fileId=9171410613&aineistoId=1298498187';
curl -o '2.jpg' 'https://astia.narc.fi/uusiastia/ws/getFile.php?fileId=9171410616&aineistoId=1298498187';
curl -o '3.jpg' 'https://astia.narc.fi/uusiastia/ws/getFile.php?fileId=9171410619&aineistoId=1298498187';
curl -o '4.jpg' 'https://astia.narc.fi/uusiastia/ws/getFile.php?fileId=9171410622&aineistoId=1298498187';
curl -o '5.jpg' 'https://astia.narc.fi/uusiastia/ws/getFile.php?fileId=9171410625&aineistoId=1298498187';
curl -o '6.jpg' 'https://astia.narc.fi/uusiastia/ws/getFile.php?fileId=9171410628&aineistoId=1298498187';
curl -o '7.jpg' 'https://astia.narc.fi/uusiastia/ws/getFile.php?fileId=9171410631&aineistoId=1298498187';
curl -o '8.jpg' 'https://astia.narc.fi/uusiastia/ws/getFile.php?fileId=9171410634&aineistoId=1298498187';
curl -o '9.jpg' 'https://astia.narc.fi/uusiastia/ws/getFile.php?fileId=9171410637&aineistoId=1298498187';
curl -o '10.jpg' 'https://astia.narc.fi/uusiastia/ws/getFile.php?fileId=9171410640&aineistoId=1298498187';
curl -o '11.jpg' 'https://astia.narc.fi/uusiastia/ws/getFile.php?fileId=9171410643&aineistoId=1298498187';
curl -o '12.jpg' 'https://astia.narc.fi/uusiastia/ws/getFile.php?fileId=9171410646&aineistoId=1298498187';
curl -o '13.jpg' 'https://astia.narc.fi/uusiastia/ws/getFile.php?fileId=9171410649&aineistoId=1298498187';
curl -o '14.jpg' 'https://astia.narc.fi/uusiastia/ws/getFile.php?fileId=9171410652&aineistoId=1298498187';
curl -o '15.jpg' 'https://astia.narc.fi/uusiastia/ws/getFile.php?fileId=9171410655&aineistoId=1298498187';
curl -o '16.jpg' 'https://astia.narc.fi/uusiastia/ws/getFile.php?fileId=9171410658&aineistoId=1298498187';
curl -o '17.jpg' 'https://astia.narc.fi/uusiastia/ws/getFile.php?fileId=9171410661&aineistoId=1298498187';
curl -o '18.jpg' 'https://astia.narc.fi/uusiastia/ws/getFile.php?fileId=9171410664&aineistoId=1298498187';
curl -o '19.jpg' 'https://astia.narc.fi/uusiastia/ws/getFile.php?fileId=9171410667&aineistoId=1298498187';
curl -o '20.jpg' 'https://astia.narc.fi/uusiastia/ws/getFile.php?fileId=9171410670&aineistoId=1298498187';
curl -o '21.jpg' 'https://astia.narc.fi/uusiastia/ws/getFile.php?fileId=9171410673&aineistoId=1298498187';
curl -o '22.jpg' 'https://astia.narc.fi/uusiastia/ws/getFile.php?fileId=9171410676&aineistoId=1298498187';
curl -o '23.jpg' 'https://astia.narc.fi/uusiastia/ws/getFile.php?fileId=9171410679&aineistoId=1298498187';
curl -o '24.jpg' 'https://astia.narc.fi/uusiastia/ws/getFile.php?fileId=9171410682&aineistoId=1298498187';
curl -o '25.jpg' 'https://astia.narc.fi/uusiastia/ws/getFile.php?fileId=9171410685&aineistoId=1298498187';

img2pdf $(ls -1 *.jpg | sort -V) -o "../1298498187.pdf";
cd ..;
rm -rf 1298498187_tmp;