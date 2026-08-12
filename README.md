# Dokumentasi Proyek — Laravel Blog + Admin Panel

Proyek hasil mengikuti kursus **"Laravel 12 For Beginners: Your First Project"** dari [Laravel Daily](https://laraveldaily.com/course/laravel-from-scratch) (15 lesson), dikerjakan mulai dari nol (install) sampai selesai (validation).

Berbeda dengan kursus yang membuat dua project terpisah (blog + project Breeze), sesuai arahan guru proyek ini **digabung dalam satu project**: halaman blog publik dan panel admin (auth + CRUD) berada di project yang sama.

---

## 1. Teknologi / Lingkungan

Bukti versi perangkat yang digunakan:

```text
> php -v
PHP 8.4.0 (cli) (built: Nov 21 2024 08:18:43) (NTS Visual C++ 2019 x64)
    with Zend OPcache v8.4.0

> composer --version
Composer version 2.9.3 2025-12-30 13:40:17
PHP version 8.4.0

> node -v
v24.12.0

> npm -v
11.6.2

> php artisan --version
Laravel Framework 12.65.0
```

Dependency utama (dari `composer.json`):

| Package | Keterangan |
|---|---|
| `laravel/framework ^12.0` | Framework Laravel 12 |
| `laravel/breeze ^2.4` (dev) | Starter kit autentikasi (Blade + Alpine) |
| `barryvdh/laravel-debugbar ^4.4` (dev) | Debugbar untuk memeriksa query (Step 13) |

Database yang dipakai: **SQLite** (`database/database.sqlite`). Frontend: **Tailwind CSS v4** (via Vite) untuk halaman admin, dan CDN Tailwind untuk halaman blog.

---

## 2. Fitur Aplikasi

### Blog (Publik, tanpa login)
- Halaman utama: daftar **Latest Posts** + sidebar **Categories** (filter `/?category_id={id}`)
- Halaman `about`, `contact`, `article`, dan halaman post per ID (`/posts/{post}`)
- Data dari database via **Eloquent**

### Autentikasi & Admin (Login dulu)
- Register / Login / Logout / Forgot password / Profile (dari **Laravel Breeze**)
- Dashboard
- **CRUD Categories** (khusus admin)
- **CRUD Posts** (khusus admin) + relasi kategori + eager loading
- **Form validation** pada form create post

---

## 3. Cara Menjalankan

```bash
# 1. Install dependency PHP
composer install

# 2. Buat file environment
copy .env.example .env        # Windows
cp .env.example .env          # Linux/Mac
php artisan key:generate

# 3. Siapkan database SQLite
php artisan migrate

# 4. Install & build aset frontend
npm install
npm run build                 # atau npm run dev (buka terminal lain)

# 5. Jalankan server
php artisan serve
# Buka http://127.0.0.1:8000
```

### Akun & Data contoh

Akun admin (sudah dibuat saat praktik):

| Email | Password | is_admin |
|---|---|---|
| `admin@example.com` | `password` | `true` |

Buat user lain lewat form `/register`. Untuk menjadikan user sebagai admin:

```bash
php artisan tinker
# lalu ketik:
# \App\Models\User::where('email','user@example.com')->update(['is_admin'=>true]);
```

Data kategori & post contoh (Category 1–4 dan Post 1–4) dibuat langsung lewat database saat praktik. Karena `database/database.sqlite` masuk `.gitignore`, data tersebut tidak ikut di-push. Cara menanam ulang (contoh via tinker):

```php
\App\Models\Category::create(['name' => 'Category 1']);
// ... dst, lalu:
\App\Models\Post::create(['title' => 'Post 1', 'text' => 'Something something very long text. Something something very long text. Something something very long text. Something something very long text.', 'category_id' => 1]);
// ... dst
```

> Setelah `migrate`, hapus baris `-- create table` di bawah ini hanya diingat: gunakan perintah di atas. Data contoh bersifat opsional.

---

## 4. Dokumentasi Step 1–15

> Semua command di bawah adalah yang benar-benar dijalankan, dan hasilnya diverifikasi langsung lewat browser / `Invoke-WebRequest` (HTTP status code). Bukti screenshot ada di folder [`docs/bukti/`](docs/bukti/).

### Step 1 — Required Tools and Laravel Installation

**Tujuan:** Menyiapkan tools (PHP, Composer, Node) dan membuat project Laravel baru.

**Perintah:**
```bash
laravel new project
# pilih None starter kit (default Laravel 12)
```

**Hasil:** Folder project berisi struktur Laravel 12 (app, bootstrap, config, database, public, resources, routes, storage, tests, vendor).

---

### Step 2 — Routing and Creating New Page

**Tujuan:** Belajar menulis route dan membuat halaman sederhana.

**Perintah / kode:**
```php
// routes/web.php
Route::view('/', 'home');
```

**File:** `routes/web.php`, `resources/views/home.blade.php`

**Hasil:** `/` menampilkan halaman `home`. Status: `GET / → 200`.

---

### Step 3 — Tailwind in Laravel: New Homepage Design

**Tujuan:** Mendesain homepage dengan Tailwind CSS.

**Perintah:**
```bash
npm install
npm run build
```

**Kode:** `home.blade.php` memuat Tailwind via CDN lalu diganti dengan `@vite([...])` (CSS di-compile).

**Hasil:** Homepage tampil dengan styling Tailwind. Status: `GET / → 200`.

---

### Step 4 — Navigation and Reusable Main Layout

**Tujuan:** Membuat layout utama (`@yield`) agar header/footer tidak diulang di tiap halaman.

**Perintah / kode:**
```php
// routes/web.php
Route::view('/', 'home')->name('home');
Route::view('contact', 'contact')->name('contact');
Route::view('about', 'about')->name('about');
```

**File:** `resources/views/layouts/app.blade.php`, `home.blade.php`, `about.blade.php`, `contact.blade.php`

**Hasil:** Navigasi antar halaman via route name. Status: `/about → 200`, `/contact → 200`.

---

### Step 5 — New Design Layout for Blog Project

**Tujuan:** Layout blog dengan sidebar, memakai `asset()` untuk gambar.

**Perintah / kode:**
```php
// routes/web.php
Route::view('article', 'article')->name('article');
```

**File:** `resources/views/layouts/blog.blade.php`, `home.blade.php`, `article.blade.php`, `about.blade.php`, `contact.blade.php`; gambar di `public/images/placeholder-150x150.png` dan `placeholder-800x400.png`.

**Hasil:** Desain blog (My Blog + menu About Us/Contact) tampil. Status: `/article → 200`.

---

### Step 6 — Database Structure and Migrations

**Tujuan:** Membuat tabel `categories` dan `posts` via migration.

**Perintah:**
```bash
php artisan make:migration create_categories_table
php artisan make:migration create_posts_table
php artisan migrate
```

**File:** `database/migrations/2026_08_11_065107_create_categories_table.php`, `2026_08_11_065825_create_posts_table.php`

**Hasil:**
```text
INFO  Running migrations.
  create_categories_table ................. DONE
  create_posts_table ...................... DONE
```

---

### Step 7 — MVC, DB Queries, and Eloquent Models

**Tujuan:** Membuat Model + Controller, mengambil data dari DB dengan Eloquent.

**Perintah:**
```bash
php artisan make:model Category
php artisan make:model Post
php artisan make:controller HomeController
```

**Kode inti:**
```php
// app/Http/Controllers/HomeController.php
public function index()
{
    $categories = Category::all();
    $posts = Post::when(request('category_id'), function ($query) {
        return $query->where('category_id', request('category_id'));
    })->latest()->get();

    return view('home', compact('categories', 'posts'));
}
```

**File:** `app/Models/Category.php`, `app/Models/Post.php`, `app/Http/Controllers/HomeController.php`, `routes/web.php`, `resources/views/home.blade.php`

**Hasil:** `GET / → 200` menampilkan posts & categories dari database.

---

### Step 8 — Eloquent Relations and GET Parameters

**Tujuan:** Relasi `belongsTo` (Post → Category) dan filtering lewat GET parameter.

**Kode:**
```php
// app/Models/Post.php
public function category()
{
    return $this->belongsTo(Category::class);
}
```

**Hasil:** Filter `GET /?category_id=2` hanya menampilkan post kategori 2. Terverifikasi: halaman berisi `Post 2`, tidak berisi `Post 1`.

---

### Step 9 — Route Model Binding with Parameters

**Tujuan:** Halaman detail post per ID dengan route model binding.

**Perintah / kode:**
```bash
php artisan make:controller PostController
```
```php
// routes/web.php
Route::get('posts/{post}', [PostController::class, 'show'])
    ->whereNumber('post')
    ->name('post.show');
```
```php
// app/Http/Controllers/PostController.php
public function show(Post $post)
{
    return view('post', compact('post'));
}
```

**Hasil:** `GET /posts/1 → 200`, `/posts/999 → 404`.

> Catatan gabungan project: `whereNumber('post')` ditambahkan agar `/posts/create` (halaman admin step 13) tidak tertangkap route blog `posts/{post}`.

---

### Step 10 — Starter Kits and Using Laravel Breeze

**Tujuan:** Memasang autentikasi (login/register/dashboard) dengan Laravel Breeze.

**Perintah:**
```bash
composer require laravel/breeze --dev
php artisan breeze:install blade --no-interaction
php artisan migrate
npm install && npm run build
```

**File baru:** `routes/auth.php`, `app/Http/Controllers/Auth/*`, `app/Http/Controllers/ProfileController.php`, `resources/views/auth/*`, `resources/views/dashboard.blade.php`, `resources/views/profile/*`, `resources/views/layouts/app.blade.php`, `resources/views/layouts/navigation.blade.php`, `resources/views/components/*`.

**Hasil:** `/login → 200`, `/register → 200`, `/dashboard → redirect ke /login` (harus login dulu).

---

### Step 11 — Categories CRUD: Index, Create, Update, Delete

**Tujuan:** CRUD kategori di panel admin.

**Perintah:**
```bash
php artisan make:controller CategoryController --resource --model=Category
```

**File:** `app/Http/Controllers/CategoryController.php`, `resources/views/categories/{index,create,edit}.blade.php`, link "Categories" di `layouts/navigation.blade.php`.

**Kode inti:**
```php
public function store(Request $request)
{
    Category::create(['name' => $request->input('name')]);
    return redirect()->route('categories.index');
}
```

**Hasil (login sebagai admin):** `/categories → 200` (tabel kategori), create → 302 redirect ke index, edit & delete jalan. Tanpa login → redirect ke `/login`.

---

### Step 12 — Admin User, Route Groups, Middleware

**Tujuan:** Kolom `is_admin`, custom middleware, dan melindungi route kategori hanya untuk admin.

**Perintah:**
```bash
php artisan make:migration add_is_admin_to_users_table
php artisan make:middleware IsAdminMiddleware
php artisan migrate
```

**Kode:**
```php
// app/Http/Middleware/IsAdminMiddleware.php
public function handle(Request $request, Closure $next): Response
{
    if (! auth()->check() || ! auth()->user()->is_admin) {
        abort(403);
    }
    return $next($request);
}
```
```php
// routes/web.php
Route::middleware('auth')->group(function () {
    // profile, dashboard
    Route::middleware(IsAdminMiddleware::class)->group(function () {
        Route::resource('categories', CategoryController::class);
        Route::resource('posts', PostController::class)->except(['show']);
    });
});
```

**File:** migrasi `add_is_admin_to_users_table`, `IsAdminMiddleware.php`, `routes/web.php`, navigasi dengan `@if(auth()->user()->is_admin)`.

**Hasil terverifikasi:**
- Admin (`admin@example.com`) akses `/categories` → `200`
- User biasa akses `/categories` → `403 Forbidden`
- Menu Categories/Posts hanya tampil untuk admin

> Catatan gabungan project: `->except(['show'])` dipakai karena route `posts/{post}` sudah dipakai halaman post publik blog (step 9).

---

### Step 13 — Posts CRUD: Performance and Debugbar

**Tujuan:** CRUD posts + eager loading + memasang Debugbar untuk melihat query.

**Perintah:**
```bash
php artisan make:controller PostController --resource --model=Post
composer require barryvdh/laravel-debugbar --dev
```

**Kode inti (eager loading):**
```php
// app/Http/Controllers/PostController.php
public function index()
{
    $posts = Post::with('category')->get();
    return view('posts.index', compact('posts'));
}
```

**File:** `PostController.php`, `resources/views/posts/{index,create,edit}.blade.php`, relasi `category()` di `Post.php`, navigasi link "Posts".

**Hasil terverifikasi:**
- `/posts → 200` (tabel posts + nama kategori), `/posts/create → 200`, edit → 200
- CRUD create/update/delete post jalan (HTTP 302 → redirect ke index)
- Debugbar muncul (terlihat bar di bawah halaman + `phpdebugbar` di HTML)
- User biasa akses `/posts` → `403`

---

### Step 14 — Form Validation and Error Messages

**Tujuan:** Validasi backend dan menampilkan pesan error.

**Perintah:**
```bash
php artisan make:request StorePostRequest
```

**Kode:**
```php
// app/Http/Requests/StorePostRequest.php
public function authorize(): bool
{
    return true;
}

public function rules(): array
{
    return [
        'title' => ['required'],
        'text' => ['required'],
        'category_id' => ['required'],
    ];
}
```
```php
// app/Http/Controllers/PostController.php
public function store(StorePostRequest $request)
{
    Post::create($request->validated());
    return redirect()->route('posts.index');
}
```
```blade
{{-- resources/views/posts/create.blade.php --}}
@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
```

**Hasil terverifikasi:**
- Submit form kosong → `302` redirect balik ke `/posts/create` dan muncul pesan *"The title field is required."*, *"The text field is required."*
- Submit form valid → post berhasil dibuat (`302` → `/posts`)

---

### Step 15 — What to Learn Next?

**Tujuan:** Penutup. Proyek selesai 15/15.

---

## 5. Daftar Route (Hasil `php artisan route:list`)

```text
GET|HEAD  /                    home  › HomeController@index
GET|HEAD  about                about
GET|HEAD  article              article
GET|HEAD  contact              contact
GET|HEAD  posts/{post}         post.show › PostController@show
GET|HEAD  posts                posts.index › PostController@index
POST      posts                posts.store › PostController@store
GET|HEAD  posts/create         posts.create › PostController@create
GET|HEAD  posts/{post}/edit    posts.edit › PostController@edit
PUT|PATCH posts/{post}         posts.update › PostController@update
DELETE    posts/{post}         posts.destroy › PostController@destroy
GET|HEAD  categories           categories.index › CategoryController@index
POST      categories           categories.store › CategoryController@store
GET|HEAD  categories/create    categories.create › CategoryController@create
GET|HEAD  categories/{category}/edit categories.edit › CategoryController@edit
PUT|PATCH categories/{category} categories.update › CategoryController@update
DELETE    categories/{category} categories.destroy › CategoryController@destroy
GET|HEAD  dashboard            dashboard (auth + verified)
GET|HEAD  login / register / logout / profile ...  (auth dari Breeze)
```

Total: **45 route**.

---

## 6. Catatan Penting (Perbedaan dari Tutorial)

Karena blog & admin digabung dalam satu project (sesuai arahan guru), ada penyesuaian kecil yang **tidak ada di tutorial** (di tutorial dua project terpisah):

1. **`posts/{post}` (blog) vs resource `posts` (admin)** — keduanya memakai URI yang sama. Solusi: resource posts memakai `->except(['show'])` agar halaman post publik tetap jalan.
2. **`/posts/create` ketangkap route blog** — route blog diberi `->whereNumber('post')` agar hanya mencocokkan ID angka.
3. **Server `php artisan serve` menyimpan route lama di memori** (karena `opcache.enable_cli=On`) — setiap mengubah route, restart server (`Ctrl+C` lalu `php artisan serve`) dan jalankan `php artisan optimize:clear`.

---

## 7. Bukti Screenshot

Bukti diambil **langsung dari layar** (browser dibuka lalu di-screenshot, dan command dijalankan beneran di terminal lalu output-nya disimpan). File ada di folder [`docs/bukti/`](docs/bukti/):

- `terminal-log.txt` — output asli command di terminal (`php -v`, `composer --version`, `node -v`, `npm -v`, `php artisan --version`, `php artisan route:list`, `php artisan migrate:status`)
- `bukti-01-home.png` — halaman utama blog
- `bukti-02-about.png` — halaman about
- `bukti-03-contact.png` — halaman contact
- `bukti-04-article.png` — halaman article
- `bukti-05-post.png` — halaman detail post `/posts/1`
- `bukti-06-login.png` — halaman login
- `bukti-07-register.png` — halaman register
- `bukti-08-dashboard.png` — dashboard (setelah login) — *bisa diambil manual*
- `bukti-09-categories.png` — halaman CRUD categories (admin) — *bisa diambil manual*
- `bukti-10-posts.png` — halaman CRUD posts (admin) — *bisa diambil manual*
