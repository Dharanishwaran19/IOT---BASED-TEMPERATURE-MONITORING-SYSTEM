<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/layout.php';
$u = require_login();
$admin = $u['role'] === 'admin';
page_start('settings', $admin ? 'Settings' : 'My account', $u);

function num_field(string $name, string $label, string $step, string $min, string $max, string $hint = ''): void
{
    echo '<div class="field"><label for="s-' . $name . '">' . e($label) . '</label><input id="s-' . $name . '" name="' . $name
       . '" type="number" step="' . $step . '" min="' . $min . '" max="' . $max . '">'
       . ($hint ? '<span class="hint">' . e($hint) . '</span>' : '') . '</div>';
}
?>
<?php if ($admin): ?>
<div class="tabs" role="tablist">
  <button role="tab" data-tab="monitoring" aria-selected="true">Monitoring</button>
  <button role="tab" data-tab="sensor" aria-selected="false">Reference sensor</button>
  <button role="tab" data-tab="email" aria-selected="false">Email alerts</button>
  <button role="tab" data-tab="alarm" aria-selected="false">Alarm sound</button>
  <button role="tab" data-tab="users" aria-selected="false">Users</button>
  <button role="tab" data-tab="account" aria-selected="false">My account</button>
</div>

<section class="tab-panel" id="tab-monitoring" role="tabpanel">
  <form class="panel panel-body" data-settings novalidate>
    <h2>Monitoring</h2>
    <div class="form-grid" style="margin-top:12px">
      <?php num_field('poll_interval', 'Dashboard refresh (s)', '1', '1', '30');
      num_field('history_interval', 'Store readings every (s)', '1', '2', '3600');
      num_field('retention_days', 'Keep readings for (days)', '1', '1', '3650');
      num_field('warning_margin', 'Warning margin (°C)', '0.1', '0', '20', 'Inside a limit by less than this counts as a warning.');
      num_field('hysteresis', 'Recovery margin (°C)', '0.1', '0', '5', 'A zone must come back this far inside its limit before an emergency clears.');
      num_field('sim_speed', 'Simulation speed (×)', '1', '0', '600', '0 pauses the simulation.'); ?>
    </div>
    <h3 style="margin-top:22px">Estimation model</h3>
    <p class="muted">Adjust these when calibrating against real measurements. The Monitoring page explains the formula.</p>
    <div class="form-grid">
      <?php num_field('leak_base', 'Wall heat leakage', '0.1', '0', '20');
      num_field('door_leak', 'Open-door heat leakage', '0.1', '0', '50');
      num_field('cool_strength', 'Cooling-unit strength', '0.1', '0', '50');
      num_field('door_reach', 'Door air reach (m)', '0.1', '0.5', '30');
      num_field('corr_length', 'Sensor correction reach (m)', '0.1', '0.5', '50');
      num_field('sim_noise', 'Simulation noise (± °C)', '0.01', '0', '2'); ?>
    </div>
    <div class="form-actions"><button class="btn btn-primary" type="submit">Save monitoring settings</button></div>
  </form>
</section>

<section class="tab-panel" id="tab-sensor" role="tabpanel" hidden>
  <div class="stack">
    <section class="panel">
      <div class="panel-head"><h2>Data source per room</h2></div>
      <div class="table-wrap"><table class="data">
        <thead><tr><th>Room</th><th>Mode</th><th>Sensor status</th><th class="num">Last reading</th></tr></thead>
        <tbody id="sensor-rooms"></tbody></table></div>
      <div class="panel-body"><p class="muted" style="margin:0">Simulation mode generates the reference reading in software. Hardware mode uses readings sent by the physical sensor and estimates every other zone from them.</p></div>
    </section>
    <section class="panel">
      <div class="panel-head"><h2>Device connection</h2></div>
      <div class="panel-body stack" style="gap:14px">
        <div class="field"><label>Endpoint</label><code id="sensor-url"></code></div>
        <div class="field"><label>API key</label>
          <div class="row"><code id="sensor-key">—</code><button class="btn btn-sm" id="btn-regen">Generate new key</button></div>
          <span class="hint">Put this key in the ESP32 sketch (hardware folder). Generating a new key disconnects devices using the old one.</span></div>
        <form class="row" data-settings novalidate>
          <?php num_field('sensor_timeout', 'Offline after (s) without data', '1', '5', '3600'); ?>
          <button class="btn btn-sm" type="submit" style="align-self:flex-end">Save</button>
        </form>
        <div class="field"><label>Test from a command prompt</label><pre class="code" id="sensor-curl"></pre></div>
        <form id="manual-reading" class="row" novalidate>
          <div class="field"><label for="mr-room">Send a test reading to</label><select id="mr-room" name="room_id"></select></div>
          <div class="field"><label for="mr-temp">Temperature °C</label><input id="mr-temp" name="temperature" type="number" step="0.1" value="4.5" style="width:110px"></div>
          <button class="btn btn-sm" type="submit" style="align-self:flex-end">Send reading</button>
        </form>
      </div>
    </section>
  </div>
</section>

