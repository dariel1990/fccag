# Music Scheduling Module — Implementation Plan

## Goal
Build a per-team monthly schedule view (Band, Media, Worship Leaders) for the music team. Each view is a month grid: columns = service types (Cottage Service / Prayer Meeting / Prayer & Fasting / Divine Service), rows = weeks. Each cell shows the service date + role/person assignments. Auto-generates all weekday rows for a month and lets users edit assignments via a shadcn dialog.

---

## Phase 0 — Discovery (already complete)

### Allowed conventions confirmed (cite these when in doubt)

**Backend conventions** (mirror Songs/Setlists exactly):
- Controllers: `index/store/show/update/destroy` only — NO `create()` / `edit()` methods. Return `Inertia\Response` from index/show, `RedirectResponse` from store/update/destroy.
  - Reference: `app/Http/Controllers/Music/SongController.php`, `SetlistController.php`
- Form Requests: array-style rules (NOT pipe-string), `authorize()` returns `true`, no `messages()` unless needed.
  - Reference: `app/Http/Requests/Music/StoreSongRequest.php` (lines 25-36)
- Models: `casts()` METHOD (not `$casts` property), return-type-hinted relationships (`: BelongsTo`, `: HasMany`, `: BelongsToMany`).
  - Reference: `app/Models/Song.php`, `app/Models/Setlist.php`, `app/Models/Activity.php`
- Migrations: `foreignId('x')->constrained()->cascadeOnDelete()` (or `nullOnDelete()` when nullable). `nullable()` BEFORE `constrained()`. Explicit indexes on FKs that are filtered.
  - Reference: `database/migrations/2026_04_21_134630_create_songs_table.php`, `2026_04_21_134632_create_setlist_songs_table.php`
- Routes: `Route::resource('x', XController::class)->except(['create', 'edit'])` inside `Route::prefix('music')->name('music.')->group()` with `Route::middleware('permission:X')->group()`.
  - Reference: `routes/web.php:105-120`
- Permissions: extend `App\Enums\Module` enum with the new `Schedules` case (and any sub-modules); the middleware string `permission:schedules` is the enum value.
  - Reference: `app/Enums/Module.php`, `app/Enums/PermissionAction.php`

**Frontend conventions** (match SongFormDialog.vue + Songs/Index.vue exactly):
- Vue 3 `<script setup>` with TypeScript.
- Wayfinder imports: `import { store as storeX, update as updateX } from '@/actions/App/Http/Controllers/Music/XController'`. Call `.url` for the URL string.
  - Reference: `resources/js/components/music/SongFormDialog.vue:5-7,157-160`
- **Forms use `router.post()` / `router.put()` directly** (NOT Inertia `useForm`, NOT `<Form>` component). Manual `ref()` form state, `errors = ref<Record<string, string>>({})` populated from `onError` callback.
  - Reference: `SongFormDialog.vue:130-161`
- Dialog pattern: `<Dialog :open="open" @update:open="handleClose">`. Props `{ open: boolean; model?: Model | null }`. Emits `{ 'update:open': [boolean]; saved: [] }`. Parent toggles `formDialogOpen.value = true` and sets `formModel.value = item | null` before opening.
  - Reference: `SongFormDialog.vue:42-50,169-170`; parent usage in `resources/js/pages/music/songs/Index.vue:59,64,74`
- Validation errors via `<InputError :message="errors.field" />`.
  - Reference: `resources/js/components/InputError.vue`
- Layout: `<AppLayout :breadcrumbs="breadcrumbs">` with `BreadcrumbItem[]` from `@/types/navigation`. `<Head title="..." />` for page title.
- Inertia v2 features available: `Inertia::defer()` server-side + `<WhenVisible>` client-side, `Inertia::merge()`, `Inertia::once()`. Useful if a month view becomes heavy — load schedule data deferred with a skeleton fallback.
- shadcn-vue components present: `Dialog*`, `Input`, `Label`, `Select*`, `Checkbox`, `Textarea`, `Button`, `Badge`, `Table`, `Card`. **Calendar/Popover are NOT installed** — for month picker use HTML5 `<input type="month">` (simplest, matches existing convention of HTML date inputs).

