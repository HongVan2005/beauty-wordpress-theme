# Beauty V9 — 4 bản sửa

File mới: inc/v9-fixes.php, template-parts/package-card.php, assets/css/beauty-v9.css, assets/js/beauty-albums.js, assets/images/products/sq-*.jpg (18 ảnh vuông).
File sửa: functions.php (thêm 1 dòng require), inc/v8.php (2 hàm ảnh sản phẩm), front-page.php + archive-spa_package.php (dùng thẻ combo mới).

Cài: Giao diện > Thêm mới > Tải lên beauty-theme-v9.zip (ghi đè) hoặc chép file qua FTP. Xóa cache plugin/CDN, Ctrl+F5.
Đổi ảnh sản phẩm: sửa sản phẩm > ô "Ảnh sản phẩm (URL)" dán link https.
Đổi ảnh/quyền lợi combo: sửa gói > ô "Combo: ảnh & quyền lợi hiển thị".
Dọn album: Admin > Album ảnh > xóa ảnh trùng (code đã tự loại trùng và giới hạn 16 ảnh khi hiển thị).

## V9.1
- Ảnh thật cho album: chép ảnh vào wp-content/uploads/beauty-album/<ten-album>/ (cham-soc-da, massage, goi-dau, khong-gian, san-pham, co-the) -> tự hiện, tối đa 48 ảnh.
- Ảnh thật cho sản phẩm: chép vào wp-content/uploads/beauty-products/ đặt tên sua-rua-mat.jpg, kem-chong-nang -> chong-nang.jpg, serum.jpg... (xem tên trong assets/images/products/sq-*.jpg). Hoặc đặt Ảnh đại diện trong trang sửa sản phẩm.
- Danh mục dịch vụ: shortcode [beauty_service_categories], tự thay khối ảnh lỗi ở trang Giới thiệu.
- Công cụ > Chẩn đoán ảnh Beauty: cho biết ảnh nào thiếu / lỗi https.


## V9.1 — Bổ sung danh mục hình ảnh trên trang chủ
- Trang chủ có thêm bộ lọc danh mục hình ảnh và lưới ảnh 4 cột (tự chuyển 3/2/1 cột theo kích thước màn hình).
- Thêm khu vực Danh mục dịch vụ dạng tab, khung mô tả/ảnh và thẻ dịch vụ nổi bật.
- Shortcode có thể dùng riêng: `[beauty_image_categories]` và `[beauty_service_categories]`.
- Ảnh lấy từ album đã cấu hình; nếu chưa có ảnh thật, theme dùng ảnh minh họa có sẵn.
