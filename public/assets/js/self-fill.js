(function () {
  const cfg = window.SF || {};
  const $ = (s, r) => (r || document).querySelector(s);
  const $$ = (s, r) => Array.from((r || document).querySelectorAll(s));

  const intro = $('#sfIntro');
  const form = $('#sfForm');
  const done = $('#sfDone');
  const steps = $$('.cr-step', form);
  let step = 1;

  function toast(icon, title) {
    if (window.Swal) Swal.fire({ toast: true, position: 'top', icon, title, showConfirmButton: false, timer: 2600 });
    else alert(title);
  }

  function showStep(n) {
    step = n;
    steps.forEach((el) => el.classList.toggle('d-none', Number(el.dataset.step) !== n));
    const total = steps.length;
    $('#sfBar').style.width = ((n / total) * 100) + '%';
    $('#sfStepTitle').textContent = steps[n - 1].dataset.title || '';
    $('#sfStepCount').textContent = 'Step ' + n + ' of ' + total;
    $('#sfPrev').disabled = n <= 1;
    $('#sfNext').classList.toggle('d-none', n >= total);
    $('#sfSubmit').classList.toggle('d-none', n < total);
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }

  function validateStep(n) {
    const pane = steps[n - 1];
    const required = $$('[required]', pane).filter((el) => el.offsetParent !== null || el.type === 'checkbox');
    for (const el of required) {
      if (el.type === 'checkbox' ? !el.checked : !(el.value || '').trim()) {
        el.classList.add('is-invalid');
        toast('warning', 'Please complete required fields');
        el.focus();
        return false;
      }
      el.classList.remove('is-invalid');
    }
    if (n === 1) {
      const mobile = (form.elements.mobile?.value || '').replace(/\D/g, '');
      if (!/^[6-9]\d{9}$/.test(mobile)) {
        form.elements.mobile?.classList.add('is-invalid');
        toast('warning', 'Enter a valid 10-digit mobile');
        return false;
      }
    }
    return true;
  }

  $('#sfStart')?.addEventListener('click', () => {
    intro.classList.add('d-none');
    form.classList.remove('d-none');
    showStep(1);
  });

  $('#sfSame')?.addEventListener('change', function () {
    $('#sfPermWrap').classList.toggle('d-none', this.checked);
  });

  /* Designation → auto-select related department */
  $('#sfDesignation')?.addEventListener('change', function () {
    const dept = this.options[this.selectedIndex]?.getAttribute('data-dept') || '';
    const deptSel = $('#sfDepartment');
    if (deptSel && dept) deptSel.value = dept;
  });

  $('#sfPrev')?.addEventListener('click', () => { if (step > 1) showStep(step - 1); });

  $('#sfNext')?.addEventListener('click', () => {
    if (!validateStep(step)) return;
    if (step < steps.length) showStep(step + 1);
  });

  form?.addEventListener('submit', async (ev) => {
    ev.preventDefault();
    if (!validateStep(step)) return;
    const btn = $('#sfSubmit');
    btn.disabled = true;
    const fd = new FormData(form);
    fd.set('_csrf', cfg.csrf);
    fd.set('mobile', (form.elements.mobile.value || '').replace(/\D/g, ''));
    try {
      const res = await fetch(cfg.submitUrl, { method: 'POST', body: fd, credentials: 'same-origin' });
      const data = await res.json().catch(() => ({ ok: false, message: 'Network error' }));
      if (!data.ok) {
        toast('error', data.message || 'Submit failed');
        btn.disabled = false;
        return;
      }
      form.classList.add('d-none');
      done.classList.remove('d-none');
      $('#sfDoneMsg').textContent = data.message + (data.employee_code ? ' · Code: ' + data.employee_code : '');
    } catch (e) {
      toast('error', 'Unable to submit. Try again.');
      btn.disabled = false;
    }
  });
})();
