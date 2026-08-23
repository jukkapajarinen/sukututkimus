#!/usr/bin/env node
// Creates smaller, web-upload-friendly copies of the scanned PDFs in books/,
// for manual upload to an AI chat web app. Downsamples images to 150dpi and
// recompresses as JPEG using Ghostscript. Books longer than PAGES_PER_PART
// are split into multiple part files, since even compressed a 900-page book
// is still too large to upload as one file.
//
// Requires the system "gs" (Ghostscript) and "pdfinfo" (poppler-utils) binaries.
// Usage: node tools/shrink-pdfs.js
// Safe to re-run: skips outputs that already exist and are valid PDFs.

const fs = require("node:fs");
const path = require("node:path");
const { spawnSync } = require("node:child_process");

const BOOKS_DIR = path.join(__dirname, "..", "books");
const PAGES_PER_PART = 60;
const RESOLUTION = 150;
const JPEGQ = 65;

function findPdfs(dir) {
  const out = [];
  for (const entry of fs.readdirSync(dir, { withFileTypes: true })) {
    const full = path.join(dir, entry.name);
    if (entry.isDirectory()) {
      out.push(...findPdfs(full));
    } else if (/\.pdf$/i.test(entry.name) && !/-small(-part\d+)?\.pdf$/i.test(entry.name)) {
      out.push(full);
    }
  }
  return out;
}

function pageCount(pdfPath) {
  const res = spawnSync("pdfinfo", [pdfPath], { encoding: "utf8" });
  const match = res.stdout.match(/^Pages:\s+(\d+)/m);
  if (!match) throw new Error(`Could not read page count for ${pdfPath}`);
  return parseInt(match[1], 10);
}

function isValidPdf(pdfPath) {
  if (!fs.existsSync(pdfPath)) return false;
  const res = spawnSync("pdfinfo", [pdfPath], { encoding: "utf8" });
  return res.status === 0;
}

function compressRange(src, out, first, last) {
  const args = [
    "-sDEVICE=pdfwrite",
    "-dCompatibilityLevel=1.4",
    "-dNOPAUSE",
    "-dBATCH",
    "-dQUIET",
    `-dFirstPage=${first}`,
    `-dLastPage=${last}`,
    `-dColorImageResolution=${RESOLUTION}`,
    `-dGrayImageResolution=${RESOLUTION}`,
    `-dMonoImageResolution=${RESOLUTION}`,
    "-dDownsampleColorImages=true",
    "-dDownsampleGrayImages=true",
    "-dDownsampleMonoImages=true",
    "-dColorImageDownsampleType=/Bicubic",
    "-dGrayImageDownsampleType=/Bicubic",
    "-dAutoFilterColorImages=false",
    "-dColorImageFilter=/DCTEncode",
    `-dJPEGQ=${JPEGQ}`,
    "-dAutoFilterGrayImages=false",
    "-dGrayImageFilter=/DCTEncode",
    `-sOutputFile=${out}`,
    src,
  ];
  const res = spawnSync("gs", args, { stdio: "inherit" });
  if (res.status !== 0) {
    fs.rmSync(out, { force: true });
    throw new Error(`gs failed (${res.status}) on ${src} pages ${first}-${last}`);
  }
}

function processBook(src) {
  const base = src.replace(/\.pdf$/i, "");
  const pages = pageCount(src);

  if (pages <= PAGES_PER_PART) {
    const out = `${base}-small.pdf`;
    if (isValidPdf(out)) {
      console.log(`${out}: already done, skipping`);
      return;
    }
    console.log(`Compressing ${src} (${pages} pages)...`);
    compressRange(src, out, 1, pages);
    return;
  }

  const parts = Math.ceil(pages / PAGES_PER_PART);
  for (let p = 1; p <= parts; p++) {
    const first = (p - 1) * PAGES_PER_PART + 1;
    const last = Math.min(p * PAGES_PER_PART, pages);
    const out = `${base}-small-part${String(p).padStart(2, "0")}.pdf`;
    if (isValidPdf(out)) {
      console.log(`${out}: already done, skipping`);
      continue;
    }
    console.log(`Compressing ${src} part ${p}/${parts} (pages ${first}-${last})...`);
    compressRange(src, out, first, last);
  }
}

for (const src of findPdfs(BOOKS_DIR)) {
  processBook(src);
}