**Test conventions**:
- `tests/Feature/Music/SongControllerTest.php` is the canonical reference.
- Extends `TestCase`, uses `RefreshDatabase` trait.
- Auth setup: `User::factory()->withPermissions([Module::Schedules->value => [PermissionAction::Read->value, PermissionAction::Write->value]])->create()`.
- Inertia assertion: `->assertInertia(fn (AssertableInertia $page) => $page->component('music/schedules/Band')->has('schedules'))`.

### Anti-patterns to avoid
- ❌ Do NOT use `useForm` — codebase doesn't, it would be inconsistent.
- ❌ Do NOT add `create()` or `edit()` controller methods.
- ❌ Do NOT put `nullable()` after `constrained()`.
- ❌ Do NOT use the `$casts` property — use the `casts()` method.
- ❌ Do NOT use pipe-string validation rules — array style only.
- ❌ Do NOT install a new date library — use `<input type="month">` for now.
- ❌ Do NOT auto-generate TypeScript types for models — they're hand-written per page.
- ❌ Do NOT skip the `Module` enum extension — `permission:schedules` middleware will silently 403 without it.

---

## Phase 1 — Backend foundation: enums, migrations, models, factories, seeders

### What to implement

1. **Extend `App\Enums\Module`** with new cases:
   ```php
   case Schedules = 'schedules';
   case MusicMembers = 'music_members';
   case ServiceTypes = 'service_types';
   case ScheduleRoles = 'schedule_roles';
   ```

2. **Migrations** (run via `php artisan make:migration --no-interaction`):
   - `create_service_types_table` — `name string`, `day_of_week tinyInteger nullable` (0=Sun..6=Sat per Carbon convention), `color string nullable` (hex), `sort_order unsignedSmallInteger default 0`, `timestamps`. Unique on `name`.
   - `create_schedule_roles_table` — `team string` (enum-style: `band`/`media`/`worship`), `name string`, `sort_order unsignedSmallInteger default 0`, `timestamps`. Unique on `[team, name]`. Index on `team`.
   - `create_music_members_table` — `name string`, `foreignId('user_id')->nullable()->constrained()->nullOnDelete()`, `instruments string nullable` (comma-list or JSON — match Song.notes style: plain `text` is fine), `is_active boolean default true`, `timestamps`. Index on `is_active`.
   - `create_schedules_table` — `foreignId('service_type_id')->constrained()->cascadeOnDelete()`, `service_date date`, `status string default 'active'` (active/cancelled/none), `notes text nullable`, `foreignId('created_by')->constrained('users')->cascadeOnDelete()`, `timestamps`. Unique on `[service_type_id, service_date]`. Index on `service_date`.
   - `create_schedule_assignments_table` — `foreignId('schedule_id')->constrained()->cascadeOnDelete()`, `foreignId('schedule_role_id')->constrained()->cascadeOnDelete()`, `foreignId('music_member_id')->nullable()->constrained()->nullOnDelete()`, `notes string nullable` (free-text fallback like "Gideon/Emman" or "All available singers"), `timestamps`. Unique on `[schedule_id, schedule_role_id]` (one assignment per role per schedule). Index on `schedule_id`.

3. **Models** (`php artisan make:model --no-interaction`):
   - `ServiceType` — fillable: `name, day_of_week, color, sort_order`. Casts: `day_of_week => integer`, `sort_order => integer`. Relationship: `schedules(): HasMany`.
   - `ScheduleRole` — fillable: `team, name, sort_order`. Casts: `sort_order => integer`. Scope: `scopeForTeam($q, $team)`. Relationship: `assignments(): HasMany`.
   - `MusicMember` — fillable: `name, user_id, instruments, is_active`. Casts: `is_active => boolean`. Relationships: `user(): BelongsTo`, `assignments(): HasMany`.
   - `Schedule` — fillable: `service_type_id, service_date, status, notes, created_by`. Casts: `service_date => date`. Relationships: `serviceType(): BelongsTo`, `assignments(): HasMany`, `creator(): BelongsTo` (User), `assignmentsForTeam(string $team): HasMany` (uses whereHas on schedule_role).
   - `ScheduleAssignment` — fillable: `schedule_id, schedule_role_id, music_member_id, notes`. Relationships: `schedule(): BelongsTo`, `role(): BelongsTo` (ScheduleRole), `member(): BelongsTo` (MusicMember).