<section class="tab-panel" id="tab-email" role="tabpanel" hidden>
  <form class="panel panel-body" data-settings id="email-form" novalidate>
    <h2>Email alerts to the maintenance team</h2>
    <label class="check" style="margin:14px 0"><input type="checkbox" name="email_enabled"> Send an email for every new emergency</label>
    <div class="form-grid">
      <div class="field wide"><label for="s-mail_recipients">Recipients</label>
        <textarea id="s-mail_recipients" name="mail_recipients" placeholder="maintenance@example.com, supervisor@example.com"></textarea>
        <span class="hint">Separate addresses with commas or new lines.</span></div>
      <div class="field"><label for="s-smtp_host">SMTP server</label><input id="s-smtp_host" name="smtp_host" placeholder="smtp.gmail.com"></div>
      <?php num_field('smtp_port', 'Port', '1', '1', '65535', '587 for TLS, 465 for SSL'); ?>
      <div class="field"><label for="s-smtp_secure">Encryption</label>
        <select id="s-smtp_secure" name="smtp_secure"><option value="tls">STARTTLS (587)</option><option value="ssl">SSL (465)</option><option value="none">None</option></select></div>
      <div class="field"><label for="s-smtp_user">SMTP username</label><input id="s-smtp_user" name="smtp_user" autocomplete="off"></div>
      <div class="field"><label for="s-smtp_pass">SMTP password</label><input id="s-smtp_pass" name="smtp_pass" type="password" autocomplete="new-password">
        <span class="hint" id="pass-hint">For Gmail use a 16-character app password.</span></div>
      <div class="field"><label for="s-mail_from">Sender address</label><input id="s-mail_from" name="mail_from" type="email" placeholder="Same as username"></div>
      <div class="field"><label for="s-mail_from_name">Sender name</label><input id="s-mail_from_name" name="mail_from_name"></div>
      <?php num_field('email_max_attempts', 'Attempts per email', '1', '1', '10', 'Failed emails retry after 1, 2, 3… minutes.'); ?>
    </div>
    <div class="form-actions">
      <button class="btn" type="button" id="btn-test-email">Send test email</button>
      <button class="btn btn-primary" type="submit">Save email settings</button>
    </div>
  </form>
</section>

<section class="tab-panel" id="tab-alarm" role="tabpanel" hidden>
  <form class="panel panel-body" data-settings novalidate>
    <h2>Alarm sound</h2>
    <label class="check" style="margin:14px 0"><input type="checkbox" name="alarm_enabled"> Play an alarm in the browser during emergencies</label>
    <div class="form-grid">
      <div class="field"><label for="s-alarm_tone">Sound</label><select id="s-alarm_tone" name="alarm_tone"><option value="siren">Siren</option><option value="beep">Fast beeps</option></select></div>
      <div class="field"><label for="s-alarm_volume">Volume</label><input id="s-alarm_volume" name="alarm_volume" type="range" min="0.1" max="1" step="0.05"></div>
    </div>
    <div class="form-actions"><button class="btn" type="button" id="btn-test-alarm">Test sound</button><button class="btn btn-primary" type="submit">Save alarm settings</button></div>
  </form>
</section>

<section class="tab-panel" id="tab-users" role="tabpanel" hidden>
  <div class="stack">
    <section class="panel">
      <div class="panel-head"><h2>Users</h2></div>
      <div class="table-wrap"><table class="data">
        <thead><tr><th>Name</th><th>Username</th><th>Role</th><th>Status</th><th>Last sign-in</th><th></th></tr></thead>
        <tbody id="user-rows"></tbody></table></div>
    </section>
    <form class="panel panel-body" id="user-form" novalidate>
      <h2>Add a user</h2>
      <div class="form-grid" style="margin-top:12px">
        <div class="field"><label for="u-name">Full name</label><input id="u-name" name="full_name" required maxlength="100"></div>
        <div class="field"><label for="u-user">Username</label><input id="u-user" name="username" required maxlength="50" autocomplete="off"></div>
        <div class="field"><label for="u-email">Email (optional)</label><input id="u-email" name="email" type="email"></div>
        <div class="field"><label for="u-role">Role</label><select id="u-role" name="role"><option value="operator">Operator</option><option value="admin">Administrator</option></select></div>
        <div class="field"><label for="u-pass">Temporary password</label><input id="u-pass" name="password" type="text" autocomplete="off">
          <span class="hint">They must change it at first sign-in.</span></div>
      </div>
      <p class="muted" style="margin-top:12px">Operators can monitor, simulate, edit rooms and zones, and acknowledge emergencies. Administrators also manage settings, users and room deletion.</p>
      <div class="form-error" id="user-error"></div>
      <div class="form-actions"><button class="btn btn-primary" type="submit">Add user</button></div>
    </form>
  </div>
</section>
<?php endif; ?>

<section class="tab-panel" id="tab-account" role="tabpanel" <?= $admin ? 'hidden' : '' ?>>
  <section class="panel panel-body">
    <h2><?= e($u['full_name']) ?></h2>
    <p class="muted" style="margin-top:6px">Username <code><?= e($u['username']) ?></code>, <?= $admin ? 'administrator' : 'operator' ?>.</p>
    <a class="btn btn-primary" href="change_password.php">Change password</a>
  </section>
</section>
<?php page_end($admin ? ['settings.js'] : []);
