# V8.1 — sửa 5 lỗi theo phản hồi

1. **Trang mất hình**: ảnh cũ đã xóa khỏi theme (spa-hero, spa-promo, spa-banner-*, spa-gallery-*) được tự thay bằng ảnh còn tồn tại khi hiển thị nội dung (không sửa DB). Trang Hình ảnh cũ bị hỏng được chuyển sang shortcode album mới. Công cụ mới **Công cụ → Kiểm tra ảnh Beauty** liệt kê nội dung còn ảnh lỗi/chưa có ảnh đại diện. Album ảnh nay quản lý tại Admin → **Album ảnh** (đăng ảnh thật + chọn album); khi chưa có ảnh nào, dùng ảnh minh họa của theme. *Môi trường làm việc không có Internet nên không tải được ảnh từ GitHub/kho ảnh; cần bạn tải ảnh thật lên Media Library.*
2. **Mua hàng**: giỏ hàng + thanh toán tích hợp (không cần WooCommerce): Thêm vào giỏ / Mua ngay, trang `/gio-hang/` (đổi số lượng, xóa), `/thanh-toan/` (COD hoặc chuyển khoản), đơn lưu ở Admin → **Đơn hàng** (đổi trạng thái), email báo cho spa và khách. Giá luôn tính lại ở máy chủ. **Không có cổng thanh toán thẻ/ví**; cần thì cài WooCommerce (theme tự chuyển sang WooCommerce). Cấu hình phí ship/chuyển khoản: Tùy biến → Cửa hàng. Nên loại trừ `/gio-hang/` và `/thanh-toan/` khỏi cache.
3. **Phân trang**: viết lại giao diện dạng nút tròn "‹ Trước 1 2 3 … 9 Sau ›" khớp màu theme.
4. **Album**: thêm ô album có ảnh bìa + số ảnh; bấm để hiện đúng ảnh của danh mục (hoạt động cả khi tắt JS nhờ `?album=`), tiêu đề album hiện tại, lightbox chỉ duyệt ảnh của album đang chọn.
5. **Thanh liên hệ**: một dòng duy nhất cố định ở đáy gồm **Hotline · Đặt lịch · Zalo · Google Map** (Messenger hiện thêm nếu có cấu hình). Zalo mặc định `zalo.me/<hotline>`, Map mặc định tìm theo địa chỉ; hãy nhập liên kết thật trong Tùy biến.

Đã test Chromium: dock cùng một hàng (4 nút), lọc album 6→2→6 ảnh, lightbox 1/2, số giỏ hàng đọc đúng từ cookie, không lỗi JS. **Chưa test**: PHP thật (đặt hàng, email, thêm giỏ qua admin-post), vì không có PHP/WordPress ở đây.

# Beauty Spa V8 — những thay đổi so với V7.1

## Nguyên nhân các lỗi bạn gặp
| Lỗi | Nguyên nhân gốc | Cách xử lý |
|---|---|---|
| Chữ tiêu đề bị tách dấu ("nhấ´t", "đầ`u") | Font Georgia thiếu glyph chồng dấu tiếng Việt (ế, ầ, ằ, ổ…) + letter-spacing âm | Tự lưu trữ font **TeX Gyre Pagella** (tiêu đề) và **TeX Gyre Heros** (nội dung) – đã kiểm tra đủ glyph tiếng Việt; bỏ toàn bộ letter-spacing âm |
| Banner không tự chuyển, bấm ảnh không phóng to | JS bị plugin cache/tối ưu (LiteSpeed, WP Rocket, Cloudflare…) trì hoãn hoặc dùng bản cache cũ | JS in trực tiếp ở footer kèm cờ `data-no-optimize/data-cfasync…`, chạy ngay cả khi DOM đã sẵn sàng |
| Trang dịch vụ/sản phẩm không vào được | Chưa flush permalink sau khi đổi cấu trúc URL; website chưa cài WooCommerce nên mục sản phẩm trống | Tự flush rewrite 1 lần mỗi phiên bản; thêm **cửa hàng tích hợp** (không cần WooCommerce) |
| Ảnh trùng | Chỉ có 12 ảnh khác nhau trong theme; nhiều khu vực dùng lại | Mỗi khu vực trên trang chủ dùng nhóm ảnh khác nhau; thẻ tin tức không có ảnh dùng hình SVG thương hiệu thay vì ảnh trùng. **Để hết trùng hẳn cần ảnh thật của cơ sở.** |

