# Balsam — Multi-Branch Clinic Management API

A Laravel 12 REST API for managing appointments, medical records, and operations across multiple clinic branches.

---

## Features

- Multi-branch appointment booking with schedule and slot management
- Role-based access control (admin, receptionist, doctor, patient)
- Automatic appointment reference generation with concurrency-safe sequences
- SMS notifications via a swappable provider (log-based by default)
- Activity logging for appointments and medical records
- Polymorphic attachments for medical records, doctors, and branches
- Scheduled commands for reminders, no-show processing, ban releases, and monthly reports
- Redis-ready cache and queue configuration (database fallback active locally)

---

## Technology Stack

| Component | Technology |
|---|---|
| Framework | Laravel 12 |
| Language | PHP 8.2+ |
| Database | MySQL |
| Cache | Database (Redis-ready) |
| Queue | Database (Redis-ready) |
| Auth | Laravel Sanctum (token-based) |
| Queue Dashboard | Laravel Horizon (Linux only) |
| SMS | Log-based (swappable interface) |

---

## Requirements

- PHP 8.2+
- Composer
- MySQL 8+
- Node.js (for Vite, optional)
- Redis (optional — required for Horizon and `onOneServer` scheduling)

---

## Installation

```bash
git clone <repo-url> balsam
cd balsam
composer install
cp .env.example .env
php artisan key:generate
```

Create the MySQL database:

```sql
CREATE DATABASE balsam CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Update `.env` with your database credentials:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=balsam
DB_USERNAME=root
DB_PASSWORD=
```

Run migrations and seeders:

```bash
php artisan migrate --seed
```

Start the development server:

```bash
php artisan serve
```

---

## Environment Configuration

Key `.env` variables:

```dotenv
APP_ENV=local
APP_DEBUG=true

# Database
DB_CONNECTION=mysql
DB_DATABASE=balsam
DB_USERNAME=root
DB_PASSWORD=

# Cache — switch to redis when Redis is available
CACHE_STORE=database
# CACHE_STORE=redis

# Queue — switch to redis when Redis is available
QUEUE_CONNECTION=database
# QUEUE_CONNECTION=redis

# Redis (configure when available)
REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_PASSWORD=null

# Failure simulation for scheduled commands (keep false in production)
SCHEDULE_FAIL_SIMULATION=false
```

---

## Demo Accounts

All demo accounts use the password: **`password`**

| Email | Role | Branch |
|---|---|---|
| admin@balsam.test | admin | — |
| receptionist.dam@balsam.test | receptionist | Damascus |
| receptionist.hal@balsam.test | receptionist | Aleppo |
| doctor.karimi@balsam.test | doctor | Damascus / Aleppo |
| doctor.nassar@balsam.test | doctor | Aleppo |
| doctor.fayyad@balsam.test | doctor | Damascus / Latakia |
| patient.khalid@balsam.test | patient | — |
| patient.nour@balsam.test | patient | — |
| patient.tariq@balsam.test | patient | — |

After running `migrate --seed`, demo tokens are written to `storage/demo-tokens.txt`. Re-running the seeders regenerates them.

---

## API Authentication

All API endpoints require a Sanctum Bearer token.

```
Authorization: Bearer <token>
Accept: application/json
```

Unauthenticated requests return:

```json
{ "message": "Unauthenticated." }
```

---

## API Endpoints

All routes are prefixed with `/api`.

### Current User

| Method | Endpoint | Roles | Description |
|---|---|---|---|
| GET | `/user` | All | Returns authenticated user with branch |

### Doctors

| Method | Endpoint | Roles | Description |
|---|---|---|---|
| GET | `/doctors` | All | List doctors, filter by `branch_id` and `specialty_id` |
| GET | `/doctors/{id}/available-slots` | All | List available slots for a doctor on a date |

#### GET /doctors query parameters

| Parameter | Type | Required | Description |
|---|---|---|---|
| branch_id | integer | No | Filter by branch |
| specialty_id | integer | No | Filter by specialty |
| page | integer | No | Pagination page |

