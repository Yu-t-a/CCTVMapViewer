FROM php:8.2-apache

# เปิดใช้งาน mod_rewrite (หากต้องใช้)
RUN a2enmod rewrite

# คัดลอกไฟล์จากโฮสต์เข้าไปใน container (จะถูก override ด้วย volume ตอน run จริง)
COPY src/ /var/www/html/

# เปลี่ยน permission หากจำเป็น
RUN chown -R www-data:www-data /var/www/html
