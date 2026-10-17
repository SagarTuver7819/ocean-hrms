/**
 * Splits #flow content into fixed A4 sheets, each carrying the letterhead from <template id="lhTpl">.
 * Container: <div id="pages" data-mode="auto|header_footer|full" data-top data-bottom data-side>
 */
(function () {
  var MM = 96 / 25.4;
  var pagesEl = document.getElementById('pages');
  var flow = document.getElementById('flow');
  var tpl = document.getElementById('lhTpl');
  if (!pagesEl || !flow || !tpl) return;

  var root = document.documentElement;
  var cfg = {
    mode: pagesEl.dataset.mode,
    top: parseFloat(pagesEl.dataset.top) || 0,
    bottom: parseFloat(pagesEl.dataset.bottom) || 0,
    side: parseFloat(pagesEl.dataset.side) || 12
  };

  function loadImage(src) {
    return new Promise(function (resolve) {
      if (!src) return resolve(null);
      var img = new Image();
      img.onload = function () { resolve(img); };
      img.onerror = function () { resolve(null); };
      img.src = src;
    });
  }

  /* Finds the printed band at the top and bottom of a full-page letterhead image (in mm). */
  function detectBands(img) {
    var w = 300, h = Math.round(img.naturalHeight * w / img.naturalWidth);
    var c = document.createElement('canvas');
    c.width = w; c.height = h;
    var ctx = c.getContext('2d');
    ctx.drawImage(img, 0, 0, w, h);
    var data;
    try { data = ctx.getImageData(0, 0, w, h).data; } catch (e) { return null; }
    function blank(y) {
      for (var x = 0; x < w; x += 2) {
        var i = (y * w + x) * 4;
        if (data[i + 3] > 20 && (data[i] < 232 || data[i + 1] < 232 || data[i + 2] < 232)) return false;
      }
      return true;
    }
    var gap = Math.max(4, Math.round(h * 0.025));
    function scan(from, to, step) {
      var seen = false, run = 0, edge = from;
      for (var y = from; step > 0 ? y < to : y > to; y += step) {
        if (blank(y)) {
          run++;
          if (seen && run >= gap) return edge;
        } else {
          seen = true; run = 0; edge = y;
        }
      }
      return seen ? edge : null;
    }
    var headEdge = scan(0, Math.round(h * 0.45), 1);
    var footEdge = scan(h - 1, Math.round(h * 0.6), -1);
    return {
      top: headEdge === null ? 0 : (headEdge + 1) / h * 297,
      bottom: footEdge === null ? 0 : (h - footEdge) / h * 297
    };
  }

  function measureLetterhead() {
    var probe = tpl.content;
    if (cfg.mode === 'full') {
      var full = probe.querySelector('img.lh-full');
      return loadImage(full && full.getAttribute('src')).then(function (img) {
        var bands = img ? detectBands(img) : null;
        if (bands) {
          cfg.top = Math.max(cfg.top, bands.top + 6);
          cfg.bottom = Math.max(cfg.bottom, bands.bottom + 6);
        }
      });
    }
    if (cfg.mode === 'header_footer') {
      var width = 210 - 2 * cfg.side;
      var head = probe.querySelector('img.lh-header'), foot = probe.querySelector('img.lh-footer');
      return Promise.all([
        loadImage(head && head.getAttribute('src')),
        loadImage(foot && foot.getAttribute('src'))
      ]).then(function (imgs) {
        if (imgs[0]) cfg.top = imgs[0].naturalHeight / imgs[0].naturalWidth * width + 6 + 6;
        if (imgs[1]) cfg.bottom = imgs[1].naturalHeight / imgs[1].naturalWidth * width + 4 + 5;
      });
    }
    return Promise.resolve();
  }

  var pages = [];
  function newPage() {
    var sheet = document.createElement('div');
    sheet.className = 'sheet';
    sheet.appendChild(tpl.content.cloneNode(true));
    var body = document.createElement('div');
    body.className = 'page-body';
    sheet.appendChild(body);
    pagesEl.appendChild(sheet);
    pages.push(sheet);
    return body;
  }
  function fits(body) { return body.scrollHeight <= body.clientHeight + 1; }
  function keepsWithNext(el) {
    return el && (/^H[1-6]$/.test(el.tagName) || el.classList.contains('keep-next'));
  }

  /* Splits a block containing a table or list across pages, repeating table header rows. */
  function splitBlock(block, body) {
    var list = block.matches('ul,ol') ? block : null;
    var table = list ? null : block.querySelector('table');
    if (!table && !list) return null;

    var container = list || table;
    var items, headRows = [];
    if (table) {
      var rows = Array.prototype.slice.call(table.rows);
      headRows = rows.filter(function (r) { return r.querySelector('th') && !r.querySelector('td'); });
      items = rows.filter(function (r) { return headRows.indexOf(r) === -1; });
    } else {
      items = Array.prototype.slice.call(list.children);
    }
    if (items.length < 2) return null;

    var before = [], after = [];
    if (table) {
      var top = table;
      while (top.parentElement && top.parentElement !== block) top = top.parentElement;
      var seenTable = false;
      Array.prototype.slice.call(block.children).forEach(function (c) {
        if (c === top) { seenTable = true; return; }
        (seenTable ? after : before).push(c);
      });
    }

    function makePart(contd) {
      var part = list ? list.cloneNode(false) : block.cloneNode(false);
      var target = part;
      if (table) {
        before.forEach(function (b) {
          var copy = b.cloneNode(true);
          if (contd && /^H[1-6]$/.test(copy.tagName)) copy.appendChild(document.createTextNode(' (contd.)'));
          part.appendChild(copy);
        });
        var t = table.cloneNode(false);
        if (headRows.length) {
          var thead = document.createElement('thead');
          headRows.forEach(function (r) { thead.appendChild(r.cloneNode(true)); });
          t.appendChild(thead);
        }
        target = document.createElement('tbody');
        t.appendChild(target);
        part.appendChild(t);
      }
      return { el: part, target: target, count: 0 };
    }

    block.remove();
    var part = makePart(false);
    body.appendChild(part.el);
    items.forEach(function (item) {
      part.target.appendChild(item);
      if (!fits(body) && !(part.count === 0 && body.children.length === 1)) {
        item.remove();
        if (part.count === 0) part.el.remove();
        body = newPage();
        part = makePart(part.count > 0);
        body.appendChild(part.el);
        part.target.appendChild(item);
      }
      part.count++;
    });
    after.forEach(function (sib) {
      part.el.appendChild(sib);
      if (!fits(body)) {
        sib.remove();
        body = newPage();
        var wrap = block.cloneNode(false);
        wrap.appendChild(sib);
        body.appendChild(wrap);
      }
    });
    return body;
  }

  function pageHasContent(sheet) {
    var b = sheet && sheet.querySelector('.page-body');
    return !!(b && b.children.length);
  }

  /** Remove empty letterhead-only sheets (middle or trailing). */
  function pruneEmptyPages() {
    for (var i = pages.length - 1; i >= 0; i--) {
      if (pageHasContent(pages[i])) continue;
      if (pages.length <= 1) break;
      pages[i].remove();
      pages.splice(i, 1);
    }
  }

  function paginate() {
    root.style.setProperty('--top', cfg.top.toFixed(1) + 'mm');
    root.style.setProperty('--bottom', cfg.bottom.toFixed(1) + 'mm');

    var blocks = Array.prototype.slice.call(flow.children);
    var body = newPage();
    blocks.forEach(function (block) {
      if (block.classList.contains('page-break') && body.children.length) body = newPage();
      body.appendChild(block);
      if (fits(body)) return;

      if (block.offsetHeight > body.clientHeight * 0.35 || body.children.length === 1) {
        var split = splitBlock(block, body);
        if (split) { body = split; return; }
      }

      if (body.children.length > 1) {
        block.remove();
        var carry = [];
        if (block.classList.contains('keep-prev') && body.children.length > 1) {
          carry.unshift(body.lastElementChild);
          body.lastElementChild.remove();
        }
        while (body.lastElementChild && keepsWithNext(body.lastElementChild) && body.children.length > 1) {
          carry.unshift(body.lastElementChild);
          body.lastElementChild.remove();
        }
        // If current page became empty after carrying, reuse it instead of creating a blank sheet
        if (!body.children.length) {
          carry.forEach(function (c) { body.appendChild(c); });
          body.appendChild(block);
        } else {
          body = newPage();
          carry.forEach(function (c) { body.appendChild(c); });
          body.appendChild(block);
        }
      }
    });

    pruneEmptyPages();

    pages.forEach(function (sheet, i) {
      var no = document.createElement('div');
      no.className = 'page-no';
      no.textContent = 'Page ' + (i + 1) + ' of ' + pages.length;
      sheet.appendChild(no);
    });
    var counter = document.getElementById('pageCount');
    if (counter) counter.textContent = pages.length + ' page' + (pages.length > 1 ? 's' : '') + ' · A4';
    flow.remove();
    document.dispatchEvent(new Event('paged:ready'));
  }

  var fontsReady = document.fonts && document.fonts.ready ? document.fonts.ready : Promise.resolve();
  Promise.all([fontsReady, measureLetterhead()]).then(paginate);
})();
