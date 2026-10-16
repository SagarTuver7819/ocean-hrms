$(function () {
  /* ---------- Toastr (Armor-style) ---------- */
  if (window.toastr) {
    toastr.options = {
      closeButton: true,
      progressBar: true,
      newestOnTop: true,
      positionClass: 'toast-top-right',
      timeOut: 3500,
      extendedTimeOut: 1500,
      showMethod: 'fadeIn',
      hideMethod: 'fadeOut'
    };

    const type = document.body.getAttribute('data-flash-type');
    const msg = document.body.getAttribute('data-flash-message');
    if (type && msg) {
      if (type === 'success') toastr.success(msg);
      else if (type === 'warning') toastr.warning(msg);
      else if (type === 'info') toastr.info(msg);
      else toastr.error(msg);
    }
  }

  window.oceanToast = function (type, message) {
    if (!window.toastr) return alert(message);
    toastr[type] ? toastr[type](message) : toastr.info(message);
  };

  /* ---------- Sidebar ---------- */
  const root = document.documentElement;
  const isDesktop = () => window.matchMedia('(min-width: 992px)').matches;

  function setCollapsed(on) {
    root.classList.toggle('sb-collapsed', on);
    try { localStorage.setItem('oceanSidebar', on ? 'collapsed' : 'expanded'); } catch (e) {}
    $('#sidebarToggle, #sidebarCollapse').attr('title', on ? 'Expand sidebar' : 'Collapse to icons');
  }
  function setMobileOpen(on) {
    root.classList.toggle('sb-mobile-open', on);
    document.body.style.overflow = on ? 'hidden' : '';
  }

  $('#sidebarToggle').on('click', function () {
    if (isDesktop()) setCollapsed(!root.classList.contains('sb-collapsed'));
    else setMobileOpen(!root.classList.contains('sb-mobile-open'));
  });
  $('#sidebarCollapse').on('click', function () {
    if (isDesktop()) setCollapsed(!root.classList.contains('sb-collapsed'));
    else setMobileOpen(false);
  });
  $('#sidebarBackdrop').on('click', function () { setMobileOpen(false); });
  $(document).on('keydown', function (e) {
    if (e.key === 'Escape' && root.classList.contains('sb-mobile-open')) setMobileOpen(false);
  });
  $('#sidebarNav').on('click', 'a', function () { if (!isDesktop()) setMobileOpen(false); });
  window.matchMedia('(min-width: 992px)').addEventListener('change', function (e) {
    if (e.matches) {
      setMobileOpen(false);
      try { root.classList.toggle('sb-collapsed', localStorage.getItem('oceanSidebar') === 'collapsed'); } catch (err) {}
    } else {
      root.classList.remove('sb-collapsed');
    }
  });

  $('.nav-group-btn').on('click', function () {
    const $group = $(this).closest('.nav-group');
    if (isDesktop() && root.classList.contains('sb-collapsed')) {
      setCollapsed(false);
      $group.addClass('open');
      return;
    }
    $group.toggleClass('open');
  });

  $('#sidebarNav .sidebar-link, #sidebarNav .nav-group-btn').each(function () {
    const label = $(this).find('.txt b').first().text().trim();
    if (label && !this.title) this.title = label;
  });


  const $search = $('#sidebarSearch');
  const $wrap = $('#sidebarSearchWrap');
  function filterMenu() {
    const q = ($search.val() || '').toLowerCase().trim();
    $wrap.toggleClass('has-value', q.length > 0);
    $('#sidebarNav .sidebar-link, #sidebarNav .nav-group').each(function () {
      const hay = (($(this).attr('data-menu') || '') + ' ' + ($(this).attr('data-menu-group') || '') + ' ' + $(this).text()).toLowerCase();
      const show = !q || hay.indexOf(q) !== -1;
      $(this).toggle(show);
      if (show && q && $(this).hasClass('nav-group')) $(this).addClass('open');
    });
  }
  $search.on('input', filterMenu);
  $('#sidebarSearchClear').on('click', function () {
    $search.val('');
    filterMenu();
    $search.focus();
  });

  function tick() {
    const d = new Date();
    const t = d.toLocaleTimeString('en-IN', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true });
    $('#liveClock').text(t);
    $('#liveDay').text(d.toLocaleDateString('en-IN', { weekday: 'long' }));
  }
  tick();
  setInterval(tick, 1000);

  /* ---------- DataTables grid ---------- */
  if ($.fn.DataTable) {
    $('.datatable').each(function () {
      const $table = $(this);
      if ($.fn.DataTable.isDataTable($table)) return;
      const exportable = $table.hasClass('dt-export');
      $table.DataTable({
        pageLength: 25,
        order: [],
        responsive: true,
        language: {
          search: '',
          searchPlaceholder: 'Search records…',
          lengthMenu: 'Show _MENU_',
          info: '_START_–_END_ of _TOTAL_',
          paginate: { previous: '‹', next: '›' },
          emptyTable: 'No records found'
        },
        dom: exportable
          ? "<'dt-top'<'dt-top-left'lB>f>r<'dt-scroll't><'dt-bottom'ip>"
          : "<'dt-top'<'dt-top-left'l>f>r<'dt-scroll't><'dt-bottom'ip>",
        buttons: exportable ? [
          { extend: 'excelHtml5', className: 'btn btn-sm btn-excel', text: '<i class="fa-solid fa-file-excel me-1"></i>Excel', exportOptions: { columns: ':not(.no-export)' } }
        ] : [],
        columnDefs: [{ targets: 'no-sort', orderable: false }]
      });
    });
  }

  if ($.fn.select2) {
    $('.select2').select2({
      width: '100%',
      theme: 'bootstrap-5',
      placeholder: 'Select…',
      allowClear: true
    });
  }

  if (window.flatpickr) {
    flatpickr('.datepicker', { dateFormat: 'Y-m-d', allowInput: true });
    flatpickr('.monthpicker', { dateFormat: 'Y-m', plugins: [] });
  }

  /* ---------- SweetAlert confirm delete / actions ---------- */
  $(document).on('click', '[data-confirm]', function (e) {
    e.preventDefault();
    const $el = $(this);
    const msg = $el.data('confirm') || 'Are you sure?';
    const title = $el.data('confirm-title') || 'Confirm';
    const form = $el.closest('form');
    const href = $el.attr('href');

    Swal.fire({
      title: title,
      text: msg,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#F5A01B',
      cancelButtonColor: '#64748b',
      confirmButtonText: 'Yes, continue',
      cancelButtonText: 'Cancel',
      reverseButtons: true
    }).then(function (result) {
      if (!result.isConfirmed) return;
      if (form.length) form[0].submit();
      else if (href) window.location.href = href;
    });
  });

  /* ---------- CRUD modal helpers ---------- */
  $(document).on('click', '[data-edit]', function () {
    const data = $(this).data('edit');
    const modalSel = $(this).data('bs-target');
    const $modal = $(modalSel);
    if (!$modal.length || !data) return;

    $modal.find('[name]').each(function () {
      const name = $(this).attr('name');
      if (!name || name === '_csrf') return;
      if (Object.prototype.hasOwnProperty.call(data, name)) {
        const v = data[name];
        if ($(this).is(':checkbox')) $(this).prop('checked', Array.isArray(v) ? v.map(String).includes(String($(this).val())) : !!Number(v));
        else $(this).val(data[name] ?? '');
      }
    });

    const action = $(this).data('action');
    if (action) $modal.find('form').attr('action', action);
    $modal.find('.modal-title-text').text($(this).data('title') || 'Edit Record');
    $modal.find('.btn-submit-text').text('Update');
    setTimeout(function () {
      $modal.find('select.select2').trigger('change');
    }, 150);
  });

  $(document).on('click', '[data-add-modal]', function () {
    const modalSel = $(this).data('add-modal') || $(this).data('bs-target');
    const $modal = $(modalSel);
    const action = $(this).data('action');
    const form = $modal.find('form')[0];
    if (form) form.reset();
    if (action) $modal.find('form').attr('action', action);
    $modal.find('.modal-title-text').text($(this).data('title') || 'Add Record');
    $modal.find('.btn-submit-text').text('Save');
    $modal.find('select').each(function () {
      $(this).val($(this).find('option:first').val() || '').trigger('change');
    });
  });

  $('.crud-modal').on('shown.bs.modal', function () {
    const $m = $(this);
    $m.find('.select2').each(function () {
      if ($(this).hasClass('select2-hidden-accessible')) {
        $(this).select2('destroy');
      }
      $(this).select2({
        width: '100%',
        theme: 'bootstrap-5',
        dropdownParent: $m
      });
    });
    if (window.flatpickr) {
      $m.find('.datepicker').each(function () {
        if (!this._flatpickr) flatpickr(this, { dateFormat: 'Y-m-d', allowInput: true });
      });
    }
  });

  $('#same_as_current').on('change', function () {
    const on = $(this).is(':checked');
    ['address','city','state','country','pincode'].forEach(function (f) {
      const src = $('[name="current_' + f + '"]');
      const dest = $('[name="permanent_' + f + '"]');
      if (on) dest.val(src.val()).prop('readonly', true);
      else dest.prop('readonly', false);
    });
  });

  /* Rupee amount in words (Indian system) under .js-salary-input */
  function rupeeWords(n) {
    const ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten', 'Eleven', 'Twelve',
      'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
    const tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];
    const units = [[10000000, 'Crore'], [100000, 'Lakh'], [1000, 'Thousand'], [100, 'Hundred']];
    const words = (x) => {
      if (x < 20) return ones[x];
      if (x < 100) return (tens[Math.floor(x / 10)] + ' ' + ones[x % 10]).trim();
      for (const [u, label] of units) if (x >= u) return (words(Math.floor(x / u)) + ' ' + label + ' ' + words(x % u)).trim();
      return '';
    };
    return words(n) + ' Rupees Only';
  }
  $(document).on('input', '.js-salary-input', function () {
    const n = Math.round(parseFloat(this.value) || 0);
    const $out = $(this).closest('div').find('.js-salary-words');
    $out.html(n > 0 ? '<b>' + rupeeWords(n) + '</b> · ₹' + (n * 12).toLocaleString('en-IN') + ' per annum' : '');
  });
  $('.js-salary-input').trigger('input');

  /* Modals marked data-auto-open show on page load; decision remarks carry into the select modal */
  $('.modal[data-auto-open]').each(function () { bootstrap.Modal.getOrCreateInstance(this).show(); });
  $('#selectModal').on('show.bs.modal', function () {
    const remarks = $('#decisionRemarks').val();
    if (remarks) $('#selectRemarks').val(remarks);
  });

  /* ---------- Punch widget: live timers + AJAX punch ---------- */
  const pad = (n) => String(n).padStart(2, '0');
  const clock = (s) => pad(Math.floor(s / 3600)) + ':' + pad(Math.floor(s % 3600 / 60)) + ':' + pad(s % 60);
  const hmText = (s) => Math.floor(s / 3600) + 'h ' + pad(Math.floor(s % 3600 / 60)) + 'm';
  let punchTimer = null;
  const initPunchWidget = function () {
    const el = document.getElementById('punchWidget');
    clearInterval(punchTimer);
    if (!el) return;
    const state = el.dataset.state, started = Date.now();
    const work0 = +el.dataset.work, break0 = +el.dataset.break, target = +el.dataset.target;
    const ring = el.querySelector('.pw-progress'), len = 2 * Math.PI * 52;
    const tick = function () {
      const live = Math.floor((Date.now() - started) / 1000);
      const work = work0 + (state === 'working' ? live : 0);
      const brk = break0 + (state === 'on_break' ? live : 0);
      $(el).find('.js-pw-work').text(clock(work));
      $(el).find('.js-pw-break').text(clock(brk).slice(3));
      $(el).find('.js-pw-breaktotal').text(brk >= 60 ? hmText(brk) : '0m');
      $(el).find('.js-pw-left').text(work >= target ? 'Target achieved' : hmText(target - work) + ' left');
      ring.style.strokeDashoffset = len * (1 - Math.min(1, work / target));
    };
    ring.style.strokeDasharray = len;
    tick();
    if (state === 'working' || state === 'on_break') punchTimer = setInterval(tick, 1000);
  };
  initPunchWidget();

  const sendPunch = function ($btn) {
    const el = document.getElementById('punchWidget');
    $btn.closest('.pw-actions').find('button').prop('disabled', true);
    $.ajax({
      url: el.dataset.url, method: 'POST', dataType: 'json',
      data: { _csrf: el.dataset.csrf, type: $btn.data('type'), back: el.dataset.back }
    }).done(function (res) {
      toastr.success(res.message);
      $(el).replaceWith(res.html);
      initPunchWidget();
      if (el.dataset.back === 'attendance') setTimeout(() => window.location.reload(), 1200);
    }).fail(function (xhr) {
      const res = xhr.responseJSON || {};
      toastr.error(res.message || 'Could not save attendance. Please try again.');
      if (res.html) { $(el).replaceWith(res.html); initPunchWidget(); }
      else $btn.closest('.pw-actions').find('button').prop('disabled', false);
    });
  };
  $(document).on('click', '.js-punch', function () {
    const $btn = $(this), ask = $btn.data('confirm-punch');
    if (!ask) return sendPunch($btn);
    Swal.fire({ title: 'Punch Out', text: ask, icon: 'question', showCancelButton: true, confirmButtonColor: '#F5A01B', cancelButtonColor: '#64748b', confirmButtonText: 'Yes, punch out', reverseButtons: true })
      .then((r) => { if (r.isConfirmed) sendPunch($btn); });
  });

  /* ---------- Copy / reveal credentials ---------- */
  $(document).on('click', '.js-copy', function () {
    const text = String($(this).data('copy'));
    const done = () => toastr.success('Copied to clipboard');
    if (navigator.clipboard && window.isSecureContext) navigator.clipboard.writeText(text).then(done);
    else { const t = $('<textarea>').val(text).appendTo('body').select(); document.execCommand('copy'); t.remove(); done(); }
  });
  $(document).on('click', '.js-reveal', function () {
    const $code = $(this).siblings('.js-secret'), shown = $code.data('shown');
    $code.text(shown ? '••••••••••' : $code.data('secret')).data('shown', !shown);
    $(this).find('i').toggleClass('fa-eye fa-eye-slash');
  });

  /* ---------- Shift assignment: set all ---------- */
  $(document).on('change', '.js-shift-all', function () {
    if (this.value !== '') $('.js-shift-pick').val(this.value);
  });

  /* ---------- Leave form: live working-day count ---------- */
  const $leaveForm = $('#leaveForm');
  const leaveDays = function () {
    const from = $leaveForm.find('[name=from_date]').val(), to = $leaveForm.find('[name=to_date]').val();
    const $half = $leaveForm.find('[name=half_day]');
    $half.closest('.form-check').toggleClass('d-none', !from || from !== to);
    if (!from || !to) return $('#leaveDays').text('Select dates to see leave days');
    $.getJSON($leaveForm.data('days-url'), { from: from, to: to, half_day: $half.is(':checked') ? 1 : 0 }, function (r) {
      $('#leaveDays').html(r.message);
    });
  };
  if ($leaveForm.length) {
    $leaveForm.on('change', '[name=from_date], [name=to_date], [name=half_day]', function () {
      const $to = $leaveForm.find('[name=to_date]'), from = $leaveForm.find('[name=from_date]').val();
      if (from && (!$to.val() || $to.val() < from)) $to.val(from);
      $to.attr('min', from);
      leaveDays();
    });
    leaveDays();
  }

  /* ---------- Notifications: badge + dropdown polling ---------- */
  const $notif = $('#notifWrap');
  let lastNotifId = +($notif.data('last') || 0);
  const pollNotifications = function () {
    $.getJSON($notif.data('feed'), function (r) {
      $('#notifCount').text(r.unread > 0 ? (r.unread > 99 ? '99+' : r.unread) : '');
      $('#notifList').html(r.html);
      (r.items || []).filter((n) => n.id > lastNotifId && !n.is_read).reverse().forEach((n) => toastr.info(n.message, n.title));
      if (r.items && r.items.length) lastNotifId = Math.max(lastNotifId, r.items[0].id);
    });
  };
  if ($notif.length) setInterval(pollNotifications, 45000);

  /* Tabbed forms: Previous / Next steps, and reveal the tab holding an invalid field */
  const stepState = function ($form) {
    const $tabs = $form.find('[data-bs-toggle="tab"]');
    const i = $tabs.index($tabs.filter('.active'));
    $form.find('.js-step[data-step="-1"]').prop('disabled', i <= 0);
    $form.find('.js-step[data-step="1"]').toggleClass('d-none', i >= $tabs.length - 1);
    $form.find('.js-step-label').text('Step ' + (i + 1) + ' of ' + $tabs.length);
  };
  $(document).on('click', '.js-step', function () {
    const $form = $(this).closest('form');
    const $tabs = $form.find('[data-bs-toggle="tab"]');
    const target = $tabs.get($tabs.index($tabs.filter('.active')) + Number($(this).data('step')));
    if (target) bootstrap.Tab.getOrCreateInstance(target).show();
  });
  $(document).on('shown.bs.tab', 'form [data-bs-toggle="tab"]', function () { stepState($(this).closest('form')); });
  $('form .js-step-label').each(function () { stepState($(this).closest('form')); });
  document.addEventListener('invalid', function (e) {
    const pane = e.target.closest('.tab-pane:not(.active)');
    const trigger = pane && document.querySelector('[data-bs-target="#' + pane.id + '"]');
    if (trigger) bootstrap.Tab.getOrCreateInstance(trigger).show();
  }, true);

  /* Profile photo: preview instantly, then upload */
  $(document).on('change', '.js-photo-input', function () {
    const file = this.files[0];
    if (!file) return;
    if (!/^image\/(jpeg|png|webp)$/.test(file.type)) { toastr.error('Please choose a JPG, PNG or WEBP image.'); this.value = ''; return; }
    if (file.size > 3 * 1024 * 1024) { toastr.error('Photo must be smaller than 3 MB.'); this.value = ''; return; }
    const $avatar = $(this).closest('.ep-avatar-wrap').find('.ep-avatar');
    $avatar.addClass('has-photo uploading').html($('<img>', { src: URL.createObjectURL(file), alt: '' }));
    $(this).closest('form').trigger('submit');
  });

  /* Form submit loading state */
  $(document).on('submit', 'form.js-loading', function () {
    const $btn = $(this).find('button[type=submit]');
    $btn.prop('disabled', true).addClass('disabled');
    const txt = $btn.find('.btn-submit-text');
    if (txt.length) txt.text('Please wait…');
  });
});
