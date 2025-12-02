
# Clothing Shop (Colorful Edition)

- Giao diện màu sắc, có ảnh minh họa (hero, grid sản phẩm, thumbnail trong bảng).
- PHP + MySQL (Laragon), Auth + CRUD đầy đủ.

## Cài đặt
1) Start Apache + MySQL trong Laragon.
2) Import `schema.sql` vào MySQL (phpMyAdmin/HeidiSQL).
3) Copy thư mục `clothing_shop` vào `C:\laragon\www\`.
4) Truy cập `http://localhost/clothing_shop/`.

## Trang
- `index.php` — Hero + sản phẩm mới (grid hình ảnh)
- `products.php` — Tìm kiếm + danh sách (bảng có thumbnail) + xem chi tiết (hình lớn) + CRUD
- `login.php` / `register.php` — Auth
- `product_form.php` — Tạo/Sửa
- `product_delete.php` — Xóa
- `includes/*` — DB, Auth, Header/Footer
- `public/styles.css` — giao diện màu sắc
