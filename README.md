# sales_management
Ứng dụng quản lý bán hàng

## Chạy nhanh (Laragon, PHP 8.3.30)
CodeIgniter 4 · AdminLTE 4

1. Sao chép `env` thành `.env` (nếu chưa có), chỉnh `app.baseURL` và MySQL (`database.default.*`). Gõ `php spark key:generate` để tạo `encryption.key`.
2. Tạo database `sales_management` (utf8mb4), rồi: `php spark migrate` và `php spark db:seed UserSeeder` (tài khoản **`admin` / `Admin@123`**) hoặc `php spark db:seed DatabaseSeeder` (gồm cả bản ghi **Thông tin công ty** cho in đơn).
3. Đăng nhập tại `.../auth/login`. Trang `/` và toàn bộ `admin/*`, `admin/view/*`, `api/*` yêu cầu đã đăng nhập. Menu **Người dùng** chỉ hiện với quản trị viên; chỉ admin mới tạo/sửa tài khoản qua `admin/view/users`.
4. Trỏ virtual host **document root** vào thư mục `public` (hoặc mở `http://127.0.0.1/sales_management/public/admin`).
5. Nếu dùng vhost `.test` mà CI4 báo baseURL không hợp lệ, đổi `app.baseURL` sang `http://127.0.0.1/...` hoặc `http://localhost/...` (PHP `FILTER_VALIDATE_URL` có thể từ chối một số TLD).

