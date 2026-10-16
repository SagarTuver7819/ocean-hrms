(function () {
  const cfg = window.SF || {};
  const $ = (s, r) => (r || document).querySelector(s);
  const $$ = (s, r) => Array.from((r || document).querySelectorAll(s));

  const intro = $('#sfIntro');
  const form = $('#sfForm');
  const done = $('#sfDone');
  const steps = $$('.cr-step', form);
  let step = 1;
  let verified = false;

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

  function fillEmployee(e) {
    verified = true;
    $('#sfEmpId').value = e.id;
    const map = {
      first_name: e.first_name, middle_name: e.middle_name, last_name: e.last_name,
      gender: e.gender, date_of_birth: e.date_of_birth, blood_group: e.blood_group,
      marital_status: e.marital_status, mobile: (e.mobile || '').replace(/\D/g, '').slice(-10),
      personal_email: e.personal_email, official_email: e.official_email, nationality: e.nationality || 'Indian',
      current_address: e.current_address, current_city: e.current_city, current_state: e.current_state,
      current_country: e.current_country || 'India', current_pincode: e.current_pincode,
      permanent_address: e.permanent_address, permanent_city: e.permanent_city, permanent_state: e.permanent_state,
      permanent_country: e.permanent_country || 'India', permanent_pincode: e.permanent_pincode,
      pan_number: e.pan_number, aadhaar_number: e.aadhaar_number,
      bank_name: e.bank_name, account_holder_name: e.account_holder_name, account_number: e.account_number,
      ifsc_code: e.ifsc_code, bank_branch: e.bank_branch, payment_mode: e.payment_mode || 'bank_transfer',
      emergency_name: e.emergency_name, emergency_relationship: e.emergency_relationship,
      emergency_mobile: e.emergency_mobile, emergency_alternate: e.emergency_alternate, emergency_address: e.emergency_address
    };
    Object.keys(map).forEach((k) => {
      const el = form.elements[k];
      if (el && map[k] != null && map[k] !== '') el.value = map[k];
    });
    if (e.same_as_current) {
      $('#sfSame').checked = true;
      $('#sfPermWrap').classList.add('d-none');
    }
    if (e.official_email) {
      $('#sfOfficial').readOnly = true;
      $('#sfOfficial').style.background = '#fff7ea';
    }
    const role = [e.designation_name, e.department_name].filter(Boolean).join(' · ');
    $('#sfRoleHint').textContent = role
      ? (e.employee_code + ' · ' + role + ' (role set by HR)')
      : (e.employee_code + ' · complete your profile');
    const badge = $('#sfEmpBadge');
    badge.style.display = 'block';
    badge.innerHTML = '<b>Verified:</b> ' + e.first_name + ' ' + e.last_name + ' · ' + e.employee_code;
  }

  async function verify() {
    const code = ($('#sfCode').value || '').trim().toUpperCase();
    const mobile = ($('#sfMobile').value || '').replace(/\D/g, '');
    if (!code || !/^[6-9]\d{9}$/.test(mobile)) {
      toast('warning', 'Enter Employee Code and valid 10-digit mobile');
      return false;
    }
    const fd = new FormData();
    fd.append('_csrf', cfg.csrf);
    fd.append('employee_code', code);
    fd.append('mobile', mobile);
    const res = await fetch(cfg.verifyUrl, { method: 'POST', body: fd, credentials: 'same-origin' });
    const data = await res.json().catch(() => ({ ok: false, message: 'Network error' }));
    if (!data.ok) {
      toast('error', data.message || 'Verification failed');
      verified = false;
      return false;
    }
    fillEmployee(data.employee);
    toast('success', 'Verified');
    return true;
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

  $('#sfPrev')?.addEventListener('click', () => { if (step > 1) showStep(step - 1); });

  $('#sfNext')?.addEventListener('click', async () => {
    if (step === 1) {
      const ok = await verify();
      if (!ok) return;
      showStep(2);
      return;
    }
    if (!validateStep(step)) return;
    if (step < steps.length) showStep(step + 1);
  });

  form?.addEventListener('submit', async (ev) => {
    ev.preventDefault();
    if (!verified) {
      toast('error', 'Please verify first');
      showStep(1);
      return;
    }
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
      $('#sfDoneMsg').textContent = data.message + (data.employee_code ? ' (' + data.employee_code + ')' : '');
    } catch (e) {
      toast('error', 'Unable to submit. Try again.');
      btn.disabled = false;
    }
  });
})();
