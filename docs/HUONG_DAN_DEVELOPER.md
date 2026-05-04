# Hướng dẫn Developer — Cài đặt, kiến trúc & mở rộng

Tài liệu dành cho **lập trình viên** tham gia bảo trì hoặc mở rộng dự án **sales_management** (CodeIgniter 4 + MySQL + AdminLTE 4). Đọc kèm `README.md` (môi trường, giai đoạn) và `docs/HUONG_DAN_SU_DUNG.md` (nghiệp vụ người dùng).

---

## 1. Yêu cầu môi trường

| Thành phần | Ghi chú |
|------------|---------|
| PHP | ^8.2 (dự án thường chạy 8.3 trên Laragon) |
| MySQL | utf8mb4, database ví dụ `sales_management` |
| Composer | Cài dependency, autoload |
| Extension | **zip** (`ext-zip`) nên bật nếu dùng xuất Excel (PhpSpreadsheet ghi `.xlsx`) |

---

## 2. Cài đặt nhanh (local)

1. Clone repo, `composer install`.
2. Sao chép `env` → `.env` (hoặc chỉnh file `.env` đã có): `app.baseURL`, `database.default.*`, timezone/locale nếu cần.
3. `php spark key:generate` (encryption).
4. Tạo DB rồi: `php spark migrate`.
5. Seed tối thiểu: `php spark db:seed DatabaseSeeder` (user admin mặc định + bản ghi `company_settings` id=1), hoặc `UserSeeder` / `CompanySettingSeeder` riêng.
6. Document root trỏ vào **`public/`** (CI4 chuẩn).
7. Kiểm tra: `php spark routes`, đăng nhập `auth/login`, vào `admin` / `admin/view/...`.

**Lưu ý baseURL:** Một số TLD (`.test`) có thể bị `FILTER_VALIDATE_URL` từ chối — xem `README.md`.

---

## 3. Cấu trúc thư mục quan trọng

```
app/
├── Config/           # Routes, Filters, Services (đăng ký DI), Validation, Database…
├── Controllers/
│   ├── Auth.php                    # Đăng nhập / đăng xuất
│   ├── Home.php                    # / → redirect login hoặc admin
│   ├── Admin/                      # JSON REST (resource) + Dashboard, Export
│   │   └── View/                   # Giao diện SSR (form/danh sách)
│   └── Api/                        # Select2, giá dòng (JSON)
├── Database/Migrations/            # Schema
├── Database/Seeds/                 # User, company_settings…
├── Filters/                        # auth, admin
├── Models/                         # Eloquent-style Model CI4
├── Services/                       # Logic nghiệp vụ (ưu tiên đặt ở đây)
├── Validation/                     # Rule tùy chỉnh (AppRules)
└── Views/admin/                    # Layout + view theo module

public/
└── assets/adminlte/                # CSS/JS/img AdminLTE (đã chuẩn hóa cho layout)

docs/
├── HUONG_DAN_SU_DUNG.md            # End-user
└── HUONG_DAN_DEVELOPER.md          # File này
```

**Nguyên tắc:** Controller mỏng; validate tập trung `Config/Validation.php` + `AppRules`; thay đổi nghiệp vụ ưu tiên sửa **Service** thay vì nhét logic vào View.

---

## 4. Luồng HTTP & routing

### 4.1. Nhóm route (xem `app/Config/Routes.php`)

| Nhóm | Filter | Namespace | Mục đích |
|------|--------|-----------|----------|
| `auth/*` | — | `App\Controllers` | Login (public), logout |
| `admin/view/*` (chính) | `auth` | `Admin\View` | SSR: sản phẩm, khách, NCC, nhập, đơn, tài xế… |
| `admin/view/*` (hẹp) | `auth` + **`admin`** | `Admin\View` | Chỉ admin: **users**, **company-settings** |
| `admin/*` | `auth` | `Admin` | Dashboard, REST resource JSON, export |
| `api/*` | `auth` | `Api` | `search-*`, `line-price` (Ajax/Select2) |