4. **Factories** for all five models (basic happy-path factory). Use `fake()->sentence()`, `fake()->randomElement([0,1,2,3,4,5,6])`, etc. Match style of `database/factories/SongFactory.php`.

5. **Seeders** (`database/seeders/`):
   - `ServiceTypeSeeder` — seed 4 rows: Cottage Service (day 4 = Thu), Prayer Meeting (day 5 = Fri), Prayer & Fasting (day null), Divine Service (day 0 = Sun). Use Carbon weekday convention (0=Sun).
   - `ScheduleRoleSeeder` — seed band: Acoustic, Cajon, Keys, E. Guitar, A. Guitar, Bass, Drums (sort_order 1..7); media: Media (1); worship: WL (1), Backups (2).
   - Register in `database/seeders/DatabaseSeeder.php` after existing seeds.

### Documentation references (copy from these)
- Migration FK + nullable order: see `database/migrations/2026_04_21_134632_create_setlist_songs_table.php`
- `casts()` method shape + relationships: `app/Models/Song.php`, `app/Models/Setlist.php`
- Factory shape: `database/factories/SongFactory.php`
- Seeder shape: existing seeders in `database/seeders/`

### Verification checklist
- [ ] `php artisan migrate:fresh --seed` runs cleanly with no errors
- [ ] `php artisan tinker` — `ServiceType::count() === 4`, `ScheduleRole::count() === 10`
- [ ] `Schedule::factory()->create()` succeeds
- [ ] All models grep-pass: `grep -r '\$casts ' app/Models/Schedule*.php app/Models/MusicMember.php app/Models/ServiceType.php app/Models/ScheduleRole.php` returns nothing (use the method form)
- [ ] `vendor/bin/pint --dirty --format agent` clean

### Anti-pattern guards
- Do NOT add `Schedules` to a hand-rolled permissions seed without checking how existing modules are seeded — there may be a permissions table that needs corresponding rows. Read `database/seeders/` end-to-end before adding Module cases.
- Do NOT use `enum()` column for the `team` field — string + check at validation layer is consistent with how `status` is handled in `setlists`.

---

## Phase 2 — Controllers, form requests, routes

### What to implement

1. **Form Requests** (`app/Http/Requests/Music/`):
   - `StoreScheduleRequest`, `UpdateScheduleRequest` — fields: `service_type_id required exists`, `service_date required date`, `status required in:active,cancelled,none`, `notes nullable string`.
   - `GenerateMonthScheduleRequest` — `year required integer min:2020 max:2100`, `month required integer min:1 max:12`. (Used by the "Generate month" action.)
   - `UpdateScheduleAssignmentsRequest` — `assignments required array`, `assignments.*.schedule_role_id required exists`, `assignments.*.music_member_id nullable exists`, `assignments.*.notes nullable string max:255`.
   - `StoreMusicMemberRequest`, `UpdateMusicMemberRequest` — `name required string max:255`, `user_id nullable exists:users,id`, `instruments nullable string`, `is_active boolean`.
   - `StoreServiceTypeRequest`, `UpdateServiceTypeRequest`, `StoreScheduleRoleRequest`, `UpdateScheduleRoleRequest` — match field shapes from migrations.

