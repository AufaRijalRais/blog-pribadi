# Dokumentasi Praktikum — Laravel Blog + Admin Panel

Project ini dibuat sambil ngikutin kursus **"Laravel 12 For Beginners: Your First Project"** dari Laravel Daily, dikerjain dari awal install sampe selesai bagian validation.

Di kursusnya, blog dan project admin (Breeze) sebenarnya dibuat jadi **dua project terpisah**. Tapi karena arahan gurunya digabung, di sini dua-duanya jadi **satu project** — halaman blog buat pengunjung dan panel admin (login, CRUD category & post) ada di project yang sama.

---

## 1. Lingkungan / Tools yang Dipakai

Versi tools yang terpasang pas ngerjain project ini:

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

Dependency utama (ada di `composer.json`):

| Package | Keterangan |
|---|---|
| `laravel/framework ^12.0` | Framework Laravel 12 |
| `laravel/breeze ^2.4` (dev) | Starter kit autentikasi (Blade + Alpine) |
| `barryvdh/laravel-debugbar ^4.4` (dev) | Debugbar buat ngelihat query (Step 13) |

Database-nya pake **SQLite** (`database/database.sqlite`). Untuk tampilan, halaman admin pake Tailwind CSS v4 lewat Vite, sedangkan halaman blog pake Tailwind dari CDN.

---

## 2. Fitur Aplikasi

### Blog (publik, gak perlu login)
- Halaman utama: daftar **Latest Posts** + sidebar **Categories**, bisa difilter pake `/?category_id={id}`
- Halaman `about`, `contact`, `article`, dan halaman detail post per ID (`/posts/{post}`)
- Datanya diambil dari database pake **Eloquent**

### Autentikasi & Admin (harus login dulu)
- Register / Login / Logout / Forgot password / Profile (dari **Laravel Breeze**)
- Dashboard
- **CRUD Categories** (khusus admin)
- **CRUD Posts** (khusus admin) + relasi kategori + eager loading
- **Validasi form** di halaman create post

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
npm run build                 # atau npm run dev (di terminal terpisah)

# 5. Jalankan server
php artisan serve
# buka http://127.0.0.1:8000
```

### Akun & Data Contoh

Akun admin yang udah dibuat pas praktik:

| Email | Password | is_admin |
|---|---|---|
| `admin@example.com` | `password` | `true` |

Buat user lain lewat form `/register`. Kalo mau dijadikan admin:

```bash
php artisan tinker
# lalu ketik:
# \App\Models\User::where('email','user@example.com')->update(['is_admin'=>true]);
```

Data contoh (Category 1–4 dan Post 1–4) dibuat manual langsung di database pas praktik. Karena `database/database.sqlite` masuk `.gitignore`, data itu gak ikut ke-push. Kalo mau ditanam ulang, contohnya gini:

```php
\App\Models\Category::create(['name' => 'Category 1']);
// ... dan seterusnya sampai Category 4, lalu:
\App\Models\Post::create(['title' => 'Post 1', 'text' => 'Something something very long text. Something something very long text. Something something very long text. Something something very long text.', 'category_id' => 1]);
// ... dan seterusnya
```

Data contoh ini sifatnya opsional, yang penting projectnya jalan dulu.

---

## 4. Dokumentasi Step 1–15

Semua command di bawah beneran dijalanin di terminal, dan hasilnya dicek langsung lewat browser atau `Invoke-WebRequest` (status HTTP code). Bukti screenshotnya ada di folder [`docs/bukti/`](docs/bukti/).

### Step 1 — Required Tools and Laravel Installation

Nyiapin tools (PHP, Composer, Node) dan bikin project Laravel baru.

```bash
laravel new project
# pilih None starter kit (default Laravel 12)
```

Hasilnya keluar folder project lengkap dengan struktur Laravel 12 (app, bootstrap, config, database, public, resources, routes, storage, tests, vendor).

---

### Step 2 — Routing and Creating New Page

Belajar nulis route dan bikin halaman sederhana.

```php
// routes/web.php
Route::view('/', 'home');
```

File yang dibuat: `routes/web.php`, `resources/views/home.blade.php`

Hasil: `/` nampilin halaman `home`. Status `GET / → 200`.

---

### Step 3 — Tailwind in Laravel: New Homepage Design

Desain homepage pake Tailwind CSS.

```bash
npm install
npm run build
```

`home.blade.php` awalnya pake Tailwind CDN, terus diganti pake `@vite([...])` biar CSS-nya di-compile. Hasilnya homepage udah rapi, `GET / → 200`.

---

### Step 4 — Navigation and Reusable Main Layout

Bikin layout utama biar header/footer gak ditulis ulang di tiap halaman.

```php
// routes/web.php
Route::view('/', 'home')->name('home');
Route::view('contact', 'contact')->name('contact');
Route::view('about', 'about')->name('about');
```

File: `resources/views/layouts/app.blade.php`, `home.blade.php`, `about.blade.php`, `contact.blade.php`

Hasil: navigasi antar halaman pake route name. `/about → 200`, `/contact → 200`.

---

### Step 5 — New Design Layout for Blog Project

Layout blog dengan sidebar, pake `asset()` buat gambar.

```php
// routes/web.php
Route::view('article', 'article')->name('article');
```

File: `resources/views/layouts/blog.blade.php`, `home.blade.php`, `article.blade.php`, `about.blade.php`, `contact.blade.php`; gambarnya di `public/images/placeholder-150x150.png` dan `placeholder-800x400.png`.

Hasil: desain blog (My Blog + menu About Us/Contact) tampil. `/article → 200`.

---

### Step 6 — Database Structure and Migrations

Bikin tabel `categories` dan `posts` pake migration.

```bash
php artisan make:migration create_categories_table
php artisan make:migration create_posts_table
php artisan migrate
```

File: `database/migrations/2026_08_11_065107_create_categories_table.php`, `2026_08_11_065825_create_posts_table.php`

Hasil di terminal:

```text
INFO  Running migrations.
  create_categories_table ................. DONE
  create_posts_table ...................... DONE
