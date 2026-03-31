# System dokumentacji medycznej i e-recept

Monorepo łączące backend API (Symfony + API Platform) z panelem klienckim (Vue 3). Projekt wspiera scenariusz kliniczny: lekarze mają dostęp do przypisanych pacjentów, pacjenci widzą wyłącznie własne dane i recepty.

## Funkcjonalności (stan obecny)

- **API REST** z API Platform 3.x: profile pacjentów, recepty, historia medyczna, endpoint **`GET /api/me`** (profil zalogowanego użytkownika i skrócone listy bez narzucania ID w URL po stronie UI).
- **Uwierzytelnianie JWT** (Lexik, algorytm **RS256**, klucze w `backend/config/jwt/`).
- **Relacja kliniczna**: wielu lekarzy ↔ wielu pacjentów (`ManyToMany` przez tabelę `patient_profile_doctor`).
- **Frontend**: logowanie, Pinia (stan + token), Vuetify 3, proxy Vite do API w trybie deweloperskim.

## Struktura katalogów

```
SymfonyMedicalDocument/
├── backend/          # Symfony 7 — API, bezpieczeństwo, Doctrine, migracje
│   ├── config/       # Konfiguracja (security, API Platform, JWT, Doctrine, …)
│   ├── migrations/   # Migracje bazy PostgreSQL
│   ├── public/       # Punkt wejścia HTTP
│   └── src/          # Encje, DTO, voterzy, providery, fixtures
└── frontend/         # Vue 3 + Vite + Vuetify 3 + Pinia + Axios
    └── src/
        ├── api/      # Klient HTTP (Axios)
        ├── stores/   # Pinia (np. auth)
        ├── views/    # Widoki (logowanie, dashboard)
        └── router/   # Vue Router
```

## Stack technologiczny

| Warstwa | Technologie |
|--------|-------------|
| Backend | PHP 8.2+, **Symfony 7.4**, **API Platform 3.x** (rdzeń 3.4.x), Doctrine ORM 3, PostgreSQL |
| Bezpieczeństwo | Symfony Security, **LexikJWTAuthenticationBundle**, voterzy (np. recepta / historia), filtrowanie kolekcji (lekarz/pacjent) |
| Frontend | **Vue 3** (Composition API), **Vite**, **Vuetify 3**, **Pinia**, **Axios**, **Vue Router** |
| Narzędzia | Composer, npm, migracje Doctrine, fixtures (środowisko deweloperskie) |

## Wymagania wstępne

- **PHP** 8.2+ z rozszerzeniami wymaganymi przez Symfony/Doctrine (m.in. `ctype`, `iconv`, `pdo_pgsql`).
- **Composer** 2.x.
- **PostgreSQL** (np. 16) — dostępna baza i użytkownik zgodny z `DATABASE_URL`.
- **Node.js** 20+ (lub 18 LTS) oraz **npm** — dla `frontend/`.
- Do generowania par kluczy JWT (opcjonalnie na nowym komputerze): **OpenSSL** (np. z Git for Windows: `C:\Program Files\Git\usr\bin\openssl.exe`) lub polecenie `php bin/console lexik:jwt:generate-keypair` w środowisku, w którym OpenSSL działa poprawnie.

> Na Windowsie Composer może zgłaszać konflikt z **`ext-redis`**; wtedy można tymczasowo użyć:  
> `composer install --ignore-platform-req=ext-redis`

## Konfiguracja i uruchomienie — backend

1. Wejdź do katalogu API:

   ```bash
   cd backend
   ```

2. Zainstaluj zależności:

   ```bash
   composer install --ignore-platform-req=ext-redis
   ```