Nguyên tắc chung
Stack: CodeIgniter 4, PHP 8.3.30, MySQL, AdminLTE 4 (Bootstrap 5), SSR + JSON cho Ajax/Select2.
Kiến trúc: MVC CI4 + Services/ (logic tồn kho, công nợ, đơn) + Entities/ (type-safe); Controllers mỏng; Validation tập trung.
UI: Copy/chuẩn hóa asset AdminLTE vào public/ (hoặc writable tùy chính sách), layout app/Views/admin/layouts/. Đối chiếu từng màn hình với ảnh data_working/Thiet Ke/*.png (bố cục, luồng, nhãn tiếng Việt) — dùng làm tiêu chí nghiệm thu UI, không chỉ “có chức năng”.
Linhh hoạt: Trong mỗi giai đoạn có thể làm MVP trước (đủ gate tối thiểu), rồi mở rộng (responsive mobile, in hóa đơn, export Thu tiền…) trong cùng giai đoạn hoặc giai đoạn kế tiếp tùy ưu tiên.

Cổng kiểm tra chung (áp dụng mọi giai đoạn)
Trước khi xác nhận sang giai đoạn sau, cần:

Chạy được: php spark migrate (và seed nếu có) không lỗi; route chính truy cập được.
Kiểm thử thủ công: checklist theo deliverable của giai đoạn (ghi rõ trong issue/PR).
Rà soát nhanh: validation đầu vào, không để logic nặng trong view; không commit secret/DB credentials.

Giai đoạn 0 — Khởi tạo dự án & chuẩn bị UI (nền tảng)
Mục tiêu: Repo CI4 chuẩn, môi trường Laragon, asset AdminLTE, “khung” layout trống + đăng nhập (nếu cần sau).

Công việc:

Cài/khởi tạo CI4 (PHP 8.3.30), .env, timezone, locale vi.
Sao chép CSS/JS/img AdminLTE vào public (hoặc đường dẫn cố định), tham chiếu index.html / layout/fixed-complete.html làm base.
Tạo layout admin: header, sidebar, content — map menu với các module trong Yeu Cau.txt (có thể ẩn link chưa làm).
Lập bảng đối chiếu từng màn hình: Thiet Ke/01.png … → route/view tương ứng (bảng này cập nhật khi làm UI).
Gate xác nhận:

Trang admin mở được, load đúng AdminLTE (không 404 asset).
Bảng mapping Thiết kế ↔ route đã có và được stakeholder duyệt.

Giai đoạn 1 — Cơ sở dữ liệu & thực thể (theo Prompt I + nền cho Yêu cầu)
Mục tiêu: Schema đủ cho: sản phẩm, khách, giá riêng, NCC, nhập hàng, đơn bán, tài xế, giao dịch thu tiền.

Công việc:

Migration đúng các bảng trong Prompt.md; bổ sung trường còn thiếu so với Yeu Cau nếu cần, ví dụ:
Sản phẩm: display_order (sắp thứ tự), có thể cần cờ/thống kê tồn kho lấy từ tổng nhập–bán hoặc lưu stock_quantity đồng bộ qua service (Prompt đã có stock_quantity).
Phiếu nhập: mã phiếu tùy chỉnh (code/reference), liên kết supplier_id.
Đơn hàng: trạng thái xử lý + trạng thái thanh toán; liên kết driver_id, ghi chú giao hàng nếu có.
transactions: đủ để ghi nhận thu theo đơn; ràng buộc FK hợp lý.
Entities PHP 8.3.30 (constructor promotion, strict types).
Gate xác nhận:

Migrate lên DB sạch + rollback thử một lần.
ERD hoặc danh sách FK được review (không mâu thuẫn với luồng hoàn tồn kho / hủy đơn).

Giai đoạn 2 — Lớp dịch vụ (theo Prompt II + logic Yêu cầu 1–6)
Mục tiêu: “Bộ não” tập trung; mọi thay đổi tồn kho và công nợ đi qua transaction và service.

Công việc (tối thiểu):

InventoryService: nhập (+), bán (-), hoàn khi sửa/xóa dòng đơn, hoàn khi xóa đơn.
CustomerService: adjustDebt, getCustomPrice; cập nhật total_purchase, total_paid, current_debt nhất quán với quy tắc nghiệp vụ (ghi rõ: khi nào tăng nợ, khi nào giảm).
OrderService: tạo đơn (kiểm tồn → giá ưu tiên customer_prices → lưu → trừ kho → cập nhật khách); mở rộng cập nhật chi tiết đơn đang hoạt động và xóa đơn (hoàn kho + điều chỉnh công nợ + xóa/đảo giao dịch thu liên quan) theo mục 4 Yeu Cau.
ImportOrderService (hoặc tương đương): lưu phiếu nhập + cộng tồn.
PaymentService / phần trong CustomerService: thu tiền theo đơn, cảnh báo không thu vượt, đủ tiền → cập nhật trạng thái thanh toán đơn (mục 6).
DriverService hoặc logic trong OrderService: gán tài xế, chuyển trạng thái tài xế (rảnh/bận) khi nhận đơn/hoàn thành (mục 5).
Gate xác nhận:

Viết kịch bản test thủ công (hoặc unit test tối thiểu) cho: tạo đơn → thu một phần → thu đủ; sửa số lượng dòng đơn; hủy đơn; nhập hàng; giá riêng khách.
Không có đường “sửa DB trực tiếp” bypass service cho nghiệp vụ lõi.

Giai đoạn 3 — Controllers & API (theo Prompt III)
Mục tiêu: CRUD qua service; JSON cho Select2; validation (SĐT VN 10 số, email, không bán quá tồn).

Công việc:

Resource controllers: Product, Customer, Supplier, ImportOrder, Order, Driver (+ endpoint thu tiền nếu tách controller).
API: /api/search-products, search-customers, search-drivers (và có thể thêm search NCC).
Rules validation tái sử dụng; thông báo lỗi rõ ràng cho form và Ajax.
Gate xác nhận:

Postman/cURL hoặc trình duyệt: các API search trả JSON đúng định dạng Select2.
CRUD cơ bản qua HTTP không lỗi 500; lỗi validation trả có cấu trúc ổn định.

Giai đoạn 4 — Giao diện AdminLTE & trải nghiệm (Prompt IV + Yêu cầu 8)
Mục tiêu: SSR hoàn chỉnh các module; Select2, SweetAlert2, tính tổng realtime; bắt đầu bám mockup Thiết kế.

Công việc (theo thứ tự ưu tiên nghiệp vụ):

Danh sách + form: Sản phẩm (lọc tồn: tất cả / sắp hết / hết), Khách + tab giá riêng + lịch sử mua, NCC, Phiếu nhập, Đơn hàng (tạo/sửa/xóa có xác nhận), Tài xế, Thu tiền.
Desktop: bảng; Mobile: card/grid theo Yeu Cau (có thể chia sprint nhỏ: trước Desktop, sau responsive).
Hiển thị tổng số bản ghi trên các trang list.
In hóa đơn (view print CSS) theo mục 4 Yeu Cau.
Gate xác nhận:

Walkthrough với ảnh Thiet Ke: từng màn chính khớp layout/chức năng đã thống nhất.
Luồng tạo đơn: chọn KH → SP → giá ưu tiên đúng → SweetAlert xóa/lưu thành công.

Giai đoạn 5 — Báo cáo, Excel & dashboard (Prompt V + Yêu cầu 7)
Mục tiêu: PhpSpreadsheet; bộ lọc thời gian; dashboard cảnh báo tồn < 5.

Công việc:

ExportController: type (orders, imports, customers, payments nếu đã có dữ liệu thu), start_date, end_date; preset hôm nay/tuần/tháng trên UI.
Format VND, header đậm, merge dòng theo đơn (theo Prompt).
Dashboard: badge đỏ sản phẩm sắp hết; có thể mở rộng widget doanh thu sau.
Gate xác nhận:

File Excel mở đúng, số liệu khớp vài case đã chuẩn bị trên DB.
Dashboard hiển thị đúng ngưỡng cảnh báo.

Cách vận hành “linh hoạt” giữa các giai đoạn
Song song an toàn: Sau Giai đoạn 2, có thể chia nhánh: một người làm API (3), một người làm layout + asset (0/4) — vẫn không merge nhánh UI hoàn chỉnh lên main cho đến khi gate Giai đoạn 2 đạt.
Ưu tiên theo rủi ro: Schema + Service (1–2) là đường găng; UI (4) bám sau khi API ổn định để tránh sửa form nhiều lần.
Mỗi giai đoạn = một milestone có tên + ngày + người xác nhận (PM/chủ dự án): chỉ “chốt” khi checklist gate đủ.

Tóm tắt ánh xạ Yêu cầu → giai đoạn
Yêu cầu (Yeu Cau)	Chủ yếu ở giai đoạn
1 Sản phẩm & tồn kho
1, 2, 3, 4, 5 (dashboard)
2 Khách & giá riêng
1, 2, 3, 4
3 Nhập hàng
1, 2, 3, 4
4 Đơn hàng & bán
1, 2, 3, 4
5 Tài xế
1, 2, 3, 4
6 Thu tiền
1, 2, 3, 4
7 Excel & báo cáo
5
8 UI/UX
0, 4 (+ Thiet Ke)