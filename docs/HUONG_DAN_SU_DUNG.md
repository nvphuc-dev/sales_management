# Hướng dẫn sử dụng — Quản lý bán hàng

Tài liệu này dành cho **người dùng cuối** (nhân viên và quản trị viên) khi làm việc trên giao diện web. Đường dẫn cụ thể phụ thuộc cách cài đặt máy chủ (ví dụ: `http://localhost/sales_management/public/` hoặc tên miền riêng); phần dưới dùng dạng **đường dẫn tương đối** sau tên miền.

---

## 1. Đăng nhập và đăng xuất

### Đăng nhập

1. Mở trang đăng nhập: **`/auth/login`** (hoặc trang chủ `/` sẽ chuyển tới đăng nhập nếu chưa vào phiên).
2. Nhập **tên đăng nhập** và **mật khẩu** do quản trị viên cấp.
3. Bấm **Đăng nhập**.

Sau khi đăng nhập thành công, hệ thống mở **Tổng quan** (dashboard).

### Đăng xuất

- Ở góc trên bên phải, bấm tên của bạn → chọn **Đăng xuất**.

### Hai vai trò

| Vai trò | Mô tả ngắn |
|--------|------------|
| **Nhân viên** | Dùng đầy đủ các chức năng nghiệp vụ (sản phẩm, khách, đơn, nhập, tài xế, xuất Excel, in đơn…). |
| **Quản trị viên** | Giống nhân viên, **thêm** được: quản lý **người dùng**, **thông tin công ty** (hiện trên in đơn). |

Chỉ **quản trị viên** mới thấy mục **Hệ thống** trên menu bên trái và được **tạo / sửa / vô hiệu** tài khoản người dùng.

---

## 2. Bố cục màn hình

- **Menu bên trái**: chuyển nhanh giữa các phân hệ.
- **Thanh trên**: tên đăng nhập, vai trò (Admin / NV), đăng xuất.
- **Khu vực giữa**: nội dung trang (danh sách, form, chi tiết…).

Thông báo thao tác thành công hoặc lỗi thường hiện dạng dải màu xanh / đỏ phía trên nội dung chính.

---

## 3. Tổng quan (Dashboard)

- **Cảnh báo tồn kho**: danh sách sản phẩm có tồn **dưới 5** (badge đỏ). Có thể vào **Sản phẩm** để xem và chỉnh tồn.
- Các ô thông tin khác giúp nắm nhanh tình hình triển khai (xuất Excel, phân quyền…).

---

## 4. Nghiệp vụ chính (menu “Nghiệp vụ”)

### 4.1. Sản phẩm (`/admin/view/products`)

- Xem danh sách, thêm mới, sửa, xóa (có thể có bước xác nhận).
- Theo dõi **SKU**, giá, **tồn kho**.

### 4.2. Khách hàng (`/admin/view/customers`)

- Quản lý khách: thêm, sửa, xóa theo quyền thiết kế hệ thống.
- Dùng chung với đơn hàng và giá theo khách (nếu đã cấu hình).

### 4.3. Nhà cung cấp (`/admin/view/suppliers`)

- Thông tin NCC phục vụ phiếu nhập.

### 4.4. Nhập hàng (`/admin/view/import-orders`)

- Tạo phiếu nhập, xem chi tiết.
- Nhập theo dòng sản phẩm (số lượng, giá…) theo form hệ thống.

### 4.5. Đơn hàng (`/admin/view/orders`)

- **Danh sách**: tìm theo mã đơn / tên khách (ô tìm kiếm nếu có).
- **Tạo đơn**: chọn khách, sản phẩm (có thể dùng ô tìm kiếm gợi ý), số lượng; hệ thống có thể tự lấy **đơn giá** theo khách.
- **Chi tiết đơn**: xem dòng hàng, trạng thái, thanh toán; thu tiền (nếu được phép và đơn chưa đóng).
- **In đơn**: trên trang chi tiết, bấm **In đơn** để mở trang in; trên trang in bấm **In** (trình duyệt) hoặc dùng Ctrl+P. Phần **đầu trang in** lấy từ **Thông tin công ty** (xem mục 6).

Các thao tác **hủy đơn**, **hoàn thành**, **xóa** có thể yêu cầu **xác nhận** (hộp thoại) để tránh nhầm.

### 4.6. Tài xế (`/admin/view/drivers`)

- Quản lý tài xế gắn với đơn giao hàng (theo cấu hình form).

---

## 5. Báo cáo — Xuất Excel (`/admin/export`)

1. Vào **Xuất Excel** trên menu.
2. Chọn **loại dữ liệu**: đơn hàng, phiếu nhập, hoặc khách hàng.
3. Chọn **từ ngày** — **đến ngày** (theo ngày tạo bản ghi trên hệ thống).
4. Bấm **Tải file .xlsx**.

**Lưu ý:** Nếu không tải được file, có thể do máy chủ chưa bật phần mở rộng **ZIP** cho PHP — báo quản trị hệ thống / IT xử lý.

---

## 6. Thông tin công ty (chỉ quản trị viên)

**Đường dẫn:** `/admin/view/company-settings` (menu **Hệ thống** → **Thông tin công ty**).

Điền các mục:

- Tên công ty / cửa hàng (**bắt buộc**)
- Điện thoại, Email
- Địa chỉ 1, Địa chỉ 2
- Mã số thuế
- Website / Fanpage

Bấm **Lưu**. Nội dung này **hiển thị trên trang in đơn** (phần tiêu đề cửa hàng).

---

## 7. Quản lý người dùng (chỉ quản trị viên)

**Đường dẫn:** `/admin/view/users`.

- **Danh sách** tài khoản: vai trò (Quản trị viên / Nhân viên), trạng thái hoạt động.
- **Thêm người dùng**: tên đăng nhập (theo quy tắc trên form), mật khẩu, họ tên, vai trò.
- **Sửa**: đổi họ tên, vai trò, bật/tắt hoạt động; có thể đặt **mật khẩu mới** (để trống nếu giữ nguyên).
- **Vô hiệu**: khóa đăng nhập; không thể vô hiệu chính mình hoặc quản trị viên **duy nhất** còn lại (hệ thống sẽ báo lỗi).

---

## 8. Mẹo khi làm việc

- Luôn **đăng xuất** khi rời máy dùng chung.
- Sau khi sửa dữ liệu quan trọng, kiểm tra lại **danh sách** hoặc **chi tiết** để đối chiếu.
- **In đơn**: nên xem thử bản in (xem trước trang) trước khi in số lượng lớn.
- Nếu hết phiên (hết thời gian đăng nhập), hệ thống sẽ yêu cầu **đăng nhập lại** — không mất dữ liệu đã lưu trước đó.

---

## 9. Khi cần hỗ trợ

- Liên hệ **quản trị viên** hoặc bộ phận IT của đơn vị để: cấp tài khoản, reset mật khẩu, cấu hình địa chỉ truy cập, hoặc xử lý lỗi kết nối / in / xuất file.

*Tài liệu phản ánh chức năng tại thời điểm biên soạn; nếu hệ thống được nâng cấp, một số màn hình có thể bổ sung thêm bước hoặc trường mới.*
