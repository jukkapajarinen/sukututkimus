<?php
// Run with: php -S localhost:8000
// (this page's transcription save endpoint needs PHP to actually execute,
// so it must be served through PHP's built-in server, not opened as a plain file)

// books.json is the single source of truth: {"<id>": {category, dir, title, pages}}.
// Transcriptions (gpt/mine/digi) live separately, one books/<dir>/<id>.json per book.
$BOOKS_META_FILE = __DIR__ . "/books.json";

function loadBookMeta($path) {
  if (!file_exists($path)) return [];
  $data = json_decode(file_get_contents($path), true);
  return is_array($data) ? $data : [];
}

// Sets (or, if $value is empty, removes) one metadata key for a book in books.json.
function saveBookMeta($path, $id, $key, $value) {
  $meta = loadBookMeta($path);
  if ($value === "" || $value === null) {
    unset($meta[$id][$key]);
  } else {
    $meta[$id][$key] = $value;
  }
  return file_put_contents($path, json_encode($meta, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), LOCK_EX);
}

function findBook($books, $id) {
  foreach ($books as $b) if ($b["id"] === $id) return $b;
  return null;
}

$BOOKS = [];
foreach (loadBookMeta($BOOKS_META_FILE) as $id => $b) {
  $b["id"] = (string)$id; // PHP casts numeric-looking JSON object keys to int; force back to string
  $b["title"] = $b["title"] ?? "";
  $b["pages"] = $b["pages"] ?? null;
  $b["filePdf"] = "books/" . $b["dir"] . "/" . $id . ".pdf";
  $b["fileData"] = "books/" . $b["dir"] . "/" . $id . ".json";
  $BOOKS[] = $b;
}

// Reads a book's "<id>.json" (gpt/mine/digi keys), if it exists.
function loadBookData($path) {
  if (!file_exists($path)) return [];
  $data = json_decode(file_get_contents($path), true);
  return is_array($data) ? $data : [];
}

// Save endpoint. POST action=mine -> updates the "mine" key in books/<dir>/<id>.json.
// POST action=title / action=pages -> update that book's entry in books.json.
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  header("Content-Type: application/json; charset=utf-8");
  $action = $_POST["action"] ?? "mine";
  $id = $_POST["id"] ?? "";
  $book = findBook($BOOKS, $id);
  if (!$book) {
    http_response_code(400);
    echo json_encode(["ok" => false, "error" => "Tuntematon kirja"]);
    exit;
  }

  if ($action === "title") {
    $ok = saveBookMeta($BOOKS_META_FILE, $id, "title", trim($_POST["title"] ?? ""));
    if ($ok === false) {
      http_response_code(500);
      echo json_encode(["ok" => false, "error" => "Tiedoston kirjoitus epäonnistui"]);
      exit;
    }
    echo json_encode(["ok" => true]);
    exit;
  }

  if ($action === "pages") {
    $pages = (int)($_POST["pages"] ?? 0);
    if ($pages < 1) {
      http_response_code(400);
      echo json_encode(["ok" => false, "error" => "Virheellinen sivumäärä"]);
      exit;
    }
    $ok = saveBookMeta($BOOKS_META_FILE, $id, "pages", $pages);
    if ($ok === false) {
      http_response_code(500);
      echo json_encode(["ok" => false, "error" => "Tiedoston kirjoitus epäonnistui"]);
      exit;
    }
    echo json_encode(["ok" => true]);
    exit;
  }

  $text = $_POST["text"] ?? "";
  $data = loadBookData($book["fileData"]);
  $data["mine"] = $text;
  $ok = file_put_contents($book["fileData"], json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), LOCK_EX);
  if ($ok === false) {
    http_response_code(500);
    echo json_encode(["ok" => false, "error" => "Tiedoston kirjoitus epäonnistui"]);
    exit;
  }
  echo json_encode(["ok" => true]);
  exit;
}