3. Skopiuj i uzupełnij zmienne środowiskowe (np. `.env.local`):

   - **`DATABASE_URL`** — połączenie z PostgreSQL.
   - **`JWT_SECRET_KEY`** / **`JWT_PUBLIC_KEY`** — ścieżki do `config/jwt/private.pem` i `config/jwt/public.pem` (domyślnie ustawione w `.env`).
   - **`JWT_PASSPHRASE`** — puste, jeśli klucz prywatny jest bez hasła (jak w wygenerowanym zestawie deweloperskim).

   Plik **`private.pem` nie powinien trafiać do repozytorium** (jest w `.gitignore` w `config/jwt/`). Na nowej maszynie wygeneruj parę kluczy ponownie lub skopiuj ją bezpiecznym kanałem.

4. Utwórz schemat bazy i (opcjonalnie) załaduj dane testowe:

   ```bash
   php bin/console doctrine:migrations:migrate --no-interaction
   php bin/console doctrine:fixtures:load --no-interaction
   ```

5. Uruchom serwer HTTP (przykład — wbudowany router PHP na porcie 8000):

   ```bash
   php -S 127.0.0.1:8000 -t public
   ```

   Możesz też użyć **Symfony CLI** (`symfony server:start`), jeśli jest zainstalowane.

**Przydatne endpointy:**

- `POST /api/auth` — logowanie JSON: `{"email":"...","password":"..."}` → w odpowiedzi m.in. pole **`token`** (JWT).
- `GET /api/me` — nagłówek `Authorization: Bearer <token>`; zwraca dane konta, profil pacjenta i/lub listy (recepty / pacjenci w zależności od roli).
- Pozostałe zasoby API Platform pod prefiksem `/api/` (np. recepty, profile pacjentów) — szczegóły w dokumentacji interaktywnej API Platform (np. `/api` w formacie HTML/OpenAPI, zależnie od konfiguracji).

**Konta z fixtures** (hasło dla wszystkich: `password`):

- `doctor@example.com`, `doctor2@example.com` — role lekarzy  
- `patient1@example.com`, `patient2@example.com` — role pacjentów  

## Konfiguracja i uruchomienie — frontend

1. Wejdź do katalogu aplikacji Vue:

   ```bash
   cd frontend
   ```

2. Zainstaluj zależności:

   ```bash
   npm install
   ```

3. Tryb deweloperski — **Vite przekierowuje żądania `/api` na backend** (`127.0.0.1:8000` w `vite.config.js`). Uruchom backend na tym hoście i porcie (lub zmień `proxy` w konfiguracji Vite).

   ```bash
   npm run dev
   ```

   Otwórz w przeglądarce adres podany w terminalu (zwykle `http://localhost:5173`).

4. **Build produkcyjny:**

   ```bash
   npm run build
   ```

   Dla wdrożenia bez proxy ustaw w pliku środowiskowym **`VITE_API_BASE_URL`** na pełny URL API (patrz `frontend/.env.example`).

## CORS

W backendzie bundle **NelmioCorsBundle** pozwala na żądania z `localhost` / `127.0.0.1` z portem. Przy pracy wyłącznie przez proxy Vite CORS dla `/api` nie jest wymagany.

## Zgodność z RODO / dane osobowe

Fixtures i opisy w kodzie mogą zawierać **dane fikcyjne** wyłącznie do testów. W produkcji stosuj polityki retencji, logowanie dostępu, umowy powierzenia i minimalizację danych zgodnie z prawem.

## Konwencja komunikatów Git

Commity w języku polskim, **w formie bezosobowej i w czasie przeszłym** (opis tego, co zostało zrobione), np.:

| Unikaj (tryb rozkazujący / osobowy) | Używaj |
|-------------------------------------|--------|
| Dodaj walidację | **Dodano** walidację |
| Zmień konfigurację | **Zmieniono** konfigurację |
| Usuń przestarzały kod | **Usunięto** przestarzały kod |

W repozytorium ustawiono szablon commita (plik `.gitmessage`). Aby Git podpowiadał go przy `git commit` (bez argumentu `-m`), w katalogu projektu wykonaj:

```bash
git config commit.template .gitmessage
```

---

*Dokument opisuje stan repozytorium w momencie utworzenia pliku; szczegóły implementacji mogą ewoluować w kolejnych commitach.*