- **`/`** → `Home::index`: đã login → `admin`, chưa → `auth/login`.
- **Chi tiết đơn + in:** `admin/view/orders/(:num)`, `admin/view/orders/(:num)/print` (print **trước** route `orders/(:num)` để tránh ăn nhầm `print` là id).

Đăng ký filter alias trong `app/Config/Filters.php`: `auth` → `AuthFilter`, `admin` → `AdminRoleFilter`.

### 4.2. Hai “mặt” admin

1. **`admin/<resource>`** — REST JSON (Phase 3), dùng Postman/API client; controller `App\Controllers\Admin\*`.
2. **`admin/view/...`** — SSR AdminLTE, form HTML, redirect + flash; controller `App\Controllers\Admin\View\*`, layout `admin/layouts/main.php`.

Khi thêm màn SSR mới: thêm route trong nhóm `admin/view` có `auth`, và (nếu chỉ admin) nhóm `filter => 'admin'`.

---

## 5. Xác thực & phân quyền

- **Session sau login:** `user_id`, `username`, `full_name`, `role` (`employee` | `admin`).
- **`AuthFilter`:** không có `user_id` → redirect `auth/login`.
- **`AdminRoleFilter`:** `role !== admin` → redirect `admin` + flash lỗi.
- **Model:** `app/Models/UserModel.php` — `ROLE_ADMIN`, `ROLE_EMPLOYEE`, `findActiveByUsername()`.
- **Seed mặc định:** `UserSeeder` (`admin` / `Admin@123` — đổi production).

Sửa quyền: điều chỉnh filter trên route hoặc điều kiện trong `main.php` (menu) cho khớp.

---

## 6. Luồng nghiệp vụ chính (Services)

| Service | Vai trò ngắn |
|---------|----------------|
| `ProductCatalogService` | CRUD/sp danh sách, `searchForSelect2` (kể cả `q` rỗng → N sản phẩm active) |
| `CustomerCrudService` / `CustomerService` | Khách, giá đơn vị (`resolveUnitPrice`), Select2 |
| `SupplierCatalogService` | NCC cho phiếu nhập |
| `ImportOrderService` / `ImportOrderReadService` | Tạo/đọc phiếu nhập + dòng |
| `OrderService` / `OrderReadService` | Đơn bán, hủy/hoàn thành/xóa, đọc bundle |
| `PaymentService` | Thu tiền gắn đơn/khách |
| `InventoryService` | Tồn kho (nhập/xuất theo nghiệp vụ) |
| `DriverService` / `DriverCrudService` | Tài xế |
| `ExportSpreadsheetService` | Xuất Excel (PhpSpreadsheet) |
| `Money` | Chuẩn hóa decimal tiền |

Luồng **tạo đơn:** `View\Orders::store` → validate → `OrderService::createOrder` (items, customer, stock…).

Luồng **tạo phiếu nhập:** `View\ImportOrders::store` → `ImportOrderService::createImportOrder`.

Khi đổi quy tắc tồn/công nợ: tìm chỗ gọi trong `OrderService`, `ImportOrderService`, `PaymentService`, `InventoryService` thay vì sửa trực tiếp Model ở nhiều nơi.

---

## 7. API JSON (Ajax)

- **`app/Controllers/Api/Search.php`:** `GET api/search-products|customers|drivers?q=` — trả `{ results: [{id,text}] }` (và có thể bọc thêm lớp `data` tùy formatter CI); Select2 trong view đã xử lý cả hai dạng.
- **`app/Controllers/Api/Pricing.php`:** `GET api/line-price?customer_id=&product_id=` — giá dòng đơn (JSON).

Các route này nằm sau filter **`auth`** (cookie session); gọi từ trình duyệt đã đăng nhập.

---

## 8. Validation & rule tùy chỉnh

