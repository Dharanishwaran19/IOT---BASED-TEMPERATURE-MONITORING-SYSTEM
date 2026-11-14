<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/layout.php';
$u = require_login();
page_start('index', 'Dashboard', $u);
?>
<section class="strip" id="strip" aria-label="Room status">
  <div><span class="k">Zones</span><span class="v" id="s-total">—</span><span class="sub">configured</span></div>
  <div><span class="k">Normal</span><span class="v" id="s-normal">—</span><span class="sub">within range</span></div>
  <div id="c-warning"><span class="k">Warning</span><span class="v" id="s-warning">—</span><span class="sub">near a limit</span></div>
  <div id="c-emergency"><span class="k">Emergency</span><span class="v" id="s-emergency">—</span><span class="sub">out of range</span></div>
  <div id="c-ref"><span class="k">Reference sensor</span><span class="v" id="s-ref">—</span><span class="sub" id="s-ref-sub">—</span></div>
  <div><span class="k">Surrounding</span><span class="v" id="s-amb">—</span><span class="sub" id="s-set">—</span></div>
  <div id="c-cool"><span class="k">Cooling</span><span class="v small" id="s-cool">—</span><span class="sub" id="s-door">—</span></div>
</section>

<div class="grid-2 wide">
  <section class="panel">
    <div class="panel-head">
      <h2 id="plan-title">Room plan</h2>
      <div class="actions">
        <label class="check"><input type="checkbox" id="heat-toggle"> Heat overlay</label>
        <a class="btn btn-sm" href="room.php">Edit room</a>
      </div>
    </div>
    <div class="panel-body"><?php room_stage(false); ?></div>
    <div class="legend">
      <span><i class="l-measured"></i>Measured</span><span><i class="l-estimated"></i>Estimated</span>
      <span><i class="l-simulated"></i>Simulated</span><span><i class="l-warning"></i>Warning</span><span><i class="l-emergency"></i>Emergency</span>
    </div>
  </section>
  <div class="stack">
    <section class="panel">
      <div class="panel-head"><h2>Active emergencies</h2><div class="actions"><a class="btn btn-sm" href="alerts.php">All alerts</a></div></div>
      <div id="emergencies"></div>
    </section>
    <section class="panel">
      <div class="panel-head"><h2>Zones</h2><div class="actions"><a class="btn btn-sm" href="monitoring.php">Details</a></div></div>
      <div class="table-wrap">
        <table class="data">
          <thead><tr><th>Zone</th><th class="num">Now</th><th class="num">Range</th><th>Source</th><th>Status</th></tr></thead>
          <tbody id="zone-rows"></tbody>
        </table>
      </div>
    </section>
  </div>
</div>

<div class="grid-2">
  <section class="panel">
    <div class="panel-head"><h2>Temperatures, last 15 minutes</h2><span class="muted" id="chart-note"></span></div>
    <div class="chart-box"><canvas id="live-chart" aria-label="Zone temperatures over the last 15 minutes"></canvas></div>
  </section>
  <section class="panel">
    <div class="panel-head"><h2>Recent events</h2><div class="actions"><a class="btn btn-sm" href="history.php">History</a></div></div>
    <ul class="events" id="events"></ul>
  </section>
</div>
<?php page_end(['roomview.js', 'roomview3d.js', 'stage.js', 'charts.js', 'dashboard.js'], true, true);
