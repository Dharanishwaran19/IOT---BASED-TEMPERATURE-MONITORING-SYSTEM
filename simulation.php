<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/layout.php';
$u = require_login();
page_start('simulation', 'Event simulation', $u);
?>
<div class="notice" id="hw-note" hidden>This room is in hardware mode. Events you set here still feed the estimate, for example telling it the door is open while the real sensor reports.</div>

<div class="controls">
  <section class="control">
    <h3>Door <span class="state" id="c-door-state">—</span></h3>
    <div class="row"><button class="btn" id="c-door-open">Open door</button><button class="btn" id="c-door-close">Close door</button></div>
    <div class="row">
      <label for="c-door-min" class="muted">Open for</label>
      <input id="c-door-min" type="number" min="1" max="600" step="1" value="5" style="width:80px">
      <span class="muted">sim. minutes</span>
      <button class="btn btn-sm" id="c-door-timed">Start</button>
    </div>
  </section>

  <section class="control">
    <h3>Cooling system <span class="state" id="c-cool-state">—</span></h3>
    <div class="row"><button class="btn btn-danger" id="c-cool-fail">Simulate failure</button><button class="btn" id="c-cool-ok">Restore cooling</button></div>
    <p class="muted" style="margin:0">With cooling stopped every zone drifts towards the surrounding temperature.</p>
  </section>

  <section class="control">
    <h3>Surrounding temperature</h3>
    <span class="big" id="c-amb-now">—</span>
    <div class="row">
      <input id="c-amb" type="range" min="-30" max="60" step="0.5" style="flex:1" aria-label="New surrounding temperature">
      <input id="c-amb-num" type="number" min="-30" max="60" step="0.5" style="width:84px" aria-label="New surrounding temperature in °C">
      <button class="btn btn-sm" id="c-amb-apply">Apply</button>
    </div>
  </section>

  <section class="control">
    <h3>Cooling setpoint</h3>
    <span class="big" id="c-set-now">—</span>
    <div class="row">
      <input id="c-set" type="number" min="-30" max="30" step="0.5" style="width:100px" aria-label="New cooling setpoint in °C">
      <button class="btn btn-sm" id="c-set-apply">Apply</button>
    </div>
  </section>

  <section class="control">
    <h3>Simulation speed <span class="state" id="c-clock">—</span></h3>
    <div class="speed-group" id="c-speed">
      <button class="btn btn-sm" data-speed="0">Pause</button>
      <button class="btn btn-sm" data-speed="1">1×</button>
      <button class="btn btn-sm" data-speed="10">10×</button>
      <button class="btn btn-sm" data-speed="30">30×</button>
      <button class="btn btn-sm" data-speed="60">60×</button>
      <button class="btn btn-sm" data-speed="120">120×</button>
    </div>
    <p class="muted" style="margin:0">Simulated seconds per real second, for every room.</p>
  </section>

  <section class="control">
    <h3>Scenarios</h3>
    <div class="row">
      <button class="btn btn-sm" data-scenario="door_left_open">Door left open 10 min</button>
      <button class="btn btn-sm" data-scenario="power_cut">Power cut</button>
      <button class="btn btn-sm" data-scenario="heatwave">Heatwave 42 °C</button>
      <button class="btn btn-sm" data-scenario="cold_snap">Cold snap −5 °C</button>
      <button class="btn btn-sm btn-primary" data-scenario="normal">Restore normal</button>
    </div>
  </section>
</div>

<div class="grid-2 even">
  <section class="panel">
    <div class="panel-head"><h2>Effect on zones</h2></div>
    <div class="table-wrap"><table class="data">
      <thead><tr><th>Zone</th><th class="num">Now</th><th class="num">Change since opened</th><th class="num">Range</th><th>Status</th></tr></thead>
      <tbody id="sim-zones"></tbody></table></div>
  </section>
  <section class="panel">
    <div class="panel-head"><h2>Event log</h2><div class="actions"><a class="btn btn-sm" href="history.php">Full history</a></div></div>
    <ul class="events" id="sim-events"></ul>
  </section>
</div>
<?php page_end(['simulation.js']);