#### GET /doctors/{id}/available-slots query parameters

| Parameter | Type | Required | Description |
|---|---|---|---|
| branch_id | integer | Yes | Branch to check |
| date | string | Yes | Date in `Y-m-d` format, today or future |

**Example response:**

```json
{
  "data": [
    { "start_at": "2026-11-24 09:00:00", "end_at": "2026-11-24 09:20:00", "slot_duration": 20 },
    { "start_at": "2026-11-24 09:20:00", "end_at": "2026-11-24 09:40:00", "slot_duration": 20 }
  ]
}
```

### Appointments

| Method | Endpoint | Roles | Description |
|---|---|---|---|
| GET | `/appointments` | All | List own appointments (role-scoped) |
| POST | `/appointments` | admin, receptionist, patient | Book an appointment |
| GET | `/appointments/{id}` | All (own scope) | View appointment details |
| PATCH | `/appointments/{id}/cancel` | All except doctor | Cancel an appointment |

#### GET /appointments query parameters

| Parameter | Roles | Description |
|---|---|---|
| status | All | Filter by status value |
| date | All | Filter by date `Y-m-d` |
| branch_id | admin only | Filter by branch |
| doctor_id | admin, receptionist | Filter by doctor |

#### POST /appointments request body

```json
{
  "doctor_id": 1,
  "patient_id": 1,
  "branch_id": 1,
  "start_at": "2026-11-24 09:00:00"
}
```

**Success response (201):**

```json
{
  "data": {
    "id": 10,
    "reference": "DAM-2026-000010",
    "status": "booked",
    "start_at": "2026-11-24 09:00:00",
    "end_at": "2026-11-24 09:20:00",
    "doctor": { "id": 1, "name": "Dr. Ahmad Karimi", "specialty": "Cardiology" },
    "patient": { "id": 1, "full_name": "Khalid Mansour", "phone": "+963-912-100001" },
    "branch": { "id": 1, "name": "Damascus", "code": "DAM" },
    "created_at": "2026-11-24 08:30:00"
  }
}
```

**Error response (422):**

```json
{ "message": "هذا الموعد محجوز مسبقاً." }
```

### Medical Records

| Method | Endpoint | Roles | Description |
|---|---|---|---|
| GET | `/appointments/{id}/medical-record` | All (own scope) | View medical record |
| POST | `/appointments/{id}/medical-record` | admin, doctor | Create medical record |
| PATCH | `/appointments/{id}/medical-record` | admin, doctor | Update medical record |

#### POST /appointments/{id}/medical-record request body

```json
{
  "diagnosis": "Upper respiratory infection",
  "notes": "Rest and fluids recommended.",
  "prescription": [
    { "name": "Amoxicillin", "dosage": "500mg", "duration": "7 days" }
  ]
}
```

### Attachments

| Method | Endpoint | Roles | Description |
|---|---|---|---|
| POST | `/medical-records/{id}/attachments` | admin, doctor | Upload a file attachment |
| DELETE | `/medical-records/{id}/attachments/{attachmentId}` | admin, doctor | Delete an attachment |

#### POST /medical-records/{id}/attachments

Multipart form data. Field name: `file`.

Allowed types: `jpg`, `jpeg`, `png`, `pdf`, `doc`, `docx`. Max size: 10 MB.

Files are stored privately at `storage/app/private/medical-records/{id}/`.

---

## Validation and Error Responses

| HTTP Status | Meaning |
|---|---|
| 401 | Unauthenticated — missing or invalid token |
| 403 | Forbidden — authenticated but not authorized |
| 404 | Resource not found |
| 422 | Validation failed or business rule rejected |
| 500 | Server error (message hidden in production) |

**Validation error format (422):**

```json
{
  "message": "Validation failed.",
  "errors": {
    "start_at": ["صيغة وقت الموعد غير صحيحة. استخدم: Y-m-d H:i:s"]
  }
}
```

All validation messages are in Arabic.

---

## Appointment Statuses