## Đã kiểm thử bằng Chromium thật (trên trang mô phỏng đúng markup + CSS + JS của theme)
- Slider tự chuyển 0→1→2 sau mỗi ~4,5 s; nút trước/sau, chấm điều hướng hoạt động; dừng khi hover; nút Tạm dừng/Phát hoạt động; với `prefers-reduced-motion` thì không tự chuyển.
- Lightbox: mở ở giữa màn hình, đếm 3/6 → 4/6 khi nhấn →, Esc đóng, khóa cuộn nền và khôi phục; lọc album theo nhóm, lightbox chỉ duyệt ảnh đang hiển thị.
- Không có lỗi JavaScript console. Font BeautySerif/BeautySans tải được; chữ ằ ầ ế ổ ữ ỳ hiển thị đúng.
## Chưa kiểm thử được
- PHP (không có php CLI): chỉ kiểm tra cân bằng ngoặc. **Hãy chạy `php -l` và test trên staging.**
- Các trang do WordPress render (thẻ dịch vụ, sản phẩm, form đặt lịch, tìm kiếm, giỏ hàng) chưa chạy thật.
- Chưa test responsive thực tế trên điện thoại.

## Cửa hàng tích hợp
- Quản lý tại Admin → **Sản phẩm (cửa hàng Beauty)**: giá, giá KM, thương hiệu, dung tích, SKU, tình trạng, thành phần, hướng dẫn, lưu ý. 18 sản phẩm **mẫu** (có nhãn "Giá mẫu"/ảnh minh họa) – hãy thay bằng sản phẩm thật.
- Chưa có giỏ hàng/thanh toán: nút "Đặt mua" dẫn tới Zalo/hotline. Muốn bán online hãy cài WooCommerce; theme sẽ tự chuyển sang dùng WooCommerce.
- URL: `/cua-hang/`, `/nhom-san-pham/<tên>/`.

## Trang dịch vụ, FAQ, đánh giá
- Trang chi tiết dịch vụ mới: banner, giá/thời lượng, giới thiệu, phù hợp với ai, lợi ích & giới hạn, cần tư vấn trước, các bước, chuẩn bị, chăm sóc sau, FAQ, dịch vụ liên quan, nút đặt lịch (nhập tại ô "Chi tiết trang dịch vụ" khi sửa dịch vụ).
- FAQ (Admin → Câu hỏi thường gặp): 8 câu khởi tạo, 2 câu có chỗ **[chủ spa cần cập nhật]** (hủy lịch, giao hàng/đổi trả).
- Đánh giá (Admin → Đánh giá khách hàng): để trống; mục chỉ hiện khi bạn nhập đánh giá thật.
- Form đặt lịch: thêm số người, kênh xác nhận, chặn giờ đã qua, giới hạn gửi lặp, trạng thái "chờ xác nhận".

# Báo cáo kiểm tra và nâng cấp — Beauty Spa V7.1

Đây là **đợt 1** (Giai đoạn 1–3 trong master prompt: audit, nền tảng ảnh/CSS, header, hero, album). Các section còn lại xem mục "Chưa làm".

## 1. Lỗi tìm thấy trong V7.0 và cách xử lý