2. **Controllers** (`app/Http/Controllers/Music/`):
   - **`ScheduleController`** — single controller, team passed as route segment:
     - `index(string $team)` — validates `$team in ['band','media','worship']`. Reads `?year=&month=` from query (default to current). Loads all `Schedule`s for that month with `serviceType` + `assignments.role` + `assignments.member` eager-loaded, scoped to roles where `team === $team`. Loads all `ScheduleRole::forTeam($team)->orderBy('sort_order')` for column rendering. Loads all active `MusicMember`s for the assignment dialog. Returns `Inertia::render("music/schedules/" . ucfirst($team), [...])`.
     - `store(StoreScheduleRequest $request)` — create one schedule.
     - `update(UpdateScheduleRequest $request, Schedule $schedule)` — update meta (status/notes).
     - `destroy(Schedule $schedule)` — delete.
     - `generateMonth(GenerateMonthScheduleRequest $request, string $team)` — for each ServiceType with non-null `day_of_week`, iterate that month's matching weekdays and `firstOrCreate` a Schedule. Returns redirect back with flash. Team param is used only for redirect destination (the schedules themselves are team-agnostic).
     - `updateAssignments(UpdateScheduleAssignmentsRequest $request, Schedule $schedule)` — upsert assignments via `updateOrCreate` keyed on `[schedule_id, schedule_role_id]`. Delete rows whose role isn't in the payload but only for the team being edited (so editing band assignments doesn't wipe media). Use a DB transaction.
   - **`MusicMemberController`** — standard resource (index/store/update/destroy).
   - **`ServiceTypeController`** — standard resource.
   - **`ScheduleRoleController`** — standard resource.

3. **Routes** (`routes/web.php`, append inside the `Route::prefix('music')` group):
   ```php
   Route::middleware('permission:schedules')->group(function (): void {
       Route::get('schedules/{team}', [ScheduleController::class, 'index'])
           ->whereIn('team', ['band', 'media', 'worship'])
           ->name('schedules.index');
       Route::post('schedules', [ScheduleController::class, 'store'])->name('schedules.store');
       Route::patch('schedules/{schedule}', [ScheduleController::class, 'update'])->name('schedules.update');
       Route::delete('schedules/{schedule}', [ScheduleController::class, 'destroy'])->name('schedules.destroy');
       Route::post('schedules/{team}/generate', [ScheduleController::class, 'generateMonth'])
           ->whereIn('team', ['band', 'media', 'worship'])
           ->name('schedules.generate');
       Route::patch('schedules/{schedule}/assignments', [ScheduleController::class, 'updateAssignments'])
           ->name('schedules.assignments.update');
   });

   Route::middleware('permission:music_members')->group(function (): void {
       Route::resource('music-members', MusicMemberController::class)->except(['create', 'edit']);
   });
   Route::middleware('permission:service_types')->group(function (): void {
       Route::resource('service-types', ServiceTypeController::class)->except(['create', 'edit']);
   });
   Route::middleware('permission:schedule_roles')->group(function (): void {
       Route::resource('schedule-roles', ScheduleRoleController::class)->except(['create', 'edit']);
   });
   ```

4. **Build Wayfinder actions**: run `npm run build` (or rely on `composer run dev`) to regenerate `resources/js/actions/App/Http/Controllers/Music/ScheduleController.ts` and friends.

### Documentation references (copy from these)
- Controller index pattern + filtering: `app/Http/Controllers/Music/SongController.php:18-25`
- Resource route registration with permission middleware: `routes/web.php:105-120`
- Form request shape: `app/Http/Requests/Music/StoreSongRequest.php`
- Inertia render with props: `app/Http/Controllers/Music/SetlistController.php` (its `show()` method)

### Verification checklist
- [ ] `php artisan route:list | grep music/schedules` shows 6 routes for schedules + 3 resource routes for each admin model
- [ ] `vendor/bin/pint --dirty --format agent` clean
- [ ] `npm run build` succeeds and writes `resources/js/actions/App/Http/Controllers/Music/ScheduleController.ts` (grep for `'updateAssignments'` in the generated file)

### Anti-pattern guards
- Do NOT call `validated()` then `Schedule::create($request->all())` — always pass `$request->validated()`.
- Do NOT eager-load all assignments unfiltered when team is selected — use `whereHas('role', fn ($q) => $q->where('team', $team))` to scope.
- Do NOT delete-then-recreate assignments inside `updateAssignments` — use `updateOrCreate` to preserve IDs and avoid race-y FK violations.

