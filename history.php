<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/layout.php';
$u = require_login();
$st = db()->prepare('SELECT id, name FROM zones WHERE room_id = ? ORDER BY sort_order, id');
$st->execute([active_room_id()]);
$zones = $st->fetchAll();
page_start('history', 'Historical analysis', $u);
?>
<section class="panel">
  <form class="panel-body form-grid" id="hfilters" novalidate>
    <div class="field"><label for="h-zone">Zone</label>
      <select id="h-zone" name="zone">
        <option value="all">All zones</option>
        <option value="ref">Reference sensor</option>
        <?php foreach ($zones as $z): ?><option value="<?= (int)$z['id'] ?>"><?= e($z['name']) ?></option><?php endforeach; ?>
      </select></div>
    <div class="field"><label for="h-range">Period</label>
      <select id="h-range" name="range">
        <option value="60">Last hour</option><option value="360">Last 6 hours</option><option value="1440" selected>Last 24 hours</option>
        <option value="10080">Last 7 days</option><option value="43200">Last 30 days</option><option value="custom">Custom…</option>
      </select></div>
    <div class="field custom-range" hidden><label for="h-from">From</label><input id="h-from" type="datetime-local" name="from"></div>
    <div class="field custom-range" hidden><label for="h-to">To</label><input id="h-to" type="datetime-local" name="to"></div>
    <div class="field"><label for="h-cat">Event type</label>
      <select id="h-cat" name="category">
        <option value="">All events</option><option value="alert">Emergencies and recoveries</option>
        <option value="simulation">Simulated events</option><option value="configuration">Configuration changes</option><option value="system">Sensor and system</option>
      </select></div>
    <div class="field" style="justify-content:flex-end"><button class="btn btn-primary" type="submit">Show</button></div>
  </form>
</section>

<section class="panel">
  <div class="panel-head"><h2 id="h-title">Temperature trend</h2><span class="muted" id="h-note"></span>
    <div class="actions">
      <a class="btn btn-sm" id="x-readings" href="#">Readings CSV</a>
      <a class="btn btn-sm" id="x-events" href="#">Events CSV</a>
      <a class="btn btn-sm" id="x-alerts" href="#">Emergencies CSV</a>
    </div></div>
  <div class="chart-box tall"><canvas id="h-chart" aria-label="Temperature history chart"></canvas></div>
</section>

<section class="panel">
  <div class="panel-head"><h2>Zone summary for the period</h2></div>
  <div class="table-wrap"><table class="data">
    <thead><tr><th>Zone</th><th class="num">Allowed</th><th class="num">Lowest</th><th class="num">Average</th><th class="num">Highest</th>
      <th class="num">Time in warning</th><th class="num">Time in emergency</th><th class="num">Emergencies</th><th class="num">Samples</th></tr></thead>
    <tbody id="h-stats"></tbody></table></div>
</section>

<section class="panel">
  <div class="panel-head"><h2>Events</h2><span class="muted" id="h-ev-count"></span></div>
  <div class="table-wrap"><table class="data">
    <thead><tr><th>Time</th><th>Type</th><th>Description</th><th>By</th></tr></thead>
    <tbody id="h-events"></tbody></table></div>
</section>
<?php page_end(['charts.js', 'history.js'], true);
