(function () {
  'use strict';

  const $ = (s, el = document) => el.querySelector(s);
  const $$ = (s, el = document) => Array.from(el.querySelectorAll(s));

  const form = $('#crForm');
  const steps = $$('.cr-step', form);
  const total = steps.length;
  let current = 1;
  let rowIndex = 0;

  /* ---------- Intro ---------- */
  $('#crStart').addEventListener('click', () => {
    $('#crIntro').classList.add('d-none');
    form.classList.remove('d-none');
    show(1);
  });

  /* ---------- Step navigation ---------- */
  function show(n) {
    current = n;
    steps.forEach(s => s.classList.toggle('d-none', +s.dataset.step !== n));
    $('#crBar').style.width = (n / total * 100) + '%';
    $('#crStepTitle').textContent = steps[n - 1].dataset.title;
    $('#crStepCount').textContent = 'Step ' + n + ' of ' + total;
    $('#crBack').style.visibility = n === 1 ? 'hidden' : 'visible';
    $('#crNext').classList.toggle('d-none', n === total);
    $('#crSubmit').classList.toggle('d-none', n !== total);
    if (n === total) buildReview();
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }

  function validateStep(n) {
    const section = steps[n - 1];
    const fields = $$('input, select, textarea', section).filter(f => !f.disabled && f.offsetParent !== null);
    for (const f of fields) {
      if (!f.checkValidity()) {
        section.classList.add('was-validated');
        f.reportValidity();
        f.focus();
        return false;
      }
    }
    return true;
  }

  $('#crNext').addEventListener('click', () => {
    if (validateStep(current)) show(current + 1);
  });
  $('#crBack').addEventListener('click', () => { if (current > 1) show(current - 1); });

  /* ---------- Step 1: position ---------- */
  const desig = $('#crDesig');
  desig.addEventListener('change', () => {
    const other = desig.value === '__other';
    $('#crOtherWrap').classList.toggle('d-none', !other);
    $('#crOther').required = other;
    if (other) $('#crOther').focus();
  });
  $('#crSource').addEventListener('change', e => {
    const other = e.target.value === 'Other';
    $('#crRefWrap').classList.toggle('d-none', e.target.value !== 'Employee Referral');
    $('#crSourceOtherWrap').classList.toggle('d-none', !other);
    $('#crSourceOther').required = other;
    if (other) $('#crSourceOther').focus();
  });

  /* ---------- Step 2: age ---------- */
  $('#crDob').addEventListener('change', e => {
    const dob = new Date(e.target.value);
    if (isNaN(dob)) { $('#crAge').value = ''; return; }
    const now = new Date();
    let age = now.getFullYear() - dob.getFullYear();
    const m = now.getMonth() - dob.getMonth();
    if (m < 0 || (m === 0 && now.getDate() < dob.getDate())) age--;
    $('#crAge').value = age > 0 ? age + ' yrs' : '';
  });
  $('input[name="pan_number"]').addEventListener('input', e => { e.target.value = e.target.value.toUpperCase(); });
  $$('input[inputmode="numeric"]').forEach(i => i.addEventListener('input', () => {
    if (i.type !== 'number') i.value = i.value.replace(/\D/g, '');
  }));

  /* ---------- Repeatable rows ---------- */
  function addRow(tplId, listId) {
    const html = $('#' + tplId).innerHTML.replace(/__i__/g, String(rowIndex++));
    const wrap = document.createElement('div');
    wrap.innerHTML = html.trim();
    const row = wrap.firstElementChild;
    $('#' + listId).appendChild(row);
    row.querySelector('.cr-row-del').addEventListener('click', () => {
      row.remove();
      calcExperience();
    });
    return row;
  }

  $('#crAddEdu').addEventListener('click', () => addRow('tplEdu', 'crEduList'));
  addRow('tplEdu', 'crEduList');

  /* ---------- Step 4: experience ---------- */
  const expList = $('#crExpList');
  function monthsBetween(from, to) {
    if (!from) return 0;
    const [fy, fm] = from.split('-').map(Number);
    const t = to ? to.split('-').map(Number) : [new Date().getFullYear(), new Date().getMonth() + 1];
    return Math.max(0, (t[0] - fy) * 12 + (t[1] - fm) + 1);
  }
  function label(months) {
    if (!months) return '—';
    const y = Math.floor(months / 12), m = months % 12;
    return [y ? y + ' yr' + (y > 1 ? 's' : '') : '', m ? m + ' month' + (m > 1 ? 's' : '') : ''].filter(Boolean).join(' ');
  }
  function calcExperience() {
    let sum = 0;
    $$('.cr-row', expList).forEach(row => {
      const cur = $('.js-current', row).checked;
      const to = $('.js-to', row);
      to.disabled = cur;
      if (cur) to.value = '';
      const mo = monthsBetween($('.js-from', row).value, cur ? '' : to.value);
      $('.js-dur', row).textContent = mo ? 'Duration: ' + label(mo) : '—';
      sum += mo;
    });
    $('#crTotalExp').textContent = sum ? label(sum) : '—';
  }
  expList.addEventListener('change', calcExperience);
  $('#crAddExp').addEventListener('click', () => { addRow('tplExp', 'crExpList'); calcExperience(); });

  const fresher = $('#crFresher');
  fresher.addEventListener('change', () => {
    const on = fresher.checked;
    $('#crExpBlock').classList.toggle('d-none', on);
    $('#crCurCtcWrap').classList.toggle('d-none', on);
    $$('input, textarea', $('#crExpBlock')).forEach(f => { f.disabled = on; });
    if (!on) calcExperience();
  });
  addRow('tplExp', 'crExpList');

  /* Internship → hide experience details (QR form unchanged) */
  function isIntern() {
    const el = form.elements.employment_type;
    if (!el) return false;
    const checked = el instanceof RadioNodeList
      ? Array.from(el).find(r => r.checked)
      : (el.checked ? el : null);
    return checked && checked.value === 'intern';
  }
  function syncInternMode() {
    const intern = isIntern();
    const step = $('#crStepExp');
    if (step) {
      step.dataset.title = intern ? 'Stipend & Availability' : 'Experience & CTC';
      if (current === 4) $('#crStepTitle').textContent = step.dataset.title;
    }
    const title = $('#crStepExpTitle');
    if (title) title.innerHTML = intern
      ? '<i class="fa-solid fa-building"></i> Stipend &amp; Availability'
      : '<i class="fa-solid fa-building"></i> Experience &amp; CTC';
    $('#crInternNote').classList.toggle('d-none', !intern);
    $('#crFresherWrap').classList.toggle('d-none', intern);
    if (intern) {
      fresher.checked = true;
      fresher.dispatchEvent(new Event('change'));
      const cur = form.elements.current_ctc;
      if (cur) cur.value = '';
      $('#crExpCtcLabel').textContent = 'Expected stipend (₹ / year)';
      $('#crExpCtc').placeholder = 'e.g. 60000';
    } else {
      $('#crExpCtcLabel').textContent = 'Expected CTC (₹ / year)';
      $('#crExpCtc').placeholder = 'e.g. 480000';
    }
  }
  $$('input[name="employment_type"]').forEach(r => r.addEventListener('change', syncInternMode));
  syncInternMode();

  /* ---------- Step 5: skill hints ---------- */
  const skills = $('#crSkills');
  $$('#crSkillHints .cr-tag').forEach(tag => tag.addEventListener('click', () => {
    const list = skills.value.split(',').map(s => s.trim()).filter(Boolean);
    const name = tag.textContent.trim();
    const idx = list.findIndex(s => s.toLowerCase() === name.toLowerCase());
    if (idx >= 0) list.splice(idx, 1); else list.push(name);
    skills.value = list.join(', ');
    syncTags();
  }));
  function syncTags() {
    const list = skills.value.toLowerCase().split(',').map(s => s.trim());
    $$('#crSkillHints .cr-tag').forEach(t => t.classList.toggle('on', list.includes(t.textContent.trim().toLowerCase())));
  }
  skills.addEventListener('input', syncTags);

  /* ---------- Step 6: uploads & review ---------- */
  const MAX = 5 * 1024 * 1024;
  $('#crPhoto').addEventListener('change', e => {
    const f = e.target.files[0];
    const img = $('#crPhotoPreview');
    if (!f) return;
    if (f.size > MAX) { e.target.value = ''; return alertErr('Photo must be under 5MB.'); }
    img.src = URL.createObjectURL(f);
    img.classList.remove('d-none');
    $('#crPhotoBox .cr-upload-txt').classList.add('d-none');
    $('#crPhotoBox').classList.add('has-file');
  });
  $('#crResume').addEventListener('change', e => {
    const f = e.target.files[0];
    if (!f) return;
    if (f.size > MAX) { e.target.value = ''; return alertErr('Resume must be under 5MB.'); }
    $('#crResumeTxt').innerHTML = '<i class="fa-solid fa-file-circle-check"></i>' + escapeHtml(f.name) + '<small>Tap to change</small>';
    e.target.closest('.cr-upload').classList.add('has-file');
  });

  function val(name) {
    const el = form.elements[name];
    if (!el) return '';
    if (el.tagName === 'SELECT') return el.value ? el.selectedOptions[0].textContent.trim() : '';
    if (el instanceof RadioNodeList) {
      const c = Array.from(el).find(r => r.checked);
      return c ? c.nextElementSibling.textContent : '';
    }
    return el.value.trim();
  }
  function buildReview() {
    const pos = desig.value === '__other' ? val('position_other') : val('position');
    const name = [val('first_name'), val('middle_name'), val('last_name')].filter(Boolean).join(' ');
    const intern = isIntern();
    const exp = intern || fresher.checked ? (intern ? 'Internship (no experience)' : 'Fresher') : $('#crTotalExp').textContent;
    const ctcLabel = intern ? 'Expected stipend' : 'Expected CTC';
    const rows = [
      ['Position', pos], ['Type', val('employment_type')],
      ['Name', name], ['Mobile', val('mobile')], ['Parents Contact', val('parent_mobile')], ['Email', val('email')],
      ['Experience', exp], [ctcLabel, val('expected_ctc') ? '₹ ' + Number(val('expected_ctc')).toLocaleString('en-IN') + ' / year' : '—'],
      ['Primary skills', val('primary_skills')],
    ];
    $('#crReview').innerHTML = rows.map(r =>
      '<div class="cr-review-row"><span>' + r[0] + '</span><b>' + escapeHtml(r[1] || '—') + '</b></div>'
    ).join('');
  }

  /* ---------- Submit ---------- */
  form.addEventListener('submit', async e => {
    e.preventDefault();
    for (let i = 1; i <= total; i++) {
      if (i !== current) {
        steps[i - 1].classList.remove('d-none');
        const ok = validateStep(i);
        if (!ok) { show(i); validateStep(i); return; }
        steps[i - 1].classList.add('d-none');
      }
    }
    if (!validateStep(current)) return;

    const btn = $('#crSubmit');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Submitting…';
    try {
      const res = await fetch(form.action, {
        method: 'POST', body: new FormData(form),
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
      });
      const data = await res.json().catch(() => ({ ok: false, message: 'Unexpected server response.' }));
      if (data.ok) {
        window.location.href = data.redirect;
        return;
      }
      alertErr(data.message || 'Could not submit application.');
    } catch (err) {
      alertErr('Network error. Please check your connection and try again.');
    }
    btn.disabled = false;
    btn.innerHTML = '<i class="fa-solid fa-paper-plane me-1"></i> Submit Application';
  });

  function alertErr(msg) {
    if (window.Swal) Swal.fire({ icon: 'error', title: 'Please check', text: msg, confirmButtonColor: '#F5A01B' });
    else alert(msg);
  }
  function escapeHtml(s) {
    return String(s).replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
  }
})();
