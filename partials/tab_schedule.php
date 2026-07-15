<?php
/**
 * partials/tab_schedule.php
 * 21-day Shift Schedule — server-side rendered
 * Expects: $today (string Y-m-d)
 */
?>
<div id="tab-schedule" class="tab-panel">

  <div class="d-flex align-items-center justify-content-between mb-3">
    <h5 class="section-title mb-0">
      <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
      </svg>
      Shift Cycle Map
    </h5>
    <span class="badge bg-secondary">21-Day View</span>
  </div>

  <div class="schedule-grid">
    <?php for ($i = -2; $i <= 18; $i++):
      $dt      = new DateTime($today);
      $dt->modify("$i days");
      $ds      = $dt->format('Y-m-d');
      $isToday = ($i === 0);
      $holiday = getGhanaHoliday($ds);
      $shiftA  = getGroupShift($ds, 'A');
      $shiftB  = getGroupShift($ds, 'B');
      $shiftC  = getGroupShift($ds, 'C');
      $dayName = $dt->format('D');
      $dayNum  = $dt->format('j');
      $month   = $dt->format('M');
      $cardCls = $isToday ? 'schedule-card today' : 'schedule-card';
    ?>
    <div class="<?= $cardCls ?>">

      <div class="sched-date">
        <div class="sched-daynum"><?= $dayNum ?></div>
        <div class="sched-dayname"><?= $dayName ?> <?= $month ?></div>
        <?php if ($isToday): ?><span class="today-pill">Today</span><?php endif; ?>
      </div>

      <div class="sched-shifts">
        <div class="shift-badge shift-<?= strtolower($shiftA) ?>">
          <span class="shift-grp">A</span><?= $shiftA ?>
        </div>
        <div class="shift-badge shift-<?= strtolower($shiftB) ?>">
          <span class="shift-grp">B</span><?= $shiftB ?>
        </div>
        <div class="shift-badge shift-<?= strtolower($shiftC) ?>">
          <span class="shift-grp">C</span><?= $shiftC ?>
        </div>
      </div>

      <?php if ($holiday): ?>
      <div class="holiday-tag">🇬🇭 <?= htmlspecialchars($holiday) ?></div>
      <?php endif; ?>

    </div>
    <?php endfor; ?>
  </div>

</div>
