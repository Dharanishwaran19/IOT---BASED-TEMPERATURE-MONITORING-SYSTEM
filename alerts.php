<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/layout.php';
$u = require_login();
$emailOn = setting('email_enabled', '0') === '1' && trim((string)setting('mail_recipients', '')) !== '';
page_start('alerts', 'Emergency alerts', $u);
?>
<div class="grid-2">
  <section class="panel">
    <div class="panel-head"><h2>Active emergencies</h2><div class="actions"><button class="btn btn-sm" id="btn-ack-all" hidden>Acknowledge all</button></div></div>
    <div id="active"></div>
  </section>
  <section class="panel">
    <div class="panel-head"><h2>Alarm and email</h2></div>
    <div class="panel-body stack" style="gap:12px">
      <div class="row"><span class="muted">Alarm sound</span><strong id="alarm-state">—</strong></div>
      <div class="row">
        <button class="btn" id="btn-test">Test alarm (3 s)</button>
        <button class="btn" id="btn-mute">Mute alarm</button>
        <button class="btn" id="btn-unmute">Unmute</button>
      </div>
      <p class="muted" style="margin:0">The alarm sounds until every emergency is acknowledged or muted, and sounds again for any new emergency. Browsers block sound until you click the page once.</p>
      <div class="row"><span class="muted">Email to maintenance</span>
        <strong><?= $emailOn ? 'On' : 'Off' ?></strong>
        <?php if ($u['role'] === 'admin'): ?><a class="btn btn-sm" href="settings.php#email">Email settings</a><?php endif; ?>
      </div>
      <?php if (!$emailOn): ?><p class="muted" style="margin:0">Emergencies are recorded but no email is sent. <?= $u['role'] === 'admin' ? 'Add recipients and turn on email in Settings.' : 'Ask an administrator to set up email alerts.' ?></p><?php endif; ?>
    </div>
  </section>
</div>

<section class="panel">
  <div class="panel-head">
    <h2>Alert history</h2>
    <form class="actions" id="filters">
      <select name="room" aria-label="Room"><option value="active">This room</option><option value="all">All rooms</option></select>
      <select name="status" aria-label="Status"><option value="">Any status</option><option value="active">Active</option><option value="resolved">Resolved</option></select>
      <select name="type" aria-label="Type"><option value="">Too warm or too cold</option><option value="HIGH">Too warm</option><option value="LOW">Too cold</option></select>
      <input type="date" name="from" aria-label="From date">
      <input type="date" name="to" aria-label="To date">
      <button class="btn btn-sm" type="submit">Filter</button>
    </form>
  </div>
  <div class="table-wrap">
    <table class="data">
      <thead><tr><th>Started</th><th>Zone</th><th>Condition</th><th class="num">Detected</th><th class="num">Worst</th><th class="num">Range</th>
        <th>Probable cause</th><th class="num">Duration</th><th>Acknowledged</th><th>Email</th><th>Status</th></tr></thead>
      <tbody id="history"></tbody>
    </table>
  </div>
</section>
<?php page_end(['alerts.js']);