$booksJson = json_encode($BOOKS, JSON_UNESCAPED_UNICODE);
?>
<!DOCTYPE html>
<html lang="fi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sukututkimus</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<style>
  mark { background-color: #fcd34d; }
  pre { white-space: pre-wrap; font-family: ui-monospace, Menlo, Consolas, monospace; }
</style>
</head>
<body class="bg-yellow-50 text-gray-800 min-h-screen flex flex-col">

<header class="bg-yellow-900 text-yellow-50 border-b-4 border-yellow-600">
  <div class="px-6 py-6 flex flex-wrap items-center justify-between gap-4">
    <div>
      <h1 class="text-3xl font-serif font-bold"><a href="#" onclick="showList();return false" class="hover:text-yellow-200">Sukututkimus</a></h1>
      <p class="text-yellow-200 mt-1">Taipaleen ortodoksisen seurakunnan arkistokirjat</p>
    </div>
    <input id="search" type="text" placeholder="Hae... (voit käyttää * jokerimerkkinä)"
      class="w-full sm:w-80 border-2 border-yellow-400 rounded px-3 py-2 bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-yellow-500"
      onkeyup="search(this.value)">
  </div>
</header>

<div class="px-6 py-4 w-full flex-1">

  <div id="output">Ladataan...</div>

</div>

<footer class="border-t-2 border-yellow-500 mt-8">
  <div class="px-6 py-6 text-center text-sm text-gray-600">
    Tehnyt <a href="https://www.jukkapajarinen.com" class="text-yellow-800 underline hover:text-yellow-900" target="_blank" rel="noopener">Jukka Pajarinen</a>
    &middot; <a href="https://github.com/jukkapajarinen/sukututkimus" class="text-yellow-800 underline hover:text-yellow-900" target="_blank" rel="noopener">GitHub</a>
  </div>
</footer>

<script>
pdfjsLib.GlobalWorkerOptions.workerSrc = "https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js";

// BOOKS (with filePdf/fileData already resolved) comes straight from PHP,
// so the list of books and their folder locations only need to be maintained once.
var BOOKS = <?php echo $booksJson; ?>;

var output = document.getElementById("output");
var SOURCES = {
  mine: { label: "Oma", field: "textMine" },
  gpt: { label: "GPT", field: "textGpt" },
  digi: { label: "Digihakemisto", field: "textDigi" }
};
var viewMode = "list";

// Each book's transcriptions live in one books/<dir>/<id>.json ({gpt, mine, digi});
// a book with none of them yet has no file at all, so a 404 there is expected.
var fetches = [];
BOOKS.forEach(function (b) {
  fetches.push(
    fetch(b.fileData)
      .then(function (r) { return r.ok ? r.json() : null; })
      .then(function (data) {
        if (!data) return;
        if (data.gpt) b.textGpt = data.gpt;
        if (data.mine) b.textMine = data.mine;
        if (data.digi) b.textDigi = data.digi;
      })
      .catch(function () {})
  );
});
Promise.all(fetches).then(showList);

// Splits a transcript into { pageNumber: pageText } based on "[Sivu N]" headings.
// Falls back to putting everything on page 1 if no headings are found.
function pagesFromText(text) {
  var map = {};
  if (!text) return map;
  var re = /\[Sivu\s+(\d+)\]/g;
  var matches = [];
  var m;
  while ((m = re.exec(text))) matches.push({ num: parseInt(m[1], 10), start: m.index, end: re.lastIndex });
  if (!matches.length) { map[1] = text; return map; }
  matches.forEach(function (mm, i) {
    var contentStart = mm.end;
    var contentEnd = (i + 1 < matches.length) ? matches[i + 1].start : text.length;
    map[mm.num] = text.slice(contentStart, contentEnd).replace(/^\s+/, "").replace(/\s+$/, "");
  });
  return map;
}

// "<id> — <custom title>"; the id itself never changes, only whether a title is appended.
function titleSuffixHtml(b) {
  return b.title ? " <span class='font-normal text-gray-600'>&mdash; " + escapeHtml(b.title) + "</span>" : "";
}

// The PDF's page count, cached in books.json the first time its page is opened (see showBookPage).
function pageCountSuffixHtml(b) {
  return b.pages ? " <span class='font-normal text-sm text-green-700'>(" + b.pages + " sivua)</span>" : "";
}

// Same as titleSuffixHtml, plus an edit pencil — used only on the book page, not the list.
function bookHeaderHtml(b, pencilClass) {
  return b.id + titleSuffixHtml(b) +
    " <button onclick='editTitle(\"" + b.id + "\", event)' class='" + pencilClass + "' title='Muokkaa otsikkoa'>&#9998;</button>";
}

function showList() {
  viewMode = "list";
  var html = "";
  var lastCategory = null;
  BOOKS.forEach(function (b) {
    if (b.category !== lastCategory) {
      html += "<h2 class='font-serif font-semibold text-yellow-900 mt-6 mb-2'>" + b.category + "</h2>";
      lastCategory = b.category;
    }
    var links = "";
    if (b.textGpt) {
      links += "<a href='#' onclick='event.stopPropagation();showBookPage(\"" + b.id + "\", 1, \"gpt\", 0);return false' " +
        "class='text-xs font-bold uppercase tracking-wide px-2 py-0.5 rounded border-2 border-green-500 text-green-800 bg-green-50 hover:bg-green-100'>GPT</a>";
    }
    if (b.textMine) {
      links += "<span class='ml-3 text-xs font-bold uppercase tracking-wide px-2 py-0.5 rounded border-2 border-blue-500 text-blue-800 bg-blue-50'>Jukka Pajarinen</span>";
    }
    if (b.textDigi) {
      links += "<a href='#' onclick='event.stopPropagation();showBookPage(\"" + b.id + "\", 1, \"digi\", 0);return false' " +
        "class='ml-3 text-xs font-bold uppercase tracking-wide px-2 py-0.5 rounded border-2 border-purple-500 text-purple-800 bg-purple-50 hover:bg-purple-100'>Digihakemisto</a>";
    }
    html += "<div onclick='showBookPage(\"" + b.id + "\", 1)' " +
      "class='flex items-center justify-between bg-white border-2 border-yellow-400 rounded px-4 py-2 mb-1 text-yellow-900 cursor-pointer hover:bg-yellow-50'>" +
      "<span>" + b.id + pageCountSuffixHtml(b) + titleSuffixHtml(b) + "</span>" +
      "<span class='space-x-1'>" + links + "</span></div>";
  });
  output.innerHTML = html;
}

function editTitle(id, event) {
  if (event) event.stopPropagation();
  var b = BOOKS.find(function (x) { return x.id === id; });
  if (!b) return;
  var input = window.prompt("Otsikko kirjalle " + id + ":", b.title || "");
  if (input === null) return;
  var newTitle = input.trim();
  fetch("index.php", { method: "POST", body: new URLSearchParams({ action: "title", id: id, title: newTitle }) })
    .then(function (r) { return r.json(); })
    .then(function (data) {
      if (!data.ok) { alert("Otsikon tallennus epäonnistui: " + (data.error || "tuntematon virhe")); return; }
      b.title = newTitle;
      if (viewMode === "book" && current.book && current.book.id === id) {
        var header = document.getElementById("bookTitleHeader");
        if (header) header.innerHTML = bookHeaderHtml(b, "text-sm text-gray-500 hover:text-gray-800 align-middle");
      } else {
        showList();
      }
    })
    .catch(function (err) { alert("Otsikon tallennus epäonnistui: " + err.message); });
}

// Current page-viewer state.
var current = { book: null, pdfDoc: null, page: 1, pageCount: null, gptPages: {}, minePages: {}, digiPages: {} };
var mineSaveTimer = null;
var mineDirty = false;

function showBookPage(id, page, focusSource, focusLineIndex) {
  var b = BOOKS.find(function (x) { return x.id === id; });
  if (!b) return;

  viewMode = "book";
  current.book = b;
  current.pdfDoc = null;
  current.page = page || 1;
  current.pageCount = null;
  current.gptPages = pagesFromText(b.textGpt);
  current.minePages = pagesFromText(b.textMine);
  current.digiPages = pagesFromText(b.textDigi);
  mineDirty = false;
  clearTimeout(mineSaveTimer);

  output.innerHTML = bookPageShell(b);

  pdfjsLib.getDocument(b.filePdf).promise.then(function (pdfDoc) {
    if (current.book !== b) return; // user navigated away while loading
    current.pdfDoc = pdfDoc;
    current.pageCount = pdfDoc.numPages;
    if (current.page > current.pageCount) current.page = current.pageCount;
    renderCurrentPage(focusSource, focusLineIndex);
    if (b.pages !== current.pageCount) {
      b.pages = current.pageCount;
      fetch("index.php", { method: "POST", body: new URLSearchParams({ action: "pages", id: b.id, pages: current.pageCount }) }).catch(function () {});
    }
  }).catch(function (err) {
    if (current.book !== b) return;
    var wrap = document.getElementById("pdfCanvasWrap");
    if (wrap) wrap.innerHTML = "<p class='text-sm text-red-500 p-2'>PDF:ää ei voitu ladata tähän selaimeen " +
      "(" + escapeHtml(String((err && err.message) || err)) + ").</p>";
    renderTextPanes(focusSource, focusLineIndex);
  });
}

function bookPageShell(b) {
  return "" +
    "<p class='mb-3'><a href='#' onclick='showList();return false' class='text-yellow-800 underline'>&larr; takaisin</a></p>" +
    "<h2 class='text-xl font-serif font-semibold mb-1 text-yellow-900' id='bookTitleHeader'>" +
      bookHeaderHtml(b, "text-sm text-gray-500 hover:text-gray-800 align-middle") + "</h2>" +
    "<p class='text-sm text-gray-600 mb-3'>" + b.category + "</p>" +
    "<div class='flex flex-wrap items-center gap-2 mb-4 bg-white border-2 border-yellow-400 rounded px-3 py-2'>" +
      "<button onclick='gotoPage(-1)' class='px-2 py-1 border-2 border-yellow-400 rounded hover:bg-yellow-50'>&larr; Edellinen</button>" +
      "<span>Sivu <input id='pageInput' type='number' min='1' value='" + current.page + "' " +
        "class='w-16 border-2 border-yellow-400 rounded px-1 text-center' onchange='gotoPageInput(this.value)'> / " +
        "<span id='pageCount'>?</span></span>" +
      "<button onclick='gotoPage(1)' class='px-2 py-1 border-2 border-yellow-400 rounded hover:bg-yellow-50'>Seuraava &rarr;</button>" +
    "</div>" +
    "<div class='grid grid-cols-1 lg:grid-cols-2 gap-4'>" +
      "<div>" +
        "<h3 class='font-serif font-semibold text-gray-800 mb-1'>Alkuperäinen</h3>" +
        "<div id='pdfCanvasWrap' class='border-4 border-gray-500 rounded bg-white overflow-auto' style='max-height:90vh'>" +
          "<canvas id='pdfCanvas' class='w-full'></canvas>" +
        "</div>" +
      "</div>" +
      "<div class='space-y-4'>" +
        "<div>" +
          "<h3 class='font-serif font-semibold text-blue-800 mb-1'>Oma transkriptio</h3>" +
          "<textarea id='mineText' oninput='onMineInput()' " +
            "class='w-full border-4 border-blue-400 rounded p-2 text-sm font-mono bg-blue-50 focus:outline-none focus:ring-2 focus:ring-blue-500' " +
            "style='height:28vh' placeholder='Kirjoita transkriptio tälle sivulle...'></textarea>" +
          "<p id='mineStatus' class='text-xs text-blue-700 mt-1'>&nbsp;</p>" +
        "</div>" +
        "<div>" +
          "<h3 class='font-serif font-semibold text-green-800 mb-1'>ChatGPT-transkriptio</h3>" +
          "<pre id='gptText' class='w-full border-4 border-green-400 rounded p-2 text-sm bg-green-50 overflow-auto' style='height:28vh'></pre>" +
        "</div>" +
        "<div>" +
          "<h3 class='font-serif font-semibold text-purple-800 mb-1'>Digihakemisto</h3>" +
          "<pre id='digiText' class='w-full border-4 border-purple-400 rounded p-2 text-sm bg-purple-50 overflow-auto' style='height:28vh'></pre>" +
        "</div>" +
      "</div>" +
    "</div>";
}

function renderCurrentPage(focusSource, focusLineIndex) {
  var pageInput = document.getElementById("pageInput");
  var pageCount = document.getElementById("pageCount");
  if (pageInput) pageInput.value = current.page;
  if (pageCount) pageCount.textContent = current.pageCount || "?";
  renderPdfCanvas();
  renderTextPanes(focusSource, focusLineIndex);
}

function renderPdfCanvas() {
  if (!current.pdfDoc) return;
  var thisBook = current.book;
  var thisPage = current.page;
  current.pdfDoc.getPage(thisPage).then(function (page) {
    if (current.book !== thisBook || current.page !== thisPage) return; // stale render
    var canvas = document.getElementById("pdfCanvas");
    if (!canvas) return;
    var viewport = page.getViewport({ scale: 1.5 });
    canvas.width = viewport.width;
    canvas.height = viewport.height;
    var ctx = canvas.getContext("2d");
    page.render({ canvasContext: ctx, viewport: viewport });
  });
}

// Renders a read-only pane (GPT / Digihakemisto), highlighting one line when it's the search-jump target.
function renderReadOnlyPane(elId, text, emptyMessage, isFocused, focusLineIndex) {
  var el = document.getElementById(elId);
  if (!el) return;
  if (isFocused && focusLineIndex !== undefined && text !== undefined) {
    var lines = text.split("\n");
    el.innerHTML = lines.map(function (l, i) {
      var esc = escapeHtml(l);
      return i === focusLineIndex ? "<mark id='hit'>" + esc + "</mark>" : esc;
    }).join("\n");
    var hit = document.getElementById("hit");
    if (hit) hit.scrollIntoView({ behavior: "smooth", block: "center" });
  } else {
    el.textContent = text !== undefined ? text : emptyMessage;
  }
}

function renderTextPanes(focusSource, focusLineIndex) {
  var page = current.page;

  var mineVal = current.minePages[page] || "";
  var mineEl = document.getElementById("mineText");
  if (mineEl) mineEl.value = mineVal;

  var statusEl = document.getElementById("mineStatus");
  if (statusEl) statusEl.textContent = " ";

  renderReadOnlyPane("gptText", current.gptPages[page], "(ei ChatGPT-transkriptiota tälle sivulle)", focusSource === "gpt", focusLineIndex);
  renderReadOnlyPane("digiText", current.digiPages[page], "(ei digihakemisto-tietoja tälle sivulle)", focusSource === "digi", focusLineIndex);

  if (focusSource === "mine" && focusLineIndex !== undefined && mineEl) {
    var mlines = mineVal.split("\n");
    var pos = 0;
    for (var i = 0; i < focusLineIndex; i++) pos += mlines[i].length + 1;
    mineEl.focus();
    mineEl.setSelectionRange(pos, pos + (mlines[focusLineIndex] || "").length);
  }
}

function onMineInput() {
  var el = document.getElementById("mineText");
  if (!current.book) return;
  current.minePages[current.page] = el.value;
  mineDirty = true;
  clearTimeout(mineSaveTimer);
  mineSaveTimer = setTimeout(saveMine, 600);
}

// Captures the visible textarea into memory and, if there's anything unsaved, saves right away.
// Called before switching pages so quick navigation right after typing doesn't drop text.
function flushMine() {
  if (!current.book) return;
  var el = document.getElementById("mineText");
  if (el) current.minePages[current.page] = el.value;
  if (mineDirty) saveMine();
}

// POSTs the whole book's transcription (all pages, re-assembled with "[Sivu N]" headings)
// to index.php, which writes it to books/<dir>/<id>-mine.txt.
function saveMine() {
  if (!current.book) return;
  clearTimeout(mineSaveTimer);
  mineDirty = false;

  var book = current.book;
  var maxPage = current.pageCount || current.page;
  Object.keys(current.minePages).forEach(function (k) {
    var n = parseInt(k, 10);
    if (n > maxPage) maxPage = n;
  });

  var parts = [];
  for (var p = 1; p <= maxPage; p++) {
    parts.push("[Sivu " + p + "]\n\n" + (current.minePages[p] || "").replace(/\s+$/, ""));
  }
  var fullText = parts.join("\n\n");

  var statusEl = document.getElementById("mineStatus");
  if (statusEl) statusEl.textContent = "Tallennetaan...";

  fetch("index.php", { method: "POST", body: new URLSearchParams({ action: "mine", id: book.id, text: fullText }) })
    .then(function (r) { return r.json(); })
    .then(function (data) {
      if (current.book !== book) return;
      book.textMine = fullText;
      if (statusEl) statusEl.textContent = data.ok ? "Tallennettu palvelimelle." : "Virhe: " + (data.error || "tuntematon virhe");
    })
    .catch(function (err) {
      if (current.book !== book || !statusEl) return;
      statusEl.textContent = "Virhe tallennuksessa (onko “php -S” käynnissä?): " + err.message;
    });
}

function gotoPage(delta) {
  if (!current.book) return;
  flushMine();
  var next = current.page + delta;
  if (next < 1) next = 1;
  if (current.pageCount && next > current.pageCount) next = current.pageCount;
  current.page = next;
  renderCurrentPage();
}

function gotoPageInput(value) {
  if (!current.book) return;
  flushMine();
  var n = parseInt(value, 10);
  if (isNaN(n) || n < 1) n = 1;
  if (current.pageCount && n > current.pageCount) n = current.pageCount;
  current.page = n;
  renderCurrentPage();
}

function escapeHtml(s) {
  return s.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
}

// Plain substring match, case-insensitive. A "*" in the query means "any characters here",
// e.g. "p*ari*" matches "pekka-ari-nen".
function matchesQuery(query, text) {
  query = query.toLowerCase();
  text = text.toLowerCase();
  if (query.indexOf("*") === -1) return text.indexOf(query) !== -1;
  var pattern = query.split("*").map(function (part) {
    return part.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
  }).join(".*");
  return new RegExp(pattern).test(text);
}

function search(query) {
  if (!query) { showList(); return; }
  var html = "";
  BOOKS.forEach(function (b) {
    Object.keys(SOURCES).forEach(function (source) {
      var src = SOURCES[source];
      var text = b[src.field];
      if (!text) return;
      var pages = pagesFromText(text);
      var pageNums = Object.keys(pages).map(Number).sort(function (a, c) { return a - c; });
      var bookHits = [];
      pageNums.forEach(function (pn) {
        pages[pn].split("\n").forEach(function (l, i) {
          if (l.trim() && matchesQuery(query, l)) bookHits.push({ page: pn, lineIndex: i, text: l });
        });
      });
      if (bookHits.length) {
        html += "<h3 class='font-serif font-semibold mt-4 text-yellow-900'>" + b.id + " &middot; " + b.category + " &middot; " + src.label + "</h3><ul class='space-y-1'>";
        bookHits.forEach(function (h) {
          html += "<li><a href='#' onclick='showBookPage(\"" + b.id + "\", " + h.page + ", \"" + source + "\", " + h.lineIndex + ");return false' " +
            "class='block bg-white border-2 border-yellow-400 rounded px-3 py-1 text-sm text-gray-900 hover:border-yellow-600 hover:shadow transition'>" +
            "<span class='text-xs text-gray-700 mr-2'>s. " + h.page + "</span>" + escapeHtml(h.text) + "</a></li>";
        });
        html += "</ul>";
      }
    });
  });
  output.innerHTML = html || "<p class='text-gray-600'>Ei osumia.</p>";
}
</script>

</body>
</html>