| Value | Meaning |
|---|---|
| `booked` | Scheduled, not yet attended |
| `arrived` | Patient has arrived at the clinic |
| `completed` | Appointment finished |
| `cancelled` | Cancelled by patient, receptionist, or admin |
| `no_show` | Patient did not attend |

---

## Database Relationships

- **Branch** has many Users, DoctorSchedules, Appointments, AppointmentSequences
- **User** belongs to Branch, has one Doctor or Patient profile
- **Doctor** belongs to User and Specialty, has many DoctorSchedules and Appointments
- **DoctorSchedule** belongs to Doctor and Branch (a doctor may have schedules at multiple branches and multiple shifts per day)
- **Patient** optionally belongs to User, has many Appointments
- **Appointment** belongs to Doctor, Patient, Branch, and creator User; has one MedicalRecord
- **MedicalRecord** belongs to Appointment; has many Attachments
- **ActivityLog** uses polymorphic `subject` and `causer` relations
- **Attachment** uses polymorphic `attachable` relation (MedicalRecord, Doctor, Branch)
- **AppointmentSequence** tracks per-branch per-year counters for reference generation

### Appointment References

References are generated automatically by `AppointmentObserver::creating()` using a `SELECT ... FOR UPDATE` lock on the `appointment_sequences` table. Format: `{BRANCH_CODE}-{YEAR}-{6-digit number}` (e.g. `DAM-2026-000001`). References are never set manually.

---

## Observers vs Events and Listeners

### Observers — model lifecycle side-effects

| Observer | Trigger | Action |
|---|---|---|
| `AppointmentObserver::creating` | Before insert | Generates appointment reference |
| `AppointmentObserver::created` | After insert | Writes ActivityLog, invalidates availability cache |
| `AppointmentObserver::updated` | After update | Writes ActivityLog for changed fields, invalidates cache if availability fields changed |
| `AppointmentObserver::deleted` | After delete | Writes ActivityLog, invalidates availability cache |
| `MedicalRecordObserver` | created / updated / deleted | Writes ActivityLog for changed fields only |
| `DoctorObserver` | saved / deleted | Increments `doctors:list:version` cache key |
| `DoctorScheduleObserver` | saved / deleted | Increments `doctors:list:version` cache key |

### Business Events and Listeners — domain-level actions after a successful operation

| Event | Dispatched from | Listener | Action |
|---|---|---|---|
| `AppointmentBooked` | `AppointmentBookingService::book()` after transaction commits | `SendBookingConfirmation` | Dispatches `SendBookingConfirmationSms` job to `high` queue |
| `AppointmentCancelled` | `AppointmentBookingService::cancel()` | `SendCancellationConfirmation` | Dispatches `SendCancellationSms` job to `high` queue |
| `AppointmentMarkedAbsent` | `AppointmentBookingService::markNoShow()` | `HandleNoShow` | Checks 30-day no-show count; bans patient for 30 days on third strike |

Events are auto-discovered by Laravel 12 from `app/Listeners/`. Manual `Event::listen()` calls were removed to prevent duplicate registration.

---

## SMS Notifications

SMS sending uses `App\Sms\SmsProviderInterface`. The active implementation is `LogSmsProvider`, which writes to `storage/logs/laravel.log` instead of calling a real SMS gateway.

To switch providers, implement the interface and rebind in `AppServiceProvider::register()`:

```php
$this->app->bind(SmsProviderInterface::class, YourRealProvider::class);
```

Jobs are dispatched to named queues:

| Job | Queue | Trigger |
|---|---|---|
| `SendBookingConfirmationSms` | `high` | Appointment booked |
| `SendCancellationSms` | `high` | Appointment cancelled |
| `SendAppointmentReminderSms` | `notifications` | Daily reminder command |

---

## Artisan Commands

| Command | Description |
|---|---|
| `appointments:send-reminders` | Send SMS reminders for tomorrow's appointments |
| `appointments:mark-no-shows` | Mark past booked appointments as no-show |
| `patients:release-booking-bans` | Clear expired booking bans |
| `reports:monthly-branch` | Generate monthly appointment report per branch |

### Options

**`appointments:send-reminders`**

