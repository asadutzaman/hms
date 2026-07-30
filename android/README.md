# Cedarview Care — Android app

Native Android client for the HMS **`/api/v1/mobile`** APIs. Kotlin + Jetpack
Compose (Material 3), Clean Architecture (MVVM), Hilt, Retrofit + kotlinx.serialization,
Coroutines/Flow. Structured to mirror the web `frontend/` (an `api/` layer, feature
modules, and a reusable component kit).

## Requirements

- **Android Studio** (Ladybug / 2024.2+). Open the `android/` folder — Studio downloads
  Gradle 8.9 and generates the wrapper on first sync. (No JDK/Gradle is needed on the
  machine that generated these files; Studio bundles its own.)
- JDK 17 (bundled with Android Studio), Android SDK 35.

## Point the app at your backend

The base URL is a `BuildConfig` field in `app/build.gradle.kts`:

```kotlin
buildConfigField("String", "API_BASE_URL", "\"http://10.0.2.2:8899/api/v1/mobile/\"")
```

- `10.0.2.2` is the **emulator's** alias for the host machine's `localhost`, and `8899`
  is the `php artisan serve --port=8899` port. Start the backend, then run the app on an
  emulator — no other config needed (cleartext to `10.0.2.2` is whitelisted in
  `res/xml/network_security_config.xml`).
- For a **physical device**, change it to your machine's LAN IP or a real HTTPS server
  (the `release` build type already points at an HTTPS placeholder).

## Try it

- **Staff:** `admin@gmail.com` / `123456` → Choose session → Doctor. (A doctor-role login
  works too; mint one per the backend notes.)
- **Patient:** email-OTP. Use a seeded patient email; the code is emailed by the backend.

## Architecture

```
app/src/main/java/com/cedarview/hms/
├─ CedarApp.kt                 @HiltAndroidApp
├─ MainActivity.kt             single-activity host → CedarTheme { CedarNavHost() }
├─ core/
│  ├─ network/                 MobileEnvelope, ApiResult, apiCall(), AuthInterceptor
│  ├─ auth/                    TokenStore (DataStore), SessionManager (+ AuthMode), SessionState
│  ├─ common/                  UiState<T>
│  └─ designsystem/
│     ├─ theme/                Color, Type, Theme (Cedarview tokens, Material 3)
│     └─ component/            CedarComponents.kt  ← the reusable kit
├─ di/                         NetworkModule (Json, OkHttp, Retrofit, APIs)
├─ data/
│  ├─ remote/dto/              @Serializable request/response models
│  ├─ remote/api/              Retrofit interfaces (AuthApi, PatientApi, DoctorApi)
│  └─ repository/              AuthRepository, PatientRepository, DoctorRepository
├─ feature/                    one package per screen area (mirrors frontend/modules)
│  ├─ auth/                    LoginViewModel + LoginScreen (staff + patient tabs)
│  ├─ shell/                   AppSessionViewModel + LauncherScreen (role picker)
│  ├─ patient/home|doctors/    ViewModel + Screen per feature
│  └─ doctor/dashboard/        ViewModel + Screen
└─ navigation/                 Routes + CedarNavHost
```

**Layering:** `feature` (Compose + ViewModel, exposes `StateFlow<UiState<T>>`) → `data.repository`
→ `data.remote.api` (Retrofit) → backend. The `AuthInterceptor` reads the bearer token from
the in-memory `SessionManager` (hydrated once from `TokenStore` on cold start, so a saved
session skips the login screen). Every response is unwrapped from `MobileEnvelope` by
`apiCall()` into an `ApiResult`, so ViewModels only ever see typed data or a message.

## Reusable component kit (`core/designsystem/component/CedarComponents.kt`)

`CedarScaffold`, `PrimaryButton`, `GhostButton`, `CedarTextField`, `CedarCard`,
`SectionHeader`, `InfoRow`, `Avatar`, `StatusPill` (+ `Tone`), `LoadingView`, `ErrorView`,
`EmptyView`. Build screens from these — do not hand-roll one-off styles.

## Adding a feature (the repeatable recipe)

1. **DTOs** in `data/remote/dto/` (`@Serializable`, camelCase — the Json uses a snake_case
   naming strategy).
2. **Endpoints** in a `data/remote/api/XApi.kt` returning `MobileEnvelope<T>`; provide it in
   `di/NetworkModule.kt`.
3. **Repository** in `data/repository/` wrapping calls in `apiCall { }`.
4. **ViewModel** (`@HiltViewModel`) exposing `StateFlow<UiState<T>>`.
5. **Screen** built from the component kit, driven by the `UiState`.
6. **Route** in `navigation/Routes.kt` + a `composable(...)` in `CedarNavHost`.

## Scope of this build

All six role apps are implemented end-to-end against the live `/api/v1/mobile` API at **full
screen parity with the iOS design** (~59 screens), each as a bottom-tab shell built from the
shared component kit. Codes in parentheses map to the design's screen ids.

| App | Screens |
|---|---|
| **Patient** | Home (P1) · Visits · Records — Rx + lab (P5) · Pay (P6) · Profile · Find-a-doctor (P2) → Book (P3) |
| **Doctor** | Today/dashboard (D1) · appointment → **Patient profile (D2)** → **Prescribe (D4)** · **Results inbox (D5)** · SOAP notes (D3) · Code Blue (D6) · Profile |
| **Ward (RMO)** | Round list + inpatient count (WD0/WD1) → Admission detail: vitals (WD9) · drug chart (WD3) · **lab + radiology orders (WD4)** · **fluid balance (WD10)** · daily review (WD2) · **discharge readiness/summary + generate/sign (WD6/WD7)** · **Results inbox (WD5)** |
| **On-call** | Console (DD1) · Jobs (DD2) · Bleeps (DD3) · A-to-E (DD4) · More → Order sets (DD5) · **ED board (DD6)** · **Handover (DD7)** · Profile |
| **Nurse** | Board — ward wall (N1/N10) → Admission detail: **barcode verify (N6)** · **MAR (N2)** · **record vitals (N3)** · **fluid balance (N7)** · **nursing note (N8)** · **discharge checklist (N11)** · **transfer (N12)** · Rapid response (N9) · Tasks (N5) · Handover (N4) · Profile |
| **Admin** | Overview — KPIs + **quality/safety (A4)** + **finance (A5)** (A1) · **Bed occupancy (A2)** · **Live ops (A3)** · Monitors — OPD/IPD/**Emergency** (A8/A9/A10) · More → **Staffing (A6)** · **Reports library (A7)** · Profile |

The launcher is role-gated: Doctor for the Doctor role; Ward/On-call for Doctor **or** Super
Admin; Nurse for the Nurse role **or** Super Admin; Administrator for Super Admin. Navigation
uses a horizontal slide + fade between destinations, and role apps drill down in-shell
(list → detail) with a back arrow.

**One deferred sub-screen:** WD8 *Take-home medicines* (dispense) — its endpoint keys off a
prescription id that isn't exposed in the mobile navigation; the discharge summary section
covers the discharge/meds workflow in the meantime.

> Not compiled here (this repo was authored on a machine without the Android SDK). Open in
> Android Studio and sync to build/run — the backend endpoint every screen calls is verified
> against its controller/resource, but do a Gradle sync and report any unresolved symbols.
> DTOs for the reused endpoints model only the rendered fields and rely on the Json
> `ignoreUnknownKeys`/`coerceInputValues` config, so an unexpected field shows "—" rather than
> crashing.