---

## Phase 3 — Frontend: per-team month grid + assignment dialog

### What to implement

1. **Page components** (mirror the spreadsheet layout in the user's screenshots):
   - `resources/js/pages/music/schedules/Band.vue`
   - `resources/js/pages/music/schedules/Media.vue`
   - `resources/js/pages/music/schedules/Worship.vue`

   Each page is thin — it just sets the team constant, breadcrumbs, page title, and renders a shared `<MonthScheduleGrid>` component. Example shape for `Band.vue`:
   ```vue
   <script setup lang="ts">
   import AppLayout from '@/layouts/AppLayout.vue';
   import MonthScheduleGrid from '@/components/music/MonthScheduleGrid.vue';
   import { Head } from '@inertiajs/vue3';
   import type { BreadcrumbItem } from '@/types/navigation';

   const props = defineProps<{
       year: number;
       month: number;
       schedules: ScheduleRow[];
       serviceTypes: ServiceType[];
       roles: ScheduleRole[];
       members: MusicMember[];
   }>();

   const breadcrumbs: BreadcrumbItem[] = [
       { title: 'Music', href: '#' },
       { title: 'Schedules — Band', href: '' },
   ];
   </script>

   <template>
       <Head title="Band Schedule" />
       <AppLayout :breadcrumbs="breadcrumbs">
           <MonthScheduleGrid team="band" v-bind="props" />
       </AppLayout>
   </template>
   ```

2. **Shared grid component** `resources/js/components/music/MonthScheduleGrid.vue`:
   - Header row: month picker (`<input type="month" v-model="monthValue" @change="navigate">`) + "Generate month" button (calls `router.post(generate({ team }).url, { year, month })`).
   - Grid: weeks as rows, one column per service type. Each cell shows the service date as a colored chip (matching the orange in the screenshots) + a stacked list of `role / member-name-or-notes`. Empty slots show a dashed placeholder.
   - Click a cell → opens `<ScheduleAssignmentDialog>`. New cell (no schedule yet for that date) → opens dialog in "create" mode that first creates the Schedule then lets you assign.
   - Compute weeks via a small helper that walks the month and groups dates by ISO week.
   - Match styling to user's screenshot: orange date pill (`bg-orange-200`), label-value rows, alternating row backgrounds. Use the team color on the page header.

3. **Assignment dialog** `resources/js/components/music/ScheduleAssignmentDialog.vue`:
   - Props: `open: boolean`, `schedule: Schedule | null`, `team: 'band' | 'media' | 'worship'`, `roles: ScheduleRole[]`, `members: MusicMember[]`.
   - Emits: `'update:open': [boolean]`, `'saved': []`.
   - Body: status select (active/cancelled/none), notes textarea, then for each role in `roles` a row with: role label, member combobox/select (populated from `members`, plus "— None —" option), free-text notes input (for "Gideon/Emman" / "All available singers" cases).
   - Submit: `router.patch(updateAssignments(schedule.id).url, { assignments: [...] }, { preserveScroll: true, onSuccess, onError })`.
   - Style: copy `SongFormDialog.vue` end-to-end — same imports, same `errors` ref, same `processing` ref, same submit/close flow.

4. **Wayfinder imports** in pages/components:
   ```ts
   import { index as schedulesIndex, generateMonth, updateAssignments, store as storeSchedule, destroy as destroySchedule } from '@/actions/App/Http/Controllers/Music/ScheduleController';
   ```

5. **Types** (define inline per page like songs/Index.vue does — no central types file):
   ```ts
   type ServiceType = { id: number; name: string; day_of_week: number | null; color: string | null };
   type ScheduleRole = { id: number; team: string; name: string; sort_order: number };
   type MusicMember = { id: number; name: string; user_id: number | null };
   type Assignment = { id: number; schedule_role_id: number; music_member_id: number | null; notes: string | null };
   type ScheduleRow = { id: number; service_type_id: number; service_date: string; status: string; notes: string | null; assignments: Assignment[] };
   ```

### Documentation references (copy from these)
- Dialog pattern (open/emit): `resources/js/components/music/SongFormDialog.vue:42-50,169-170`
- Form submit with `router.post` + errors: `SongFormDialog.vue:130-161`
- Page scaffold (AppLayout + Head + breadcrumbs): `resources/js/pages/music/songs/Index.vue:1-50`
- InputError usage: `SongFormDialog.vue:246`
- Wayfinder import: `SongFormDialog.vue:5-7`

### Verification checklist
- [ ] `npm run build` clean, no TS errors
- [ ] Visit `/music/schedules/band` — grid renders, current month, "Generate month" creates schedules for all Thu/Fri/Sun in the month
- [ ] Click a cell — dialog opens populated with current assignments
- [ ] Save assignments — dialog closes, grid reflects update without full page reload (preserveScroll)
- [ ] Visit `/music/schedules/media` — only Media role column shown in dialog; same Sunday schedule shown but only media assignments visible
- [ ] Visit `/music/schedules/worship` — only WL/Backups roles shown
- [ ] Mobile viewport (≤640px): grid scrolls horizontally without breaking layout

### Anti-pattern guards
- Do NOT use `useForm` — use `router.post()` + manual ref state.
- Do NOT install `date-fns` or `dayjs` for the month picker — `<input type="month">` and a small inline weekday helper is enough.
- Do NOT auto-update the grid by reloading the whole page; use `router.reload({ only: ['schedules'] })` after save.
- Do NOT scope styles with `<style scoped>` if the existing components use Tailwind utility classes — match the prevailing pattern.

---

## Phase 4 — Admin CRUD pages for music_members, service_types, schedule_roles

### What to implement

For each of three resources, mirror the `Songs/Index.vue` + `SongFormDialog.vue` pattern:
- `resources/js/pages/music/music-members/Index.vue` + `resources/js/components/music/MusicMemberFormDialog.vue`
- `resources/js/pages/music/service-types/Index.vue` + `resources/js/components/music/ServiceTypeFormDialog.vue`
- `resources/js/pages/music/schedule-roles/Index.vue` + `resources/js/components/music/ScheduleRoleFormDialog.vue`

Each Index.vue:
- Table of records (mobile: card list, desktop: table — match `songs/Index.vue`).
- Add button → opens dialog in create mode.
- Each row has Edit + Delete actions.
- Pass `members` prop with index payload from controller.

Music Members dialog also needs a **user select** (search for unlinked users, allow null) so members can optionally be linked to a User account.

Service Types dialog: name, day_of_week select (Sunday..Saturday with null option for "any day"), color picker (`<input type="color">`), sort_order number.

Schedule Roles dialog: team select (band/media/worship), name, sort_order.

### Documentation references
- Page + dialog: `resources/js/pages/music/songs/Index.vue` + `resources/js/components/music/SongFormDialog.vue`
- User listing for member-user link: check existing user search/list patterns — likely `app/Http/Controllers/Settings/` has a user index that can be referenced.

### Verification checklist
- [ ] `/music/music-members` — can create, edit, delete members
- [ ] `/music/service-types` — can create new service types and they appear as columns in the grid
- [ ] `/music/schedule-roles` — can add a new band role (e.g., "Percussion") and it appears in the band assignment dialog
- [ ] Linking a `MusicMember` to a `User` works; unlinked members still function

### Anti-pattern guards
- Do NOT hardcode the team/day_of_week dropdown options in the Vue file twice — extract a shared `const TEAMS = ['band', 'media', 'worship'] as const`.
- Do NOT allow deleting a `ScheduleRole` that has assignments without confirming — show a warning if `assignments_count > 0`.

---

## Phase 5 — Sidebar nav, tests, and final verification

### What to implement

1. **Sidebar nav** (`resources/js/components/AppSidebar.vue` around lines 178-193):
   Append to the Music group's `items`:
   ```ts
   { title: 'Schedules — Band', href: schedulesIndex({ team: 'band' }).url, icon: CalendarDays, permission: 'schedules' },
   { title: 'Schedules — Media', href: schedulesIndex({ team: 'media' }).url, icon: CalendarDays, permission: 'schedules' },
   { title: 'Schedules — Worship', href: schedulesIndex({ team: 'worship' }).url, icon: CalendarDays, permission: 'schedules' },
   ```
   And admin entries (probably under a "Settings" or "Admin" group depending on existing pattern):
   ```ts
   { title: 'Music Members', href: musicMembersIndex().url, icon: Users, permission: 'music_members' },
   { title: 'Service Types', href: serviceTypesIndex().url, icon: Calendar, permission: 'service_types' },
   { title: 'Schedule Roles', href: scheduleRolesIndex().url, icon: ListChecks, permission: 'schedule_roles' },
   ```
   Read `AppSidebar.vue` first to confirm the icon import style and nav-group structure.

2. **Feature tests** (`php artisan make:test --phpunit Music/ScheduleControllerTest`):
   - `test_index_renders_per_team_grid` — for each of band/media/worship: GET returns Inertia component `music/schedules/Band|Media|Worship`, has `schedules`, `roles`, `members` props; roles are scoped to the requested team.
   - `test_generate_month_creates_schedules_for_each_matching_weekday` — call generate for May 2026, assert 5 Sundays + 4 Thursdays + 5 Fridays = expected count of Schedule rows.
   - `test_generate_month_is_idempotent` — calling twice creates no duplicates (firstOrCreate).
   - `test_update_assignments_upserts_only_team_scoped_roles` — band assignments don't wipe media assignments on the same Sunday schedule.
   - `test_update_assignments_validates_role_team` — passing a media role id while editing band should 422 (or silently skip, depending on chosen behavior — pick one and assert).
   - `test_index_requires_schedules_permission` — user without permission gets 403.
   - One smoke test per admin resource (`MusicMemberControllerTest`, `ServiceTypeControllerTest`, `ScheduleRoleControllerTest`): index/store/update/destroy happy path.

3. **Final formatting + lint**:
   - `vendor/bin/pint --dirty --format agent`
   - `npm run lint` (if defined in package.json; otherwise skip)
   - `npm run build`

4. **Manual smoke** in Herd (`https://lcas.test/music/schedules/band`):
   - Generate May 2026 → see 14 cells populated as empty schedules
   - Open a Sunday cell → assign Algene to Keys, Dariel to Bass, Blake to Drums — save
   - Switch to `/music/schedules/media` → same Sunday shows empty media row (no leakage from band)
   - Open the same Sunday from media view → assign Reaiah to Media — save
   - Reopen from band view → media assignment is preserved on the model but not shown (or is shown read-only — pick one in design)

### Verification checklist
- [ ] `php artisan test --compact --filter=Schedule` all green
- [ ] `php artisan test --compact --filter=MusicMember` all green
- [ ] `php artisan test --compact tests/Feature/Music/` full suite green
- [ ] `vendor/bin/pint --dirty --format agent` clean
- [ ] Sidebar entries visible to a user with `schedules` permission
- [ ] Manual smoke passes

### Anti-pattern guards
- Do NOT add tests that don't assert anything (`->assertOk()` alone is weak — assert prop shape).
- Do NOT skip the permission test — it's the only check that the `Module::Schedules` enum was actually wired.
- Do NOT commit if `pint` or tests fail — fix root cause, don't `--no-verify`.

---

## Phase boundaries (each runnable in a fresh chat context)

| Phase | Scope | Approx LOC |
|-------|-------|------------|
| 1 | enums, migrations, models, factories, seeders | ~600 |
| 2 | controllers, form requests, routes, Wayfinder regen | ~500 |
| 3 | per-team grid pages + assignment dialog | ~700 |
| 4 | three admin CRUD pages + dialogs | ~900 |
| 5 | sidebar, tests, final pass | ~400 |

Each phase ends with its verification checklist passing before the next begins. Run `/claude-mem:do` with this plan to execute.
