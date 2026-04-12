USE naut_shop;

SET NAMES utf8mb4;

INSERT INTO products (
    name,
    name_en,
    description,
    description_en,
    price,
    category,
    category_en,
    image,
    quantity
) VALUES
('Áo thun cotton regular', 'Regular cotton t-shirt', 'Áo thun nam chất cotton mềm, form regular dễ mặc hằng ngày.', 'Soft cotton t-shirt with a regular fit for everyday wear.', 199000, 'Áo thun', 'T-Shirts', 'product-1.jpg', 50),
('Áo sơ mi Oxford trắng', 'White Oxford shirt', 'Áo sơ mi tay dài chất Oxford đứng phom, phù hợp đi làm và đi chơi.', 'Long-sleeve Oxford shirt with a structured fit, suitable for work and casual outings.', 349000, 'Áo sơ mi', 'Shirts', 'product-2.jpg', 30),
('Quần jean straight xanh nhạt', 'Light straight jeans', 'Quần jean ống suông vừa, chất denim co giãn nhẹ và dễ phối đồ.', 'Straight-fit jeans in lightly stretch denim that pair easily with everyday outfits.', 459000, 'Quần jean', 'Jeans', 'product-3.jpg', 25),
('Giày sneaker trắng tối giản', 'Minimal white sneakers', 'Mẫu sneaker basic đế êm, phù hợp mặc hằng ngày và dễ phối nhiều phong cách.', 'Minimal sneakers with cushioned soles, ideal for daily wear and versatile styling.', 699000, 'Giày', 'Shoes', 'product-4.jpg', 20),
('Túi đeo chéo canvas', 'Canvas crossbody bag', 'Túi đeo chéo gọn nhẹ với nhiều ngăn tiện dụng cho nhu cầu di chuyển mỗi ngày.', 'Compact canvas crossbody bag with practical compartments for daily essentials.', 289000, 'Phụ kiện', 'Accessories', 'product-5.jpg', 40),
('Váy midi hoa nhí', 'Floral midi dress', 'Váy midi dáng xòe nhẹ, họa tiết hoa nhí nữ tính và dễ mặc.', 'Feminine midi dress with a gentle flared silhouette and delicate floral print.', 399000, 'Váy', 'Dresses', 'product-6.jpg', 15),
('Áo hoodie nỉ basic', 'Basic fleece hoodie', 'Áo hoodie nỉ giữ ấm tốt, bề mặt mềm và phù hợp cho thời tiết mát.', 'Warm fleece hoodie with a soft finish, perfect for cool weather.', 499000, 'Áo hoodie', 'Hoodies', 'product-7.jpg', 35),
('Quần short kaki basic', 'Basic khaki shorts', 'Quần short kaki thoải mái, lên dáng gọn và phù hợp sinh hoạt hằng ngày.', 'Comfortable khaki shorts with a clean silhouette for daily wear.', 259000, 'Quần short', 'Shorts', 'product-8.jpg', 45),
('Áo polo pique đen', 'Black pique polo', 'Áo polo chất pique thoáng nhẹ, phù hợp môi trường công sở và casual.', 'Breathable pique polo suitable for both office and casual settings.', 279000, 'Áo polo', 'Polos', 'product-9.jpg', 28),
('Dép sandal quai ngang', 'Slide sandals', 'Dép sandal đế nhẹ, bền và thuận tiện cho các hoạt động thường ngày.', 'Lightweight and durable slide sandals for everyday activities.', 219000, 'Dép', 'Sandals', 'product-10.jpg', 60),
('Balo laptop chống sốc', 'Padded laptop backpack', 'Balo nhiều ngăn, có lớp chống sốc phù hợp laptop và đồ dùng cá nhân.', 'Multi-compartment backpack with padded protection for laptops and personal items.', 549000, 'Phụ kiện', 'Accessories', 'product-11.jpg', 18),
('Mũ lưỡi trai basic', 'Basic baseball cap', 'Mũ lưỡi trai unisex dễ phối, form gọn và phù hợp sử dụng hằng ngày.', 'Unisex baseball cap with a clean shape that works well for everyday wear.', 149000, 'Phụ kiện', 'Accessories', 'product-12.jpg', 70);
