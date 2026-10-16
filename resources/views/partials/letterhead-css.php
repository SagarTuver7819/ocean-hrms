<?php $lh = $letterhead; ?>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
  :root {
    --brand: #F5A01B; --ink: #211F23; --line: #d9d9de; --soft: #fff4e2;
    --top: <?= (int) $lh['top_mm'] ?>mm; --bottom: <?= (int) $lh['bottom_mm'] ?>mm; --side: <?= (int) $lh['side_mm'] ?>mm;
  }
  * { box-sizing: border-box; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
  body { margin: 0; background: #e9e9ee; font: 11px/1.45 "Plus Jakarta Sans", Arial, sans-serif; color: var(--ink); }

  .toolbar { max-width: 210mm; margin: 14px auto; display: flex; flex-wrap: wrap; gap: 10px; justify-content: space-between; align-items: center;
    background: #fff; padding: 10px 14px; border-radius: 10px; box-shadow: 0 4px 14px rgba(0,0,0,.06); font-size: 13px; border-top: 3px solid var(--brand); }
  .toolbar .tb-actions { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; }
  .toolbar button, .toolbar .tb-btn { background: var(--brand); color: #fff; border: 0; padding: 9px 16px; border-radius: 8px; font-weight: 700; cursor: pointer; font-family: inherit; font-size: 13px; text-decoration: none; }
  .toolbar .tb-btn.ghost { background: #f3f4f6; color: var(--ink); }
  .toolbar label { font-size: 12px; font-weight: 700; color: #4b5563; display: flex; align-items: center; gap: 6px; }
  .toolbar input { font-family: inherit; font-size: 12.5px; padding: 6px 8px; border: 1px solid var(--line); border-radius: 7px; }
  .toolbar .tb-pages { font-size: 12px; color: #6b7280; font-weight: 700; }

  /* One fixed A4 sheet per printed page; content flows between header and footer. */
  .sheet { position: relative; width: 210mm; height: 297mm; margin: 0 auto 18px; background: #fff; box-shadow: 0 6px 24px rgba(0,0,0,.12); overflow: hidden; }
  .page-body { position: absolute; z-index: 1; top: var(--top); bottom: var(--bottom); left: var(--side); right: var(--side); overflow: hidden; }
  .page-body > :first-child { margin-top: 0 !important; }
  .page-no { position: absolute; z-index: 2; right: var(--side); bottom: calc(var(--bottom) - 5mm); font-size: 8.5px; color: #9ca3af; font-weight: 700; }
  .flow { position: absolute; left: -10000px; top: 0; width: calc(210mm - 2 * var(--side)); }

  .lh-full { position: absolute; top: 0; left: 0; width: 210mm; height: 297mm; object-fit: fill; z-index: 0; }
  .lh-header, .lh-footer { position: absolute; left: 0; width: 210mm; z-index: 0; }
  .lh-header { top: 0; } .lh-footer { bottom: 0; }
  img.lh-header, img.lh-footer { display: block; height: auto; left: var(--side); width: calc(210mm - 2 * var(--side)); }
  img.lh-header { top: 6mm; }
  img.lh-footer { bottom: 4mm; }

  .lh-auto-head { height: var(--top); padding: 8mm var(--side) 0; display: flex; align-items: center; gap: 14px; border-bottom: 3px solid var(--brand); background: #fff; }
  .lh-auto-head img { height: 18mm; width: auto; }
  .lh-auto-head .co { flex: 1; }
  .lh-auto-head .co b { display: block; font-size: 18px; font-weight: 800; letter-spacing: .01em; }
  .lh-auto-head .co small { display: block; color: #6b7280; font-size: 10px; }
  .lh-auto-head .ct { text-align: right; font-size: 9.5px; color: #4b5563; line-height: 1.6; }
  .lh-auto-foot { height: var(--bottom); padding: 0 var(--side); display: flex; align-items: center; justify-content: space-between; font-size: 9px; color: #6b7280; border-top: 1px solid var(--line); background: #fff; }
  .lh-auto-foot::before { content: ""; position: absolute; left: 0; right: 0; bottom: 0; height: 4px; background: linear-gradient(90deg, var(--ink) 0 30%, var(--brand) 30%); }

  @page { size: A4; margin: 0; }
  @media print {
    body { background: #fff; }
    .toolbar { display: none; }
    .sheet { margin: 0; box-shadow: none; break-after: page; page-break-after: always; }
    .sheet:last-child { break-after: auto; page-break-after: auto; }
  }
</style>
