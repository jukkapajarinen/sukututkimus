#!/usr/bin/env node
// Transcribes a scanned church-book PDF (handwritten Finnish/Russian) using
// the OpenAI API (ChatGPT). The whole PDF is uploaded and transcribed in one
// call - OpenAI extracts page images from the PDF itself.
//
// Usage: node --env-file=.env tools/transcribe.js <pdf-file>
// (put OPENAI_API_KEY=sk-... in .env, or set it in the environment directly)
// Output goes to "<pdf-file>-gpt.txt" next to the PDF.

const fs = require("node:fs");
const path = require("node:path");

// Cheaper alternatives: "gpt-5.6-terra" (balanced), "gpt-5.6-luna" (cheapest).
const MODEL = "gpt-5.6";

const PROMPT = `
You are transcribing a scan from a 19th-20th century Finnish Orthodox parish register ("kirkonkirja" / church book).

The image may show one page or a two-page spread. Some scans additionally include a modern printed Finnish archive reference card (with a barcode, and words like "Tekninen", "Taipaleen ortodoksisen seurakunnan arkisto") - this card is a modern archival label, NOT part of the original register. If such a card appears, ignore it and do not transcribe it.

Transcribe the handwritten register content. It is written by hand, mixing two scripts within the same page or even the same entry:
- Old-style Finnish cursive handwriting
- Russian (Cyrillic) cursive handwriting

Rules:
- Transcribe each word in its original script and language. Do NOT translate anything.
- Preserve line breaks and the reading order of entries as closely as possible.
- Pay close attention to personal names, place names, dates, and numbers - this is genealogical record data and accuracy on these matters most.
- If a word or number is uncertain, give your best reading followed by [?]. If a word is entirely illegible, write [illegible].
- Before the transcription of each PDF page, output a heading line "[Sivu <numero>]", where <numero> is that page's number in the uploaded PDF file (starting at 1). Output this heading even for a two-page spread (one heading for the spread's page number) and even for a page you are skipping because it only contains the archival reference card.
- Output only the transcription itself - no summary, no translation, no commentary, no markdown formatting.
`;

const pdfPath = process.argv[2];
if (!pdfPath) {
  console.error("Usage: node transcribe.js <pdf-file>");
  process.exit(1);
}

const apiKey = process.env.OPENAI_API_KEY;
if (!apiKey) {
  console.error("Set OPENAI_API_KEY in the environment.");
  process.exit(1);
}

async function main() {
  const outPath = `${pdfPath.replace(/\.pdf$/i, "")}-gpt.txt`;
  if (fs.existsSync(outPath)) {
    console.log(`${outPath}: already done, skipping`);
    return;
  }

  console.log(`Uploading ${pdfPath}...`);
  const form = new FormData();
  form.append("purpose", "user_data");
  form.append("file", new Blob([fs.readFileSync(pdfPath)]), path.basename(pdfPath));

  const uploadRes = await fetch("https://api.openai.com/v1/files", {
    method: "POST",
    headers: { Authorization: `Bearer ${apiKey}` },
    body: form,
  });
  if (!uploadRes.ok) throw new Error(`File upload failed ${uploadRes.status}: ${await uploadRes.text()}`);
  const { id: fileId } = await uploadRes.json();

  try {
    console.log(`Transcribing with ${MODEL}...`);
    const start = Date.now();

    const res = await fetch("https://api.openai.com/v1/responses", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Authorization: `Bearer ${apiKey}`,
      },
      body: JSON.stringify({
        model: MODEL,
        input: [
          {
            role: "user",
            content: [
              { type: "input_file", file_id: fileId },
              { type: "input_text", text: PROMPT },
            ],
          },
        ],
      }),
    });
    if (!res.ok) throw new Error(`Responses API failed ${res.status}: ${await res.text()}`);
    const data = await res.json();

    fs.writeFileSync(outPath, data.output_text.trim() + "\n");
    console.log(`Done (${((Date.now() - start) / 1000).toFixed(0)}s) -> ${outPath}`);
  } finally {
    await fetch(`https://api.openai.com/v1/files/${fileId}`, {
      method: "DELETE",
      headers: { Authorization: `Bearer ${apiKey}` },
    }).catch(() => {});
  }
}

main().catch((err) => {
  console.error(err.message);
  process.exit(1);
});
