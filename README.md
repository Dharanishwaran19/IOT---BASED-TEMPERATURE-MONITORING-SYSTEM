# IoT Multi-Zone Temperature Monitoring and Estimation System

A web application, written in PHP with a MySQL database, that runs on XAMPP. It monitors many storage zones from one reference temperature sensor. Temperatures in the other zones are estimated in software from that sensor, the room layout and what is happening in the room. The app includes a customizable 3D virtual storage room, event simulation, an audible alarm, email alerts to the maintenance team, history with CSV export, and sign-in with roles.

## Requirements

- XAMPP with PHP 8.0 or newer (PHP 8.2 recommended) and MySQL/MariaDB
- A modern browser (Chrome, Edge or Firefox)
- Optional: an ESP32 with a DS18B20 sensor for real readings
- Optional: an email account with SMTP access (Gmail works) for alert emails

Chart.js, three.js (for the 3D room) and PHPMailer are included in the project folders, so no Composer, npm or internet connection is needed to run it.

## Installation

1. Copy the `iot_temp_monitor` folder into `C:\xampp\htdocs\`.
2. Open the XAMPP Control Panel and start **Apache** and **MySQL**.
3. Open http://localhost/phpmyadmin, choose **Import**, select `database/schema.sql` and click **Go**. This creates the `iot_temp_monitor` database with a demo room of six zones.
4. Open http://localhost/iot_temp_monitor
5. Sign in with username `admin` and password `Admin@123`. You must choose a new password straight away.

If your MySQL root user has a password, or you changed the folder name or time zone, edit `config.php`.

Importing `schema.sql` again resets everything, including users and history.

## A five-minute demo

1. **Dashboard**: all six zones are normal, and the reference sensor shows a simulated reading.
2. **Simulation**: set the speed to **60×**, then click **Power cut**. Within about a minute, zones go into emergency one after another. Each zone's insulation, its distance from the cooling unit and its thermal mass decide when it does.
3. A red banner appears on every page and the **siren plays**. Browsers only allow sound after you click the page once, so click anywhere if the banner says the sound is blocked.
4. Click **Acknowledge** on the banner, or use **Alerts** to acknowledge, mute, or see the probable cause and email status.
5. Click **Restore normal**. Zones recover and the emergencies close automatically.
6. Try **Open door** instead. Only the zone near the door goes out of range.
7. **Virtual room**: orbit the 3D room by dragging empty space, zoom with the scroll wheel and pan with the right mouse button. Drag a zone to move it, or drag its blue corner ball to resize it. The door, cooling unit and reference sensor can be dragged too. Try **Top view** and **Heat overlay**. The estimates follow the new layout.
8. **History**: view trends per zone, how often each zone was in emergency, the event log, and CSV exports.

## Pages

| Page | What it is for |
|---|---|
| Dashboard | Zone counts, reference sensor status, live 3D room, active emergencies, live 15-minute chart, recent events |
| Virtual room | 3D model of the room (switchable to a 2D floor plan): create rooms, set size, add, arrange, resize and edit zones, choose how each zone looks in 3D, move fixtures, room controls under the view, save and reset the layout |
| Zones | Table of every zone with all its settings; add, edit, delete |
| Monitoring | Zone tiles with temperature, trend and range bar, reference sensor details, estimate validation (spot checks, MAE/RMSE, calibration offsets), and how the estimate works |
| Simulation | Door (including timed openings), cooling failure, surrounding temperature, setpoint, simulation speed, ready-made scenarios |
| Alerts | Active emergencies, acknowledge, mute, alarm test, email status, resend failed emails, filtered alert history |
| History | Trend chart for any period, per-zone summary (lowest, average, highest, time in warning and emergency, recurring emergencies), events, CSV export |
| Settings | Administrators: monitoring and model settings, reference sensor and API key, email (SMTP), alarm sound, users. Operators: their own account |

## The 3D virtual room

The dashboard and the Virtual room page show the room in 3D, built with three.js. A switch above the view changes to the 2D floor plan, and the browser remembers your choice.

- **What you see**:
  - Walls, a door that swings open, and the cooling unit with cold air flowing from it.
  - The reference sensor, with a pulsing ring on the floor.
  - Each zone drawn as what it stores. "Shown in 3D as" picks shelving racks, drums, stacked crates or pallets. On Automatic, the look comes from the zone name: "Beverages" becomes drums and "Dispatch staging" becomes pallets.
- **Status**: every zone has a floating label with its name, temperature, status and data source. Normal zones stay grey. Warning zones turn amber. Emergency zones turn red and glow. Warm air drifts in while the door is open, and a failed cooler turns red and stops blowing.
- **Data source**: the outline on the floor shows where the value comes from: solid for measured, dashed for estimated, dotted for simulated.
- **Controls**: drag empty space to orbit, scroll to zoom, right-drag to pan. **Reset view**, **Top view** and **Rotate** sit above the view. Walls between you and the room turn see-through automatically.
- **Editing** (Virtual room page):
  - Drag a zone to move it, or drag the blue ball on its corner to resize it.
  - Drag the door, cooling unit or reference sensor to move them. The door snaps to the nearest wall.
  - Click a zone or its label to edit its settings on the right.
- **Room controls**: the bar under the view pauses or resumes the simulation, opens the door, simulates a cooling failure, restores normal conditions and sets the surrounding temperature.

Databases imported before the 3D update are upgraded automatically the first time a page opens.

## How the estimate works

Each zone is treated as a mass that gains heat from the surroundings and loses heat to the cooling unit:

```
leak    = leak_base × (1 − insulation) + door_open × door_leak × e^(−distance_to_door / door_reach) × (1 − 0.5 × insulation)
cooling = cooling_running × cool_strength × cooling_exposure × (0.4 + 0.6 × e^(−distance_to_cooler / cool_reach))
dT/dt   = [leak × (surrounding − T) + cooling × (setpoint − T)] / thermal_mass
```

The equation is solved exactly at every update. In **hardware mode**, the difference between the real sensor and the model's prediction at the sensor's position corrects every zone, with nearer zones corrected more:

```
estimate = model + (sensor − model_at_sensor) × e^(−distance_to_sensor / correction_length) + calibration_offset
```

A zone that contains the sensor shows the actual measured value. Each value on screen is labelled with its source: **measured** (solid outline), **estimated** (dashed) or **simulated** (dotted).

**Validation before real use:** on the Monitoring page, record readings taken with a handheld thermometer in several zones. The page reports the mean error, the MAE and the RMSE for each zone. **Apply correction** turns the mean error into a calibration offset for that zone. The model constants can be tuned under Settings > Monitoring.

## Alerts

- A zone is **warning** when it is within the warning margin of a limit (default 1 °C), and **emergency** when it is outside its range.
- An emergency clears only after the temperature comes back inside the limit by the recovery margin (default 0.3 °C). A reading hovering on the limit therefore does not raise a stream of alerts.
- Each emergency episode records:
  - the zone, the temperature at detection and the worst temperature reached
  - the limits and the data source
  - the probable cause (cooling failure, door open near the zone, high or low surrounding temperature, sensor deviation, and so on)
  - when it was acknowledged, and by whom
- **One email per episode.** A new emergency, including the same zone going out of range again later, sends a new email. Failed emails are retried after 1, 2, 3… minutes, and every attempt and error is recorded.

## Email setup (Gmail example)

1. Turn on 2-Step Verification for the Google account, then create an **App password** at https://myaccount.google.com/apppasswords.
2. Go to Settings > Email alerts and enter:
   - SMTP server `smtp.gmail.com`, port `587`, encryption STARTTLS
   - SMTP username: the full Gmail address. SMTP password: the 16-character app password.
   - Recipients: the maintenance team's addresses, separated by commas
3. Tick **Send an email for every new emergency**, click **Save**, then **Send test email**.

The `openssl` extension must be enabled in `C:\xampp\php\php.ini` (it is by default in XAMPP).

## Monitoring with nobody watching the dashboard

Estimates, alert detection and emails run whenever a dashboard page is open or the hardware sensor sends a reading. To keep monitoring running with no browser open, run the background ticker:

```
C:\xampp\php\php.exe C:\xampp\htdocs\iot_temp_monitor\cron_tick.php --loop
```

You can also create a Windows Task Scheduler task that runs it at startup.

## Connecting a real reference sensor (ESP32 + DS18B20)

1. Wire the DS18B20: red to 3.3 V, black to GND, yellow to GPIO 4. Add a 4.7 kΩ resistor between GPIO 4 and 3.3 V.
2. In the Arduino IDE, install the **OneWire** and **DallasTemperature** libraries.
3. Open `hardware/esp32_reference_sensor/esp32_reference_sensor.ino` and set the following:
   - your Wi-Fi name and password
   - `SERVER_URL`, using your PC's LAN IP address (run `ipconfig`). Do not use `localhost`.
   - `API_KEY`, from Settings > Reference sensor > Generate new key
   - `ROOM_ID`
4. Allow Apache through the Windows firewall for private networks.
5. In Settings > Reference sensor, switch the room to **Hardware sensor**. The sensor shows online once readings arrive, and zones switch to **estimated**.

Without hardware, you can test with **Send a test reading** on the same page, or with the `curl` command shown there.

## Security

- Passwords are stored as bcrypt hashes and must have at least 8 characters with a letter and a number. The default admin password must be changed at first sign-in.
- After 5 failed sign-ins, that username is locked for 15 minutes. After 20 failures, the computer's IP address is locked. The IP limit is higher because on XAMPP every user shares 127.0.0.1.
- An unknown username is rejected with the same message and in the same time as a wrong password, so attackers cannot find valid usernames.
- Sessions use HttpOnly SameSite cookies, a new session ID at sign-in, and an idle timeout. Every form and API change needs a CSRF token, and signing out also requires one.
- The user's role and active status are re-checked on every request, so deactivating a user signs them out immediately.
- All database queries use prepared statements, and all output is escaped. CSV exports neutralise spreadsheet formulas.
- The SMTP password is never sent back to the browser. The sensor endpoint requires an API key and rejects impossible readings.
- **Roles**:
  - Operators can monitor, simulate, edit rooms and zones, and acknowledge emergencies.
  - Administrators can also change settings, manage users and delete rooms.

Before using this outside a lab: set `APP_DEBUG` to `false` in `config.php`, give MySQL a password, serve over HTTPS, and note that the SMTP password is stored in the database as plain text.

## Project structure

```
iot_temp_monitor/
├── config.php                 database, time zone, app settings
├── index.php … settings.php   pages
├── login.php, logout.php, change_password.php
├── cron_tick.php              background ticker (command line)
├── api/                       JSON endpoints (state, rooms, zones, simulate, alerts, history, calibration, settings, users, sensor)
├── includes/
│   ├── bootstrap.php          database, sessions, CSRF, helpers
│   ├── auth.php               sign-in, lockout, guards
│   ├── engine.php             estimation model, alert detection, history, email queue
│   ├── mailer.php             SMTP email
│   └── layout.php             shared page layout
├── assets/css, assets/js      interface (no build step)
├── assets/vendor/chart.umd.js Chart.js 4.4.4 (MIT)
├── assets/vendor/three/       three.js r147 and OrbitControls (MIT)
├── lib/PHPMailer/             PHPMailer 6.9.3 (LGPL 2.1)
├── database/schema.sql        tables and demo data
└── hardware/                  ESP32 + DS18B20 firmware
```

## Troubleshooting

| Problem | Fix |
|---|---|
| "Cannot connect to the MySQL database" | Start MySQL in XAMPP and import `database/schema.sql`. Check `config.php`. |
| No alarm sound | Click anywhere on the page once (browser autoplay rule), check Settings > Alarm sound, and use **Test alarm** on the Alerts page. For a wall-mounted screen, allow sound for the site in the browser's site settings. |
| Test email fails | The error shown comes from the mail server. With Gmail, use an app password, not the account password. Check the port and encryption pair (587/STARTTLS or 465/SSL). |
| Sensor stays offline | The room must be in hardware mode, `SERVER_URL` must use the PC's IP, Apache must be allowed through the firewall, and the API key must match. The ESP32 serial monitor shows the HTTP response. |
| Times look wrong | Set `APP_TIMEZONE` in `config.php` to your time zone. |
| 3D view is greyed out and only the floor plan shows | The browser has no WebGL. Turn on hardware acceleration in the browser settings and update the graphics driver. Everything else works with the floor plan. |
| Locked out of admin | In phpMyAdmin, run `DELETE FROM login_attempts;`. |
