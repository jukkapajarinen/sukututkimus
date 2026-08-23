#!/usr/bin/env node
// Transcribes a scanned church-book PDF (handwritten Finnish/Russian) using a
// local Ollama vision model.
//
// Usage: node transcribe.js <pdf-file>
// Output goes to "<pdf-file>-transcript/page-NNN.{png,md}" next to the PDF.

const { execFileSync } = require("node:child_process");
const fs = require("node:fs");
const path = require("node:path");

const PROMPT = `You are transcribing a scan from a 19th-20th century Finnish Orthodox parish register ("kirkonkirja" / church book).

The image may show one page or a two-page spread. Some scans additionally include a modern printed Finnish archive reference card (with a barcode, and words like "Tekninen", "Taipaleen ortodoksisen seurakunnan arkisto") - this card is a modern archival label, NOT part of the original register. If such a card appears, ignore it and do not transcribe it.

Transcribe the handwritten register content. It is written by hand, mixing two scripts within the same page or even the same entry:
- Old-style Finnish cursive handwriting
- Russian (Cyrillic) cursive handwriting

Rules:
- Transcribe each word in its original script and language. Do NOT translate anything.
- Preserve line breaks and the reading order of entries as closely as possible.
- Pay close attention to personal names, place names, dates, and numbers - this is genealogical record data and accuracy on these matters most.
- If a word or number is uncertain, give your best reading followed by [?]. If a word is entirely illegible, write [illegible].
- Output only the transcription itself - no summary, no translation, no commentary, no markdown formatting.`;

const pdfPath = process.argv[2];
if (!pdfPath) {
  console.error("Usage: node transcribe.js <pdf-file>");
  process.exit(1);
}

async function main() {
  const outDir = `${pdfPath.replace(/\.pdf$/i, "")}-transcript`;
  fs.mkdirSync(outDir, { recursive: true });

  execFileSync("pdftoppm", ["-r", "80", "-png", pdfPath, path.join(outDir, "page")]);
  const pages = fs.readdirSync(outDir).filter((f) => f.endsWith(".png")).sort();

  for (const [i, file] of pages.entries()) {
    const mdPath = path.join(outDir, file.replace(/\.png$/, ".md"));
    const label = `${file} (${i + 1}/${pages.length})`;

    if (fs.existsSync(mdPath)) {
      console.log(`${label}: already done, skipping`);
      continue;
    }

    console.log(`${label}: transcribing...`);
    const start = Date.now();

    const res = await fetch("http://127.0.0.1:11434/api/chat", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        model: "qwen2.5vl:3b",
        messages: [
          { role: "user", content: PROMPT, images: [fs.readFileSync(path.join(outDir, file)).toString("base64")] },
        ],
        stream: true,
        options: { num_ctx: 8192, num_gpu: 0 },
      }),
    });
    if (!res.ok) throw new Error(`Ollama returned ${res.status}: ${await res.text()}`);

    // Streamed rather than one big reply
    const decoder = new TextDecoder();
    let buffer = "";
    let text = "";
    streamLoop: for await (const chunk of res.body) {
      buffer += decoder.decode(chunk, { stream: true });
      let j;
      while ((j = buffer.indexOf("\n")) !== -1) {
        const line = buffer.slice(0, j).trim();
        buffer = buffer.slice(j + 1);
        if (!line) continue;
        const data = JSON.parse(line);
        text += data.message?.content ?? "";
        if (data.done) break streamLoop;
      }
    }

    fs.writeFileSync(mdPath, text.trim() + "\n");
    console.log(`${label}: done (${((Date.now() - start) / 1000).toFixed(0)}s)`);
  }
}

main().catch((err) => {
  console.error(err.message);
  process.exit(1);
});