| Mức | Lỗi (nguyên nhân gốc) | Xử lý trong V7.1 |
|---|---|---|
| Cao | **11/23 ảnh trong theme thực chất là cùng một bức ảnh** (spa-hero, promo, banner-*, gallery-*) chỉ khác cách cắt. 4 tệp có **viền đen 26–58% diện tích** (spa-banner-relax.jpg/.png, spa-gallery-room.jpg, spa-gallery-detail.jpg). Trang chủ dùng chúng cho banner, bảng giá, không gian, đội ngũ. | Xóa 11 tệp này (~6 MB). Mọi vị trí dùng 12 ảnh khác nhau còn lại. |
| Cao | Trang "Bảng giá" (page, có bộ lọc) bị **CPT `spa_package` chiếm cùng đường dẫn `/bang-gia/`** → trang có bộ lọc/bảng giá khó truy cập (theo cơ chế rewrite của WP; cần xác nhận trên site thật). | Archive gói đổi sang `/goi-lieu-trinh/`. Menu/footer trỏ `/bang-gia/`. |
| Cao | Bộ lọc nhóm trong bảng giá không hoạt động khi dịch vụ thuộc ≥2 nhóm: regex `/\\s+/` trong JS khớp ký tự `\` thay vì khoảng trắng. | Sửa thành `/\s+/`; thêm sự kiện `change`; chỉ tải JS ở đúng template. |
| Cao | Nội dung email/bản ghi đặt lịch chứa chữ `\n` thô (chuỗi `"\\n"`) → mọi trường dồn một dòng. | Sửa thành xuống dòng thật. |
| Cao | Hai thanh liên hệ nổi (header + footer) chồng nhau, trùng nút. | Một thanh duy nhất trong footer: Đặt lịch, Hotline, Zalo, Messenger, Bản đồ (chỉ hiện khi có dữ liệu). Mobile thành thanh đáy. |
| Trung bình | Ảnh dịch vụ gán sai: "Massage lưng và vai gáy" dùng ảnh cắt từ ảnh chăm sóc da; "Triệt lông tay/chân" dùng ảnh tư vấn. | Sửa map + migration `beauty_v71_fix_service_images` (chỉ thay nếu ảnh hiện tại vẫn là ảnh theme đã gán). |
| Trung bình | Trang Hình ảnh: chỉ 6 ảnh, bấm mở tab mới; shortcode **nhập ảnh vào Media Library ngay khi khách xem trang** (ghi dữ liệu trên request công khai). | 12 ảnh khác nhau, 6 nhóm có bộ lọc, lightbox đầy đủ; bỏ import khỏi shortcode. |
| Trung bình | Tự đặt giảm giá 10% cho 3 sản phẩm demo (giá khuyến mãi giả). | Bỏ đoạn tự giảm giá. (Site đã chạy bản cũ: dữ liệu đã ghi vẫn còn, hãy xóa giá KM trong WooCommerce nếu không có chương trình thật.) |
| Trung bình | Tiêu đề hero dùng `letter-spacing:-0.035em` → dễ chồng dấu tiếng Việt. | Đặt về 0, line-height 1.18. |
| Trung bình | Chỉ 3 slide, ảnh PNG 1–1.7 MB, không dừng khi hover/focus, không xét giảm chuyển động, không chỉnh được từ Admin. | 5 slide, ảnh JPG riêng, tự chuyển 4,5 s, dừng khi hover/focus/tab ẩn, nút Tạm dừng, vuốt trên mobile, tôn trọng `prefers-reduced-motion`, chỉnh trong Tùy biến. |
| Thấp | `get_page_by_title()` (lỗi thời từ WP 6.2); 3 ảnh trùng + alt rỗng ở khối đội ngũ (ẩn bằng CSS); liên kết footer `/shop` sai; version cache cứng. | Thay bằng helper; xóa khối ảnh; dùng `wc_get_page_permalink`; hằng `BEAUTY_SPA_VERSION`. |
| Thêm | Header chưa có tìm kiếm và giỏ hàng. | Nút tìm kiếm (mở panel, Esc đóng), giỏ hàng có số lượng (cập nhật qua fragments WooCommerce), thêm `search.php`. |

## 2. Đã kiểm tra thật (trong môi trường này)
- 24 tham chiếu tên ảnh trong PHP/JS/CSS → **0 tệp thiếu**; mọi ảnh trong theme **giải mã được** (Pillow); **0 tệp trùng**; không còn ảnh mồ côi.
- `node --check` cho `theme.js` và `pricing-filter.js`: hợp lệ.
- Kiểm tra cân bằng ngoặc/chuỗi cho 18 tệp PHP bằng script tự viết: 0 lỗi.

## 3. CHƯA kiểm tra được — bắt buộc test trên staging
- **Không có PHP/WordPress/trình duyệt** ở đây: chưa lint PHP thật, chưa chạy theme, chưa test slider, lightbox, tìm kiếm, giỏ hàng, form, responsive. Hãy chạy `php -l` cho mọi tệp và test trên staging **trước khi** đưa lên site thật.
- Chưa kiểm tra HTTP 200 qua hosting, mixed content, xung đột plugin cache/SEO.
- Chưa tự lưu trữ font (không có mạng): tiêu đề dùng Georgia (hỗ trợ tiếng Việt), nội dung dùng font hệ thống.
- Độ phân giải: 11 ảnh dịch vụ/album chỉ 504×504; hero hiển thị tối đa ~450 px nên ổn, nhưng nên thay bằng ảnh thật độ phân giải cao hơn.
- Tất cả ảnh là **ảnh minh họa**, không phải cơ sở thật.

## 4. Việc phải làm sau khi cài
1. Giao diện > Tùy biến > *Thông tin liên hệ Spa*: nhập hotline, email, địa chỉ, giờ, Zalo, Messenger, liên kết bản đồ (hiện đang là dữ liệu mẫu).
2. Giao diện > Tùy biến > *Banner trang chủ*: thay ảnh/nội dung từng slide.
3. **Cài đặt > Đường dẫn tĩnh > Lưu thay đổi** (đổi slug gói liệu trình). URL cũ `/bang-gia/<tên-gói>/` sẽ đổi thành `/goi-lieu-trinh/<tên-gói>/`; nếu đã có link cũ được chia sẻ, cần thêm redirect.
4. Xóa/ghi đè giá khuyến mãi demo và dữ liệu mẫu (dịch vụ, gói, sản phẩm, ưu đãi) bằng dữ liệu thật.

## 5. Chưa làm (các đợt tiếp theo)
Section ưu đãi có ngày hết hạn; thẻ dịch vụ đủ trường + 12 nhóm; trang chi tiết dịch vụ 14 mục; ≥15 sản phẩm WooCommerce thật + trang sản phẩm đầy đủ; mục Chăm sóc tại nhà; ≥8 bài viết; bảng giá 3 gói Basic/Premium/VIP; đánh giá & FAQ (CPT + schema); sidebar tin tức + mục lục; trang liên hệ có bản đồ; form đặt lịch nâng cấp (số người, trạng thái, chặn trùng giờ); SEO/schema/Open Graph; ~20 ảnh album (cần thêm ảnh); chính sách (bảo mật, giao hàng, đổi trả); dropdown điều hướng bàn phím đầy đủ.