```

---

### Step 7 — MVC, DB Queries, and Eloquent Models

Bikin Model + Controller, dan ngambil data dari DB pake Eloquent.

```bash
php artisan make:model Category
php artisan make:model Post
php artisan make:controller HomeController
```

Kode intinya:

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

File: `app/Models/Category.php`, `app/Models/Post.php`, `app/Http/Controllers/HomeController.php`, `routes/web.php`, `resources/views/home.blade.php`

Hasil: `GET / → 200`, halaman utama nampilin posts & categories dari database.

---

### Step 8 — Eloquent Relations and GET Parameters

Relasi `belongsTo` (Post → Category) dan filtering lewat GET parameter.

```php
// app/Models/Post.php
public function category()
{
    return $this->belongsTo(Category::class);
}
```

Hasil: `GET /?category_id=2` cuma nampilin post dari kategori 2. Dicek di browser, halamannya berisi `Post 2` dan gak ada `Post 1`.

---

### Step 9 — Route Model Binding with Parameters

Halaman detail post per ID pake route model binding.

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

Hasil: `GET /posts/1 → 200`, dan `/posts/999 → 404`.

> Catatan (karena project digabung): `whereNumber('post')` ditambah biar `/posts/create` (halaman admin di step 13) gak ketangkap sama route blog `posts/{post}`.

---

### Step 10 — Starter Kits and Using Laravel Breeze

Pasang autentikasi (login/register/dashboard) pake Laravel Breeze.

```bash
composer require laravel/breeze --dev
php artisan breeze:install blade --no-interaction
php artisan migrate
npm install && npm run build
```

File baru: `routes/auth.php`, `app/Http/Controllers/Auth/*`, `app/Http/Controllers/ProfileController.php`, `resources/views/auth/*`, `resources/views/dashboard.blade.php`, `resources/views/profile/*`, `resources/views/layouts/app.blade.php`, `resources/views/layouts/navigation.blade.php`, `resources/views/components/*`.

Hasil: `/login → 200`, `/register → 200`, sedangkan `/dashboard` ngelunjak ke `/login` kalo belum login.

---

### Step 11 — Categories CRUD: Index, Create, Update, Delete

CRUD kategori di panel admin.

```bash
php artisan make:controller CategoryController --resource --model=Category
```

File: `app/Http/Controllers/CategoryController.php`, `resources/views/categories/{index,create,edit}.blade.php`, link "Categories" di `layouts/navigation.blade.php`.

Kode intinya:

```php
public function store(Request $request)
{
    Category::create(['name' => $request->input('name')]);
    return redirect()->route('categories.index');
}
```

Hasil (login sebagai admin): `/categories → 200` nampilin tabel kategori, tambah data → 302 balik ke index, edit & delete jalan. Kalo belum login → disuruh login dulu.

---

### Step 12 — Admin User, Route Groups, Middleware

Nambahin kolom `is_admin`, bikin custom middleware, dan melindungi route kategori biar cuma admin yang bisa akses.

```bash
php artisan make:migration add_is_admin_to_users_table
php artisan make:middleware IsAdminMiddleware
php artisan migrate
```

Kodenya:

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

File: migrasi `add_is_admin_to_users_table`, `IsAdminMiddleware.php`, `routes/web.php`, dan navigasi yang pake `@if(auth()->user()->is_admin)`.

Hasil yang dicek:
- Admin (`admin@example.com`) buka `/categories` → `200`
- User biasa buka `/categories` → `403 Forbidden`
- Menu Categories/Posts cuma muncul di user admin

> Catatan (karena project digabung): `->except(['show'])` dipake soalnya route `posts/{post}` udah kepake sama halaman post publik blog (step 9).

---

### Step 13 — Posts CRUD: Performance and Debugbar

CRUD posts + eager loading + pasang Debugbar buat ngelihat query.

```bash
php artisan make:controller PostController --resource --model=Post
composer require barryvdh/laravel-debugbar --dev
```

Kode inti (eager loading):

```php
// app/Http/Controllers/PostController.php
public function index()
{
    $posts = Post::with('category')->get();
    return view('posts.index', compact('posts'));
}
```

File: `PostController.php`, `resources/views/posts/{index,create,edit}.blade.php`, relasi `category()` di `Post.php`, link "Posts" di navigasi.

Hasil yang dicek:
- `/posts → 200` (tabel posts + nama kategori), `/posts/create → 200`, edit → 200
- CRUD create/update/delete post jalan (302 → balik ke index)
- Debugbar muncul (ada bar di bawah halaman + `phpdebugbar` di HTML)
- User biasa buka `/posts` → `403`

---

### Step 14 — Form Validation and Error Messages

Validasi di sisi backend dan nampilin pesan error.

```bash
php artisan make:request StorePostRequest
```

Kodenya:

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

Hasil yang dicek:
- Submit form kosong → `302` balik ke `/posts/create` dan muncul pesan *"The title field is required."*, *"The text field is required."*
- Submit form yang diisi bener → post kebuat (302 → `/posts`)

---

### Step 15 — What to Learn Next?

Penutup dari kursus, project udah selesai 15/15.

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

Karena blog & admin digabung dalam satu project (sesuai arahan guru), ada penyesuaian kecil yang di tutorial gak ada (di tutorial dua projectnya dipisah):

1. **`posts/{post}` (blog) vs resource `posts` (admin)** — dua-duanya pake URI yang sama. Solusinya resource posts dikasih `->except(['show'])` biar halaman post publik tetep jalan.
2. **`/posts/create` ketangkap route blog** — route blog dikasih `->whereNumber('post')` biar cuma cocok sama ID angka.
3. **Server `php artisan serve` nyimpen route lama di memori** (karena `opcache.enable_cli=On`) — tiap ganti route, server harus di-restart (`Ctrl+C` lalu `php artisan serve`) dan jalankan `php artisan optimize:clear`.

---

## 7. Bukti Screenshot

Buktinya diambil langsung dari layar — browser dibuka beneran terus di-screenshot, dan command-nya dijalanin beneran di terminal lalu output-nya disimpen. Filenya ada di folder [`docs/bukti/`](docs/bukti/):

- `terminal-log.txt` — output asli command di terminal (`php -v`, `composer --version`, `node -v`, `npm -v`, `php artisan --version`, `php artisan route:list`, `php artisan migrate:status`)
- `bukti-01-home.png` — halaman utama blog
- `bukti-02-about.png` — halaman about
- `bukti-03-contact.png` — halaman contact
- `bukti-04-article.png` — halaman article
- `bukti-05-post.png` — halaman detail post `/posts/1`
- `bukti-06-login.png` — halaman login
- `bukti-07-register.png` — halaman register
- `bukti-08-dashboard.png` — dashboard setelah login sebagai admin
- `bukti-09-categories.png` — halaman CRUD categories (admin)
- `bukti-10-posts.png` — halaman CRUD posts (admin)
