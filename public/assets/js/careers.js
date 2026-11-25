(function () {
  'use strict';

  const $ = (s, el = document) => el.querySelector(s);
  const $$ = (s, el = document) => Array.from(el.querySelectorAll(s));

  const form = $('#crForm');
  const steps = $$('.cr-step', form);
  let current = 1;
  let rowIndex = 0;

  function isIntern() {
    const el = form.elements.employment_type;
    if (!el) return false;
    const checked = el instanceof RadioNodeList
      ? Array.from(el).find(r => r.checked)
      : (el.checked ? el : null);
    return !!(checked && checked.value === 'intern');
  }

  /** Internship: 1→2→3→6 only. Full/Part: all 6 steps. */
  function path() {
    return isIntern() ? [1, 2, 3, 6] : [1, 2, 3, 4, 5, 6];
  }
  function pathIndex(step) {
    const p = path();
    const i = p.indexOf(step);
    return i < 0 ? 0 : i;
  }

  /* ---------- Intro ---------- */
  $('#crStart').addEventListener('click', () => {
    $('#crIntro').classList.add('d-none');
    form.classList.remove('d-none');
    show(1);
  });

  /* ---------- Step navigation ---------- */
  function show(n) {
    const p = path();
    if (!p.includes(n)) n = p[0];
    current = n;
    const idx = pathIndex(n);
    const total = p.length;
    steps.forEach(s => s.classList.toggle('d-none', +s.dataset.step !== n));
    $('#crBar').style.width = ((idx + 1) / total * 100) + '%';
    const stepEl = steps[n - 1];
    $('#crStepTitle').textContent = stepEl.dataset.title;
    $('#crStepCount').textContent = 'Step ' + (idx + 1) + ' of ' + total;
    $('#crBack').style.visibility = idx === 0 ? 'hidden' : 'visible';
    const last = p[p.length - 1];
    $('#crNext').classList.toggle('d-none', n === last);
    $('#crSubmit').classList.toggle('d-none', n !== last);
    if (n === last) buildReview();
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }

  function fieldLabel(f) {
    const wrap = f.closest('.mb-2, .mb-3, [class*="col-"], .cr-check, .cr-upload, .cr-row') || f.parentElement;
    const lab = wrap && wrap.querySelector('.form-label, .cr-upload-txt, span');
    if (lab) return lab.childNodes[0] ? lab.childNodes[0].textContent.trim() : lab.textContent.trim();
    return f.name || 'This field';
  }

  function isVisibleField(f) {
    if (f.disabled || f.type === 'hidden') return false;
    if (f.classList.contains('cr-hp')) return false;
    if (f.offsetParent === null && f.type !== 'file' && f.type !== 'checkbox' && f.type !== 'radio') return false;
    const style = window.getComputedStyle(f);
    if (style.visibility === 'hidden' || style.display === 'none') return false;
    let el = f.parentElement;
    while (el && el !== form) {
      if (el.classList.contains('d-none')) return false;
      el = el.parentElement;
    }
    return true;
  }

  function validateStep(n, { preferAlert = false } = {}) {
    const section = steps[n - 1];
    const fields = $$('input, select, textarea', section).filter(isVisibleField);
    for (const f of fields) {
      if (!f.checkValidity()) {
        section.classList.add('was-validated');
        const msg = f.validationMessage || ('Please fill: ' + fieldLabel(f));
        const useAlert = preferAlert || f.type === 'file' || f.type === 'checkbox';
        if (useAlert) {
          alertErr(msg);
        } else {
          try { f.reportValidity(); } catch (_) { alertErr(msg); }
        }
        try { f.focus({ preventScroll: false }); } catch (_) { f.focus(); }
        f.scrollIntoView({ behavior: 'smooth', block: 'center' });
        return false;
      }
    }
    return true;
  }

  $('#crNext').addEventListener('click', () => {
    if (!validateStep(current)) return;
    const p = path();
    const i = pathIndex(current);
    if (i < p.length - 1) show(p[i + 1]);
  });
  $('#crBack').addEventListener('click', () => {
    const p = path();
    const i = pathIndex(current);
    if (i > 0) show(p[i - 1]);
  });

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
    $$('input, textarea', $('#crExpBlock')).forEach(f => {
      f.disabled = on;
      // Drop required so hidden experience rows never block mobile submit
      if (f.dataset.wasRequired === undefined) {
        f.dataset.wasRequired = f.required ? '1' : '0';
      }
      f.required = on ? false : f.dataset.wasRequired === '1';
    });
    if (!on) calcExperience();
  });
  addRow('tplExp', 'crExpList');

  /* Internship: short form — Basic Details + Degree + Technology only */
  function syncInternMode() {
    const intern = isIntern();
    form.classList.toggle('cr-intern-mode', intern);

    $$('.cr-full-only').forEach(el => el.classList.toggle('d-none', intern));
    $('#crBasicHint')?.classList.toggle('d-none', !intern);

    const eduStep = $('#crStepEdu');
    if (eduStep) {
      eduStep.dataset.title = intern ? 'Degree & Interest' : 'Education';
      if (current === 3) $('#crStepTitle').textContent = eduStep.dataset.title;
    }
    $('#crInternSimple')?.classList.toggle('d-none', !intern);
    $('#crFullEdu')?.classList.toggle('d-none', intern);

    const deg = $('#crInternDegree');
    const tech = $('#crInternTech');
    if (deg) deg.required = intern;
    if (tech) tech.required = intern;

    // Disable full education rows so they don't block validation / submit
    $$('#crEduList input, #crEduList textarea, #crEduList select').forEach(f => {
      f.disabled = intern;
      if (intern) f.required = false;
    });
    $('#crAddEdu')?.classList.toggle('d-none', intern);

    // Skills step fields
    const skills = $('#crSkills');
    if (skills) {
      skills.required = !intern;
      skills.disabled = intern;
    }
    $$('#crStepExp input, #crStepExp textarea, #crStepExp select').forEach(f => {
      if (intern) {
        f.disabled = true;
        f.required = false;
      }
    });
    const ctc = $('#crExpCtc');
    if (ctc) ctc.required = !intern;

    // Resume optional for internship
    const resume = $('#crResume');
    const resumeLabel = $('#crResumeLabel');
    if (resume) resume.required = !intern;
    if (resumeLabel) {
      resumeLabel.classList.toggle('req', !intern);
      resumeLabel.textContent = intern ? 'Resume / CV (optional)' : 'Resume / CV';
    }
    $('#crSubmitTitle') && ($('#crSubmitTitle').textContent = intern ? 'Review & Submit' : 'Documents & Submit');
    const submitStep = steps[5];
    if (submitStep) submitStep.dataset.title = intern ? 'Submit' : 'Documents & Submit';

    $('#crInternNote')?.classList.toggle('d-none', !intern);
    $('#crFresherWrap')?.classList.toggle('d-none', intern);
    // Position dropdown: for internship auto-pick Intern role (not asked separately)
    const posWrap = desig ? desig.closest('.mb-3') : null;
    if (posWrap) posWrap.classList.toggle('d-none', intern);
    if (desig) desig.required = !intern;

    if (intern) {
      fresher.checked = true;
      fresher.dispatchEvent(new Event('change'));
      const cur = form.elements.current_ctc;
      if (cur) cur.value = '';
      if (desig) {
        const opt = Array.from(desig.options).find(o => /intern/i.test(o.value) || /intern/i.test(o.textContent));
        desig.value = opt ? opt.value : '__other';
        if (!opt) {
          $('#crOtherWrap')?.classList.remove('d-none');
          const other = $('#crOther');
          if (other) { other.required = true; other.value = other.value || 'Intern Employee'; }
        } else {
          $('#crOtherWrap')?.classList.add('d-none');
          const other = $('#crOther');
          if (other) other.required = false;
        }
      }
    } else {
      if (posWrap) posWrap.classList.remove('d-none');
      if (desig) desig.required = true;
      if (deg) { deg.required = false; deg.value = deg.value; }
      if (tech) tech.required = false;
      if (skills) { skills.disabled = false; skills.required = true; }
      $$('#crEduList input').forEach(f => {
        f.disabled = false;
        if (f.name && f.name.includes('[degree]')) f.required = true;
      });
      $$('#crStepExp input, #crStepExp textarea, #crStepExp select').forEach(f => {
        f.disabled = fresher.checked && f.closest('#crExpBlock');
      });
      if (ctc) ctc.required = true;
      fresher.dispatchEvent(new Event('change'));
    }

    // If currently on a skipped step, jump to nearest valid
    if (!path().includes(current)) show(path()[0]);
    else show(current);
  }
  $$('input[name="employment_type"]').forEach(r => r.addEventListener('change', syncInternMode));

  /* ---------- Tech / skill tag hints ---------- */
  const skills = $('#crSkills');
  function wireTags(containerSel, inputEl) {
    if (!inputEl) return;
    $$(containerSel + ' .cr-tag').forEach(tag => tag.addEventListener('click', () => {
      const list = inputEl.value.split(',').map(s => s.trim()).filter(Boolean);
      const name = tag.textContent.trim();
      const idx = list.findIndex(s => s.toLowerCase() === name.toLowerCase());
      if (idx >= 0) list.splice(idx, 1); else list.push(name);
      inputEl.value = list.join(', ');
      syncTagState(containerSel, inputEl);
    }));
    inputEl.addEventListener('input', () => syncTagState(containerSel, inputEl));
  }
  function syncTagState(containerSel, inputEl) {
    const list = inputEl.value.toLowerCase().split(',').map(s => s.trim());
    $$(containerSel + ' .cr-tag').forEach(t => t.classList.toggle('on', list.includes(t.textContent.trim().toLowerCase())));
  }
  wireTags('#crSkillHints', skills);
  wireTags('#crInternTechHints', $('#crInternTech'));
  syncInternMode();

  /* ---------- Step 6: uploads & review ---------- */
  const MAX = 5 * 1024 * 1024;
  // Cache files in memory immediately — fixes Android Chrome ERR_UPLOAD_FILE_CHANGED
  let cachedResume = null; // { file: File, name: string }
  let cachedPhoto = null;

  async function cacheFile(file) {
    const buffer = await file.arrayBuffer();
    return new File([buffer], file.name, {
      type: file.type || 'application/octet-stream',
      lastModified: Date.now(),
    });
  }

  $('#crPhoto').addEventListener('change', async e => {
    const f = e.target.files[0];
    const img = $('#crPhotoPreview');
    cachedPhoto = null;
    if (!f) return;
    if (f.size > MAX) { e.target.value = ''; return alertErr('Photo must be under 5MB.'); }
    try {
      cachedPhoto = { file: await cacheFile(f), name: f.name };
    } catch (_) {
      e.target.value = '';
      return alertErr('Could not read photo. Please choose the file again from Phone storage (not Drive).');
    }
    img.src = URL.createObjectURL(cachedPhoto.file);
    img.classList.remove('d-none');
    $('#crPhotoBox .cr-upload-txt').classList.add('d-none');
    $('#crPhotoBox').classList.add('has-file');
  });
  $('#crResume').addEventListener('change', async e => {
    const f = e.target.files[0];
    cachedResume = null;
    if (!f) return;
    if (f.size > MAX) { e.target.value = ''; return alertErr('Resume must be under 5MB.'); }
    try {
      cachedResume = { file: await cacheFile(f), name: f.name };
    } catch (_) {
      e.target.value = '';
      return alertErr('Could not read resume. Please choose PDF from Phone Files / Downloads (not Google Drive).');
    }
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
    let rows;
    if (intern) {
      rows = [
        ['Type', 'Internship'],
        ['Name', name],
        ['Mobile', val('mobile')],
        ['Parents Contact', val('parent_mobile')],
        ['Email', val('email')],
        ['Degree', ($('#crInternDegree') && $('#crInternDegree').value.trim()) || '—'],
        ['Technology Interest', ($('#crInternTech') && $('#crInternTech').value.trim()) || '—'],
      ];
    } else {
      const exp = fresher.checked ? 'Fresher' : $('#crTotalExp').textContent;
      rows = [
        ['Position', pos], ['Type', val('employment_type')],
        ['Name', name], ['Mobile', val('mobile')], ['Parents Contact', val('parent_mobile')], ['Email', val('email')],
        ['Experience', exp], ['Expected CTC', val('expected_ctc') ? '₹ ' + Number(val('expected_ctc')).toLocaleString('en-IN') + ' / year' : '—'],
        ['Primary skills', val('primary_skills')],
      ];
    }
    $('#crReview').innerHTML = rows.map(r =>
      '<div class="cr-review-row"><span>' + r[0] + '</span><b>' + escapeHtml(r[1] || '—') + '</b></div>'
    ).join('');
  }

  /** Validate a step without toggling visibility (avoids clearing file inputs on mobile). */
  function validateStepHidden(n, { preferAlert = false } = {}) {
    const section = steps[n - 1];
    const fields = $$('input, select, textarea', section).filter(f => {
      if (f.disabled || f.type === 'hidden' || f.classList.contains('cr-hp')) return false;
      // File inputs: use cache, skip HTML required check against live FileList
      if (f.type === 'file') return false;
      let el = f.parentElement;
      while (el && el !== section) {
        if (el.classList.contains('d-none')) return false;
        el = el.parentElement;
      }
      return true;
    });
    for (const f of fields) {
      if (!f.checkValidity()) {
        show(n);
        return validateStep(n, { preferAlert });
      }
    }
    return true;
  }

  function buildFormData() {
    // Start from form, then replace file fields with in-memory copies
    const fd = new FormData(form);
    fd.delete('resume');
    fd.delete('photo');
    if (cachedResume) fd.append('resume', cachedResume.file, cachedResume.name);
    if (cachedPhoto) fd.append('photo', cachedPhoto.file, cachedPhoto.name);

    if (isIntern()) {
      const degree = ($('#crInternDegree') && $('#crInternDegree').value.trim()) || '';
      const tech = ($('#crInternTech') && $('#crInternTech').value.trim()) || '';
      fd.set('primary_skills', tech);
      fd.set('is_fresher', '1');
      fd.set('employment_type', 'intern');
      fd.set('edu[0][degree]', degree);
      fd.delete('expected_ctc');
      fd.delete('current_ctc');
    }
    return fd;
  }

  function postApplication(fd, onProgress) {
    return new Promise((resolve, reject) => {
      const xhr = new XMLHttpRequest();
      const url = window.location.pathname + window.location.search;
      xhr.open('POST', url, true);
      xhr.timeout = 180000;
      xhr.withCredentials = true;
      xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
      xhr.setRequestHeader('Accept', 'application/json');
      if (xhr.upload && onProgress) {
        xhr.upload.onprogress = ev => {
          if (ev.lengthComputable) onProgress(Math.round((ev.loaded / ev.total) * 100));
        };
      }
      xhr.onload = () => {
        let data = null;
        try { data = JSON.parse(xhr.responseText || '{}'); } catch (_) {}
        if (xhr.status >= 200 && xhr.status < 300) {
          // JSON success
          if (data && data.ok && data.redirect) {
            resolve(data);
            return;
          }
          // HTML thank-you redirect response (rare)
          if (xhr.responseURL && /thank-you/i.test(xhr.responseURL)) {
            resolve({ ok: true, redirect: xhr.responseURL });
            return;
          }
        }
        if (data && data.message) {
          reject(new Error(data.message));
          return;
        }
        if (xhr.status === 413) {
          reject(new Error('Resume file is too large. Please upload a PDF under 2MB.'));
          return;
        }
        reject(new Error('Could not submit application. Please try again.'));
      };
      xhr.onerror = () => reject(new Error('Upload failed. Please check network and try again.'));
      xhr.ontimeout = () => reject(new Error('Upload timed out. Try a smaller PDF or Wi‑Fi.'));
      xhr.onabort = () => reject(new Error('Upload cancelled.'));
      xhr.send(fd);
    });
  }

  /* ---------- Submit via XHR + cached file blobs (fixes ERR_UPLOAD_FILE_CHANGED) ---------- */
  form.addEventListener('submit', async e => {
    e.preventDefault();
    e.stopPropagation();

    for (const step of path()) {
      if (!validateStepHidden(step, { preferAlert: true })) return;
    }

    const intern = isIntern();
    if (!intern && !cachedResume) {
      alertErr('Please upload your resume (PDF / DOC / DOCX) from Phone storage.');
      $('#crResume')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
      return;
    }
    if (cachedResume && cachedResume.file.size > MAX) {
      alertErr('Resume must be under 5MB. Please use a smaller PDF.');
      return;
    }
    if (!$('input[name="declaration"]').checked) {
      alertErr('Please accept the declaration before submitting.');
      return;
    }

    const btn = $('#crSubmit');
    if (btn.disabled) return;
    btn.disabled = true;
    const setBtn = (label) => {
      btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>' + label;
    };
    setBtn(cachedResume ? 'Uploading…' : 'Submitting…');

    try {
      // Temporarily enable fields so FormData picks values (disabled are skipped)
      const disabled = $$('input, select, textarea', form).filter(f => f.disabled);
      disabled.forEach(f => { f.disabled = false; });
      const fd = buildFormData();
      disabled.forEach(f => { f.disabled = true; });

      const data = await postApplication(fd, pct => {
        setBtn(pct < 100 ? ('Uploading… ' + pct + '%') : 'Saving…');
      });
      if (data.ok && data.redirect) {
        window.location.href = data.redirect;
        return;
      }
      alertErr((data && data.message) || 'Could not submit application.');
    } catch (err) {
      alertErr((err && err.message) || 'Could not submit. Please try again.');
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
