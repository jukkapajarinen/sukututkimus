#!/usr/bin/env bash

mkdir -p 1298471217_tmp;
cd 1298471217_tmp || exit 1;

curl -o '1.jpg' 'https://astia.narc.fi/uusiastia/ws/getFile.php?fileId=9171141901&aineistoId=1298471217';
curl -o '2.jpg' 'https://astia.narc.fi/uusiastia/ws/getFile.php?fileId=9171141904&aineistoId=1298471217';
curl -o '3.jpg' 'https://astia.narc.fi/uusiastia/ws/getFile.php?fileId=9171141907&aineistoId=1298471217';
curl -o '4.jpg' 'https://astia.narc.fi/uusiastia/ws/getFile.php?fileId=9171141910&aineistoId=1298471217';
curl -o '5.jpg' 'https://astia.narc.fi/uusiastia/ws/getFile.php?fileId=9171141913&aineistoId=1298471217';
curl -o '6.jpg' 'https://astia.narc.fi/uusiastia/ws/getFile.php?fileId=9171141916&aineistoId=1298471217';
curl -o '7.jpg' 'https://astia.narc.fi/uusiastia/ws/getFile.php?fileId=9171141919&aineistoId=1298471217';
curl -o '8.jpg' 'https://astia.narc.fi/uusiastia/ws/getFile.php?fileId=9171141922&aineistoId=1298471217';
curl -o '9.jpg' 'https://astia.narc.fi/uusiastia/ws/getFile.php?fileId=9171141925&aineistoId=1298471217';
curl -o '10.jpg' 'https://astia.narc.fi/uusiastia/ws/getFile.php?fileId=9171141928&aineistoId=1298471217';
curl -o '11.jpg' 'https://astia.narc.fi/uusiastia/ws/getFile.php?fileId=9171141931&aineistoId=1298471217';
curl -o '12.jpg' 'https://astia.narc.fi/uusiastia/ws/getFile.php?fileId=9171141934&aineistoId=1298471217';
curl -o '13.jpg' 'https://astia.narc.fi/uusiastia/ws/getFile.php?fileId=9171141937&aineistoId=1298471217';
curl -o '14.jpg' 'https://astia.narc.fi/uusiastia/ws/getFile.php?fileId=9171141940&aineistoId=1298471217';
curl -o '15.jpg' 'https://astia.narc.fi/uusiastia/ws/getFile.php?fileId=9171141943&aineistoId=1298471217';
curl -o '16.jpg' 'https://astia.narc.fi/uusiastia/ws/getFile.php?fileId=9171141946&aineistoId=1298471217';
curl -o '17.jpg' 'https://astia.narc.fi/uusiastia/ws/getFile.php?fileId=9171141949&aineistoId=1298471217';
curl -o '18.jpg' 'https://astia.narc.fi/uusiastia/ws/getFile.php?fileId=9171141949&aineistoId=1298471217';

img2pdf $(ls -1 *.jpg | sort -V) -o "../1298471217.pdf";
cd ..;
rm -rf 1298471217_tmp;