- **`app/Config/Validation.php`:** các nhóm rule đặt tên (`orderWebHeader`, `importOrderWebHeader`, `companySettings`, `userAccountCreate`, …).
- **`app/Validation/AppRules.php`:** rule phức tạp (ví dụ kiểm tra tồn dòng đơn).

Thêm form mới: khai báo nhóm rule mới + `validateData()` trong controller tương ứng.

---

## 9. Cơ sở dữ liệu & migration

- Migration trong `app/Database/Migrations/` — chạy tuần tự theo timestamp.
- Bảng nghiệp vụ: `products`, `customers`, `orders`, `order_items`, `import_orders`, `import_order_items`, `transactions`, …
- **`users`:** tài khoản đăng nhập.
- **`company_settings`:** một dòng `id=1` — thông tin in đơn (`CompanySettingModel::getSingletonRow()`).

Sau khi thêm migration: `php spark migrate`. Seed: `php spark db:seed <TênSeeder>`.

---

## 10. Giao diện (Views)

- **Layout:** `app/Views/admin/layouts/main.php` — Bootstrap 5 + AdminLTE, jQuery, Select2, SweetAlert2; section `content`, `scripts`, `page_styles`.
- **Partial lỗi:** `admin/partials/validation_errors.php` (flash `errors` + biến `$errors`).
- **In đơn:** `admin/orders/print.php` — view độc lập (không extend layout), dùng dữ liệu công ty + `OrderReadService`.

Thêm module SSR: tạo view dưới `Views/admin/<module>/`, controller trong `Admin\View\`, cập nhật route + menu sidebar.

---

## 11. Đăng ký service tùy chỉnh

File `app/Config/Services.php`: ví dụ `orderService()`, `exportSpreadsheetService()`.

Tạo service mới: class trong `App\Services`, thêm static `make()` hoặc inject qua constructor, rồi đăng ký method trong `Services` nếu cần singleton qua `getSharedInstance`.

---

## 12. Xuất Excel (Giai đoạn 5)

- Controller: `App\Controllers\Admin\Export`.
- Logic: `App\Services\ExportSpreadsheetService` — query theo `type` + khoảng ngày, format VND, merge ô đơn/phiếu nhập.

Nếu lỗi ghi file: kiểm tra `ext-zip` và quyền ghi buffer output.

---

## 13. Kiểm tra nhanh khi sửa code

1. `php spark routes` — route mới có đúng filter không.
2. Form POST có `csrf_field()` nếu bật CSRF toàn cục (`Config/Filters.php` / `Security.php`).
3. Select2: URL `site_url('api/...')` phải khớp baseURL; `minimumInputLength: 0` nếu cần load khi chưa gõ.
4. Sau thay schema: migration + (nếu cần) cập nhật Model `$allowedFields`.

---

## 14. Tài liệu ngoài repo

- `Prompt.md` — roadmap / prompt theo giai đoạn.
- `data_working/Yeu Cau.txt` — yêu cầu nghiệp vụ tiếng Việt.
- `data_working/Thiet Ke/*.png` — mockup UI (đối chiếu khi chỉnh layout).

---

## 15. Gợi ý mở rộp thường gặp

| Nhu cầu | Gợi ý chỗ sửa |
|---------|----------------|
| Thêm cột bảng | Migration + Model `$allowedFields` + View form/list |
| Thêm API cho frontend | `Controllers/Api` + route nhóm `api` + filter `auth` |
| Thêm trang admin SSR | `Controllers/Admin/View` + `Views/admin/...` + `Routes` + `main.php` menu |
| Đổi quy tắc giá / tồn | `CustomerService`, `OrderService`, `InventoryService` |
| In thêm trường | `orders/print.php` + query trong `OrderReadService` nếu thiếu cột join |
| Phân quyền chi tiết hơn | Filter mới + middleware session hoặc bảng `permissions` (chưa có sẵn) |

---

*Tài liệu phản ánh trạng thái codebase tại thời điểm biên soạn; khi refactor lớn (ví dụ gộp REST + SSR), nên cập nhật lại mục 4 và 6 cho đồng bộ.*