```bash
php artisan appointments:send-reminders
php artisan appointments:send-reminders --date=2026-11-25
php artisan appointments:send-reminders --date=2026-11-25 --dry-run
```

Skips appointments where `reminder_sent_at` is already set (idempotent).

**`appointments:mark-no-shows`**

```bash
php artisan appointments:mark-no-shows
php artisan appointments:mark-no-shows --date=2026-11-23
php artisan appointments:mark-no-shows --dry-run
```

Calls `AppointmentBookingService::markNoShow()` per record so `AppointmentMarkedAbsent` is dispatched and the ban listener fires.

**`patients:release-booking-bans`**

```bash
php artisan patients:release-booking-bans
php artisan patients:release-booking-bans --dry-run
```

**`reports:monthly-branch`**

```bash
php artisan reports:monthly-branch
php artisan reports:monthly-branch --month=2026-11
php artisan reports:monthly-branch --month=2026-11 --branch=1
php artisan reports:monthly-branch --month=2026-11 --format=csv
```

Reports saved to `storage/app/reports/monthly-branch_{YYYY-MM}.{format}`.

---

## Task Scheduler

Configured in `routes/console.php`. All commands run in `Asia/Damascus` timezone.

| Command | Schedule | Flags |
|---|---|---|
| `appointments:send-reminders` | Daily 08:00 | withoutOverlapping, onOneServer |
| `appointments:mark-no-shows` | Daily 23:59 | withoutOverlapping, onOneServer, evenInMaintenanceMode |
| `patients:release-booking-bans` | Daily 00:10 | withoutOverlapping, onOneServer |
| `reports:monthly-branch` | 1st of month 01:00 | withoutOverlapping, onOneServer |

All append output to `storage/logs/scheduled.log` (daily rotation). Failures are logged to the `scheduled` channel.

### Enabling the scheduler (Linux cron)

```
* * * * * cd /path/to/balsam && php artisan schedule:run >> /dev/null 2>&1
```

### `onOneServer` note

`onOneServer()` requires a shared atomic cache store (Redis or Memcached). With `CACHE_STORE=database`, it has no effect. Switch to `CACHE_STORE=redis` on a Linux server for multi-process protection.

### Failure simulation

Set `SCHEDULE_FAIL_SIMULATION=true` in `.env` to test the failure path for `appointments:send-reminders`. Keep it `false` in all other environments.

---

## Queue Configuration

```bash
# Start queue worker (all queues)
php artisan queue:work --queue=high,default,notifications

# Start queue worker (database driver, local dev)
php artisan queue:listen --tries=3
```

Queue priorities: `high` (booking/cancellation SMS) → `default` → `notifications` (reminders).

`after_commit = true` is set on both the `database` and `redis` queue connections, ensuring jobs dispatched inside transactions only run after the transaction commits.

---

## Horizon (Linux only)

Laravel Horizon requires `ext-pcntl` and `ext-posix`, which are Linux-only extensions. It cannot be installed on Windows/XAMPP.

Configuration is complete in `config/horizon.php` with supervisor queues `high`, `default`, and `notifications`.

To install and start on a Linux server:

```bash
composer require laravel/horizon
php artisan horizon:install
php artisan horizon
```

The dashboard is available at `/horizon`.

---

## Cache Strategy

| Cache key pattern | TTL | Invalidated by |
|---|---|---|
| `available_appointments:{doctor}:{branch}:{date}` | 5 minutes | `AppointmentObserver` on create/update/delete |
| `doctors:list:v{version}:branch=...:specialty=...:page=...` | 30 minutes | `DoctorObserver` / `DoctorScheduleObserver` increment version key |

Cache store is `database` locally. Switch to `CACHE_STORE=redis` for production.

The doctors list uses a version key (`doctors:list:version`) rather than tag-based invalidation, because the database cache driver does not support tags.

---

## Storage and Attachments

Medical record attachments are stored on the `private` disk under `medical-records/{record_id}/`. They are not publicly accessible.

Polymorphic `attachments` table supports Doctor (profile images), Branch (images), and MedicalRecord (medical files).

