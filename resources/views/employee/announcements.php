<div class="page-head">
  <div>
    <h1>Announcements</h1>
    <p>Company notices · Open any notice to view on letterhead</p>
  </div>
</div>

<?php if (empty($items)): ?>
  <div class="panel">
    <div class="panel-body dash-empty" style="padding:48px 20px">
      <i class="fa-regular fa-bell"></i>
      No announcements right now.
    </div>
  </div>
<?php else: ?>
  <div class="ann-emp-grid">
    <?php foreach ($items as $item): ?>
      <a href="<?= url('announcements/' . $item['id']) ?>" class="ann-emp-card">
        <span class="ann-emp-ico"><i class="fa-solid fa-bullhorn"></i></span>
        <div class="ann-emp-body">
          <b><?= e($item['title']) ?></b>
          <p><?= e(mb_strimwidth((string) preg_replace('/\s+/', ' ', strip_tags((string) ($item['description'] ?? ''))), 0, 160, '…')) ?></p>
          <div class="ann-emp-meta">
            <span><i class="fa-regular fa-calendar"></i> <?= format_date($item['publish_date'] ?? null) ?: '—' ?></span>
            <?php if (!empty($item['department_name'])): ?>
              <span><i class="fa-solid fa-building"></i> <?= e($item['department_name']) ?></span>
            <?php else: ?>
              <span><i class="fa-solid fa-users"></i> All employees</span>
            <?php endif; ?>
          </div>
        </div>
        <span class="ann-emp-go"><i class="fa-solid fa-arrow-right"></i> View</span>
      </a>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<style>
.ann-emp-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 14px; }
.ann-emp-card {
  display: flex; align-items: flex-start; gap: 14px;
  padding: 16px 18px; background: #fff; border: 1px solid var(--line);
  border-radius: 16px; text-decoration: none; color: inherit;
  box-shadow: var(--shadow-sm); transition: .18s ease;
}
.ann-emp-card:hover {
  border-color: var(--brand); transform: translateY(-2px);
  box-shadow: 0 10px 22px rgba(245,160,27,.16); color: inherit;
}
.ann-emp-ico {
  width: 42px; height: 42px; border-radius: 12px; flex-shrink: 0;
  display: grid; place-items: center; background: #fff1dc; color: #e08a0a; font-size: 16px;
}
.ann-emp-body { flex: 1; min-width: 0; }
.ann-emp-body b { display: block; font-size: 15px; font-weight: 800; color: var(--ink); margin-bottom: 4px; }
.ann-emp-body p { margin: 0; font-size: 13px; color: var(--muted); line-height: 1.45; }
.ann-emp-meta {
  display: flex; flex-wrap: wrap; gap: 10px; margin-top: 10px;
  font-size: 11.5px; font-weight: 700; color: #94a3b8;
}
.ann-emp-meta i { margin-right: 4px; color: #c47800; }
.ann-emp-go {
  align-self: center; flex-shrink: 0; font-size: 12px; font-weight: 800;
  color: #c47800; white-space: nowrap;
}
</style>
