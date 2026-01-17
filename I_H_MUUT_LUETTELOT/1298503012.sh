#!/usr/bin/env bash

mkdir -p 1298503012_tmp;
cd 1298503012_tmp || exit 1;

curl -o '1.jpg' 'https://astia.narc.fi/uusiastia/ws/getFile.php?fileId=9171357170&aineistoId=1298503012';
curl -o '2.jpg' 'https://astia.narc.fi/uusiastia/ws/getFile.php?fileId=9171357177&aineistoId=1298503012';
curl -o '3.jpg' 'https://astia.narc.fi/uusiastia/ws/getFile.php?fileId=9171357184&aineistoId=1298503012';
curl -o '4.jpg' 'https://astia.narc.fi/uusiastia/ws/getFile.php?fileId=9171357190&aineistoId=1298503012';
curl -o '5.jpg' 'https://astia.narc.fi/uusiastia/ws/getFile.php?fileId=9171357196&aineistoId=1298503012';
curl -o '6.jpg' 'https://astia.narc.fi/uusiastia/ws/getFile.php?fileId=9171357202&aineistoId=1298503012';
curl -o '7.jpg' 'https://astia.narc.fi/uusiastia/ws/getFile.php?fileId=9171357208&aineistoId=1298503012';
curl -o '8.jpg' 'https://astia.narc.fi/uusiastia/ws/getFile.php?fileId=9171357214&aineistoId=1298503012';

img2pdf $(ls -1 *.jpg | sort -V) -o "../1298503012.pdf";
cd ..;
rm -rf 1298503012_tmp;