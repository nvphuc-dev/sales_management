"Tôi đang làm dự án Quản lý bán hàng bằng CI4, PHP 8.3. Cấu trúc thư mục theo chuẩn CI4. Database sử dụng MySQL. Backend dùng AdminLTE 4. Hãy ưu tiên viết code sạch (Clean Code), sử dụng Type Hinting mạnh mẽ của PHP 8.3, và luôn kiểm tra dữ liệu đầu vào (Validation)."

I. Giai đoạn 1: Khởi tạo Project & Database Schema
Mục tiêu: Thiết lập cấu trúc thư mục, Migration và các quan hệ thực thể.

Prompt:
"Tôi đang xây dựng hệ thống quản lý bán hàng bằng CodeIgniter 4, PHP 8.3.

Bước 1: Hãy tạo các file Migration cho các bảng sau:

products: id, name, sku, purchase_price, selling_price, stock_quantity, status (active, inactive).

customers: id, name, phone, email, address, total_purchase, total_paid, current_debt.

customer_prices: id, customer_id, product_id, custom_price (Để quản lý giá riêng cho từng khách).

suppliers: id, name, contact_info.

import_orders & import_order_items: Lưu thông tin nhập hàng.

orders & order_items: Lưu thông tin bán hàng, trạng thái đơn (pending, shipping, completed, cancelled), trạng thái thanh toán.

drivers: id, name, license_plate, status (available, busy).

transactions: id, order_id, customer_id, amount, type (payment_in), created_at.

Yêu cầu: Sử dụng Foreign Key để ràng buộc dữ liệu. Sử dụng Strict Typing và Constructor Promotion của PHP 8.3 cho các Entity."

II. Giai đoạn 2: Xử lý Logic lõi (Services Layer)
Mục tiêu: Tạo bộ não cho hệ thống để tính toán tồn kho và công nợ tự động.

Prompt:
"Dựa trên Database đã tạo, hãy viết các Service Classes trong thư mục app/Services:

InventoryService: Có hàm updateStock(productId, quantity, type) để cộng/trừ tồn kho khi nhập/bán hàng.

CustomerService: Có hàm adjustDebt(customerId, amount) để cập nhật công nợ và getCustomPrice(customerId, productId) để lấy giá ưu đãi nếu có.

OrderService: Hàm createOrder(data) xử lý trong một Database Transaction. Logic: Kiểm tra tồn kho -> Tính tổng tiền dựa trên customer_prices hoặc giá mặc định -> Lưu đơn hàng -> Trừ tồn kho -> Cập nhật công nợ khách hàng.

Viết code theo nguyên tắc SOLID, đảm bảo logic tính toán không nằm ở Controller."

III. Giai đoạn 3: Xây dựng Backend Controllers & API
Mục tiêu: Tạo các endpoint để giao tiếp với Frontend sau này và hỗ trợ Select2.

Prompt:
"Hãy tạo các Resource Controllers cho: Product, Customer, ImportOrder, Order, Driver.

Yêu cầu:

Các hàm index, store, update, delete phải gọi qua Service Layer ở Giai đoạn 2.

Viết thêm các API endpoint trả về JSON cho Select2 để tìm kiếm nhanh: /api/search-products, /api/search-customers, /api/search-drivers.

Validation: Sử dụng Config\Validation để kiểm tra định dạng số điện thoại Việt Nam (10 số), email, và không cho phép bán quá số lượng tồn kho."

IV. Giai đoạn 4: Tích hợp Giao diện AdminLTE 4 (Phần Backend)
Mục tiêu: Render giao diện chuẩn và xử lý UI/UX.

Prompt:
"Tôi đã có source AdminLTE-4.0.0-rc7. Hãy thiết lập Layout chính (app/Views/layouts/admin.php).

Yêu cầu:

Tạo View cho danh sách đơn hàng và form tạo đơn hàng.

Sử dụng Select2 cho việc chọn khách hàng và sản phẩm.

Logic Frontend: Viết Javascript (hoặc Alpine.js) để khi chọn sản phẩm trong đơn hàng, nó tự động gọi API lấy giá (ưu tiên giá riêng của khách) và tính tổng tiền Real-time khi thay đổi số lượng.

Tích hợp SweetAlert2 để xác nhận khi bấm 'Xóa đơn hàng' và hiển thị thông báo thành công sau khi lưu."


V. Giai đoạn 5: Hệ thống Báo cáo & Xuất Excel
Mục tiêu: Trích xuất dữ liệu.

Prompt:
"Sử dụng thư viện PhpSpreadsheet để viết chức năng xuất Excel.

Tạo ExportController nhận các tham số: type (orders, imports, customers), start_date, end_date.

Format Excel: Phải có header in đậm, định dạng tiền tệ (VND) cho các cột giá, và gộp dòng (merge cells) cho những sản phẩm thuộc cùng một đơn hàng.

Thêm tính năng 'Cảnh báo tồn kho': Một trang Dashboard đơn giản hiển thị số lượng sản phẩm có stock_quantity < 5 sử dụng các Badge màu đỏ của AdminLTE 4."


Kế hoạch Phát triển Hệ thống (Architecture Plan)
Tách biệt Backend và Frontend, đồng thời sử dụng AdminLTE 4 (dựa trên Bootstrap 5), chúng ta sẽ tổ chức theo hướng Modular Monolith hoặc chuẩn MVC của CI4 nhưng tối ưu hóa cho RESTful API hoặc Server-Side Rendering (SSR) tùy mục tiêu tương lai.

app/
├── Controllers/
│   ├── Admin/ (Chứa logic Backend)
│   └── Api/   (Cho các yêu cầu Select2/Ajax)
├── Models/
├── Services/  (Nơi chứa logic tính toán Tồn kho, Công nợ - Cursor nên viết ở đây)
├── Entities/  (Định nghĩa đối tượng dữ liệu)
├── Views/
│   ├── admin/
│   │   ├── layouts/ (AdminLTE 4 wrapper)
│   │   ├── products/
│   │   └── orders/
└── Database/
    ├── Migrations/
    └── Seeds/