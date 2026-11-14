<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/layout.php';
$u = require_login();
page_start('zones', 'Zone management', $u);
?>
<section class="panel">
  <div class="panel-head">
    <h2 id="zones-title">Zones</h2>
    <div class="actions">
      <a class="btn" href="room.php">Arrange on plan</a>
      <button class="btn btn-primary" id="btn-add">Add zone</button>
    </div>
  </div>
  <div class="table-wrap">
    <table class="data">
      <thead><tr>
        <th>Zone</th><th class="num">Now</th><th class="num">Min °C</th><th class="num">Max °C</th>
        <th class="num">Position (m)</th><th class="num">Size (m)</th><th class="num">Insulation</th>
        <th class="num">Cooling</th><th class="num">Mass</th><th>3D look</th><th class="num">Offset</th><th>Status</th><th></th>
      </tr></thead>
      <tbody id="rows"></tbody>
    </table>
  </div>
</section>
<p class="intro">Insulation, cooling exposure and thermal mass shape how each zone reacts in the estimate. The offset comes from calibration spot checks on the Monitoring page.</p>

<dialog id="dlg">
  <form id="zform" novalidate>
    <div class="dlg-head"><h2 id="dlg-title">Zone</h2><button type="button" class="dlg-close" data-close aria-label="Close">×</button></div>
    <div class="dlg-body">
      <div class="form-grid">
        <div class="field wide"><label for="f-name">Name</label><input id="f-name" name="name" maxlength="80" required></div>
        <div class="field"><label for="f-min">Minimum °C</label><input id="f-min" name="min" type="number" step="0.1" required></div>
        <div class="field"><label for="f-max">Maximum °C</label><input id="f-max" name="max" type="number" step="0.1" required></div>
        <div class="field"><label for="f-x">X position (m)</label><input id="f-x" name="x" type="number" step="0.1" min="0"></div>
        <div class="field"><label for="f-y">Y position (m)</label><input id="f-y" name="y" type="number" step="0.1" min="0"></div>
        <div class="field"><label for="f-w">Width (m)</label><input id="f-w" name="w" type="number" step="0.1" min="0.5"></div>
        <div class="field"><label for="f-d">Depth (m)</label><input id="f-d" name="d" type="number" step="0.1" min="0.5"></div>
        <div class="field"><label for="f-ins">Insulation (0 to 0.95)</label><input id="f-ins" name="insulation" type="number" step="0.05" min="0" max="0.95"></div>
        <div class="field"><label for="f-cf">Cooling exposure (0 to 2)</label><input id="f-cf" name="cooling_factor" type="number" step="0.05" min="0" max="2"></div>
        <div class="field"><label for="f-tm">Thermal mass (5 to 200)</label><input id="f-tm" name="thermal_mass" type="number" step="1" min="5" max="200"></div>
        <div class="field wide"><label for="f-type">Shown in 3D as</label>
          <select id="f-type" name="display_type">
            <option value="auto">Automatic, from the zone name</option><option value="shelving">Shelving racks</option>
            <option value="drums">Drums</option><option value="crates">Stacked crates</option><option value="pallets">Pallets</option>
          </select></div>
      </div>
      <div class="form-error" id="f-error"></div>
      <div class="form-actions"><button type="button" class="btn" data-close>Cancel</button><button type="submit" class="btn btn-primary" id="f-submit">Save zone</button></div>
    </div>
  </form>
</dialog>
<?php page_end(['zones.js']);