Allowed upload types: `jpg`, `jpeg`, `png`, `pdf`, `doc`, `docx`. Max: 10 MB.

---

## Authorization Design

### Roles and access

| Role | Appointment access | Medical record access |
|---|---|---|
| admin | All branches | All |
| receptionist | Own branch only | Own branch only |
| doctor | Own appointments | Own appointments |
| patient | Own appointments | Own appointments |

### Why there is no receptionist Global Scope

A database-level global scope would silently restrict appointments in background commands (`appointments:mark-no-shows`), monthly reports, and admin queries — all of which are legitimate and require unrestricted access. Access control is applied instead through:

- `AppointmentPolicy` checked in every controller action
- Scoped `index()` query in `AppointmentController` using local scopes (`forBranch`, `forDoctor`, `forPatient`)
- Local scopes: `forBranch`, `forDoctor`, `forPatient`, `withStatus`, `onDate`, `forSpecialty`

---

## Running Tests

```bash
php artisan test
```

The existing suite contains two placeholder tests (unit + feature). No application-specific test suite has been built in this project phase.

---

## Requirements Checklist

| Requirement | Status |
|---|---|
| Multi-branch clinic data model | ✅ Implemented and verified |
| Migrations, models, relationships | ✅ Implemented and verified |
| Enums (UserRole, AppointmentStatus, WeekDay) | ✅ Implemented and verified |
| Sanctum authentication | ✅ Implemented and verified |
| Role middleware | ✅ Implemented and verified |
| Unified JSON error handling | ✅ Implemented and verified |
| Appointment reference generation (concurrency-safe) | ✅ Implemented and verified |
| AppointmentObserver (logging + cache) | ✅ Implemented and verified |
| MedicalRecordObserver | ✅ Implemented and verified |
| Available slots endpoint with caching | ✅ Implemented and verified |
| Appointment booking with transaction lock | ✅ Implemented and verified |
| Appointment cancellation | ✅ Implemented and verified |
| Appointment list (role-scoped) | ✅ Implemented and verified |
| Medical record endpoints | ✅ Implemented and verified |
| Attachment upload/delete | ✅ Implemented |
| AppointmentPolicy | ✅ Implemented and verified |
| MedicalRecordPolicy | ✅ Implemented and verified |
| FormRequests with Arabic validation messages | ✅ Implemented and verified |
| Local scopes on Appointment | ✅ Implemented and verified |
| Business events (Booked, Cancelled, MarkedAbsent) | ✅ Implemented and verified |
| SMS provider interface + LogSmsProvider | ✅ Implemented and verified |
| Queued SMS jobs (high queue) | ✅ Implemented and verified |
| HandleNoShow listener (booking ban) | ✅ Implemented and verified |
| `appointments:send-reminders` command | ✅ Implemented and verified |
| `appointments:mark-no-shows` command | ✅ Implemented and verified |
| `patients:release-booking-bans` command | ✅ Implemented and verified |
| `reports:monthly-branch` command | ✅ Implemented and verified |
| Task scheduler (4 commands, Damascus timezone) | ✅ Implemented and verified |
| Scheduler failure logging | ✅ Implemented and verified |
| Doctors list endpoint with caching | ✅ Implemented and verified |
| DoctorObserver / DoctorScheduleObserver cache invalidation | ✅ Implemented and verified |
| Composite database indexes | ✅ Implemented (migration ran) |
| Redis configuration (cache + queue) | ✅ Configured — not runtime-verified (Redis not running) |
| Redis queue activation | ⚙️ Configured — requires `QUEUE_CONNECTION=redis` + running Redis server |
| Redis cache activation | ⚙️ Configured — requires `CACHE_STORE=redis` + running Redis server |
| Laravel Horizon | ⚙️ Configured — cannot install on Windows (`ext-pcntl`/`ext-posix` missing) |
| Receptionist Global Scope | ❌ Intentionally not implemented — policy + scoped queries used instead |
| Application test suite | ❌ Only Laravel's placeholder tests exist |
