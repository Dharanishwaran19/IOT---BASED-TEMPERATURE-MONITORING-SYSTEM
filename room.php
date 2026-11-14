<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/layout.php';
$u = require_login();
page_start('room', 'Virtual storage room', $u);
?>
<div class="row">
  <p class="intro">A 3D model of the storage room. Every zone, the door, the cooling unit and the reference sensor can be moved, and every change feeds straight into the temperature estimates.</p>
  <span class="spacer"></span>
  <button class="btn" id="btn-new-room">New room</button>
  <button class="btn" id="btn-baseline" title="Use the current layout as the point that Reset room returns to">Save as reset point</button>
  <button class="btn btn-danger" id="btn-reset">Reset room</button>
</div>

<div class="grid-2 wide">
  <section class="panel">
    <div class="panel-head">
      <h2 id="room-title">Room</h2>
      <div class="actions">
        <label class="check"><input type="checkbox" id="heat-toggle"> Heat overlay</label>
        <button class="btn btn-primary btn-sm" id="btn-add-zone">Add zone</button>
      </div>
    </div>
    <div class="panel-body"><?php room_stage(true); ?></div>
    <div class="room-toolbar" aria-label="Room controls">
      <button class="btn" id="q-pause">Pause</button>
      <button class="btn" id="q-door">Open door</button>
      <button class="btn" id="q-cool">Simulate cooling failure</button>
      <button class="btn" id="q-normal">Restore normal</button>
      <div class="amb">
        <label for="q-amb">Surrounding</label>
        <input id="q-amb" type="range" min="-20" max="50" step="0.5">
        <output id="o-amb"></output>
        <button class="btn btn-sm" id="q-amb-apply">Apply</button>
      </div>
    </div>
    <div class="legend">
      <span><i class="l-measured"></i>Measured</span><span><i class="l-estimated"></i>Estimated</span>
      <span><i class="l-simulated"></i>Simulated</span><span><i class="l-warning"></i>Warning</span><span><i class="l-emergency"></i>Emergency</span>
    </div>
  </section>

  <div class="stack">
    <section class="panel">
      <div class="panel-head"><h2 id="zone-head">Zone</h2><span id="zone-live" class="muted"></span></div>
      <div class="panel-body">
        <p id="zone-empty" class="muted" style="margin:0">Click a zone in the room to edit it, or add a new zone.</p>
        <form id="zone-form" hidden novalidate>
          <div class="form-grid">
            <div class="field wide"><label for="z-name">Name</label><input id="z-name" name="name" maxlength="80" required></div>
            <div class="field"><label for="z-min">Minimum °C</label><input id="z-min" name="min" type="number" step="0.1" required></div>
            <div class="field"><label for="z-max">Maximum °C</label><input id="z-max" name="max" type="number" step="0.1" required></div>
            <div class="field"><label for="z-x">X position (m)</label><input id="z-x" name="x" type="number" step="0.1" min="0"></div>
            <div class="field"><label for="z-y">Y position (m)</label><input id="z-y" name="y" type="number" step="0.1" min="0"></div>
            <div class="field"><label for="z-w">Width (m)</label><input id="z-w" name="w" type="number" step="0.1" min="0.5"></div>
            <div class="field"><label for="z-d">Depth (m)</label><input id="z-d" name="d" type="number" step="0.1" min="0.5"></div>
            <div class="field wide"><label for="z-type">Shown in 3D as</label>
              <select id="z-type" name="display_type">
                <option value="auto">Automatic, from the zone name</option><option value="shelving">Shelving racks</option>
                <option value="drums">Drums</option><option value="crates">Stacked crates</option><option value="pallets">Pallets</option>
              </select></div>
            <div class="field wide"><label for="z-ins">Insulation: <output id="o-ins"></output></label>
              <input id="z-ins" name="insulation" type="range" min="0" max="0.95" step="0.05">
              <span class="hint">Higher keeps outside heat and door air away from the zone.</span></div>
            <div class="field wide"><label for="z-cf">Cooling exposure: <output id="o-cf"></output></label>
              <input id="z-cf" name="cooling_factor" type="range" min="0" max="2" step="0.05">
              <span class="hint">How much cold air from the cooling unit reaches the zone (1 = typical).</span></div>
            <div class="field wide"><label for="z-tm">Thermal mass: <output id="o-tm"></output></label>
              <input id="z-tm" name="thermal_mass" type="range" min="5" max="200" step="1">
              <span class="hint">Heavier, fuller zones change temperature more slowly.</span></div>
          </div>
          <div class="form-error" id="zone-error"></div>
          <div class="form-actions">
            <button type="button" class="btn btn-danger" id="btn-del-zone">Delete zone</button>
            <span class="spacer"></span>
            <button type="submit" class="btn btn-primary">Save zone</button>
          </div>
        </form>
      </div>
    </section>

    <section class="panel">
      <div class="panel-head"><h2>Room size</h2></div>
      <form class="panel-body" id="room-form" novalidate>
        <div class="form-grid">
          <div class="field wide"><label for="r-name">Room name</label><input id="r-name" name="name" maxlength="100" required></div>
          <div class="field"><label for="r-w">Width (m)</label><input id="r-w" name="width" type="number" min="3" max="100" step="0.5"></div>
          <div class="field"><label for="r-d">Depth (m)</label><input id="r-d" name="depth" type="number" min="3" max="100" step="0.5"></div>
        </div>
        <div class="form-actions">
          <?php if ($u['role'] === 'admin'): ?><button type="button" class="btn btn-danger" id="btn-del-room">Delete room</button><span class="spacer"></span><?php endif; ?>
          <button type="submit" class="btn btn-primary">Save room</button>
        </div>
      </form>
    </section>
  </div>
</div>

<dialog id="dlg-room">
  <form method="dialog" id="new-room-form" novalidate>
    <div class="dlg-head"><h2>New storage room</h2><button type="button" class="dlg-close" data-close aria-label="Close">×</button></div>
    <div class="dlg-body">
      <div class="form-grid">
        <div class="field wide"><label for="n-name">Room name</label><input id="n-name" name="name" maxlength="100" required placeholder="Cold Store B"></div>
        <div class="field"><label for="n-w">Width (m)</label><input id="n-w" name="width" type="number" min="3" max="100" step="0.5" value="10"></div>
        <div class="field"><label for="n-d">Depth (m)</label><input id="n-d" name="depth" type="number" min="3" max="100" step="0.5" value="6"></div>
      </div>
      <p class="muted" style="margin-top:12px">The new room starts empty with the door, cooling unit and reference sensor in default places.</p>
      <div class="form-error" id="new-room-error"></div>
      <div class="form-actions"><button type="button" class="btn" data-close>Cancel</button><button type="submit" class="btn btn-primary">Create room</button></div>
    </div>
  </form>
</dialog>
<?php page_end(['roomview.js', 'roomview3d.js', 'stage.js', 'room.js'], false, true);
