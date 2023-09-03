<p align="center">
    <a href="https://greenhat1998.github.io" target="_blank">
        <img src="web/images/logo.png" height="150px">
    </a>
    <h1 align="center">Greenhat Online Judge</h1>
    <br>
</p>

Tiếng Việt | [English](README.en.md)

<p>Greenhat Online Judge là hệ thống chấm điểm lập trình trực tuyến được xây dựng bởi các cựu thành viên đội tuyển OLP-ICPC - Trường Đại học Kỹ thuật Hậu cần CAND, phát triển dựa trên nền tảng mã nguồn mở <a href="https://github.com/zhblue/hustoj">HUSTOJ.</a></p>
<p>Greenhat Online Judge được tạo ra với mục đích xây dựng một giải pháp chấm điểm lập trình trực tuyến hoàn toàn tự động, hỗ trợ việc luyện tập lập trình cho sinh viên. Các thành viên có thể sử dụng chức năng chấm điểm lập trình của hệ thống để đánh giá lời giải của mình đúng hay sai thông qua các bộ test đã được chuẩn bị từ trước. Qua đó, các sinh viên có thể thực hành và tương tác trực tiếp, đánh giá được lời giải của mình có đủ chính xác hay không. Đồng thời, sinh viên sẽ có thể tích lũy được rất nhiều kinh nghiệm và kiến thức về lập trình.</p>
Mã nguồn chương trình (viết bằng ngôn ngữ C, C++, Java, Python,...) sẽ được hệ thống tự động biên dịch thành chương trình để kiểm tra tính chính xác thông qua các bộ dữ liệu có sẵn.</p>

# Một số tính năng nổi bật
----------

- [x] OI Mode - Hỗ trợ chấm theo chế độ OI/IOI, có thiết lập cấu hình theo từng subtask.
- [x] Scrollboard - Hiệu ứng cuộn bảng điểm, được sử dụng trong lúc công bố kết quả, áp dụng đối với cuộc thi Offline.
- [x] Groups - Người dùng có thể tạo nhóm và tổ chức các cuộc thi trong nhóm, giao bài tập về nhà.
- [x] Testlib - Hỗ trợ sử dụng testlib.h để viết các trình chấm đặc biệt (Special judge).
- [x] Offline/Online Contest - Phân quyền tài khoản tham gia theo thể thức cuộc thi. Tài khoản tham gia thi đấu không thể tự sửa đổi thông tin cá nhân.
- [x] Print service - Cung cấp chức năng in mã nguồn cho các cuộc thi Ofline.
- [x] Print problem - Trích xuất thông tin bài tập dưới dạng PDF hiển thị trên trình duyệt.
- [x] Download submisstion - Tải xuống bài nộp của thí sinh, phục vụ kiểm tra trùng lặp mã nguồn.
- [x] Notification - Trong cuộc thi, khi một thông báo được đưa ra, một cửa sổ popup được hiển thị cho tất cả người dùng trực tuyến để thông báo rằng có một thông báo mới.
- [x] Home - Trang chủ hiển thị tin tức, các thông báo khác nhau,...
- [x] Hỗ trợ đa ngôn ngữ - Hiện đang hỗ trợ C, C ++, Java, Python3, Pascal
- [x] Tags - Gắn thẻ bài tập để dễ dàng phân loại
- [x] Polygon System - Hệ thống Polygon giúp hỗ trợ tạo bài tập, tạo dữ liệu, kiểm tra tính chính xác của mã nguồn, v.v.

# Hướng dẫn cài đặt hệ thống
----------

Hệ thống được xây dựng và vận hành trên môi trường Linux (Ubuntu/CentOS), không thể chạy trực tiếp trên các máy chủ sử dụng hệ điều hành Windows. 
Nếu muốn triển khai trên hệ điều hành Windows, vui lòng sử dụng các phần mềm ảo hóa (VMware Workstation/VirtualBox) hoặc sử dụng WSL.

Hướng dẫn dưới đây được xây dựng mô phỏng trên Ubuntu 20.04 LTS. Đối với các phiên bản Linux khác làm tương tự.

## Cách 1: Cài đặt thủ công
### 1. Cài đặt môi trường LNMP và các gói phần mềm cần thiết
```
sudo apt update
sudo apt install nginx mysql-server php-fpm php-mysql php-common php-gd php-zip php-mbstring php-xml 
sudo apt install libmysqlclient-dev libmysql++-dev git make gcc g++ fp-compiler openjdk-11-jdk 
``` 

### 2. Tạo user để cấp quyền biên dịch và quản lý nộp bài
```
sudo /usr/sbin/useradd -m -u 1536 judge
cd /home/judge
```

### 3. Tải về phiên bản mới nhất của hệ thống
```
git clone htttps://github.com/VietThienTran/onlinejudge
```
### 4. Cấu hình Nginx kết nối đến judge
Cấu hình lại file <code>/etc/nginx/sites-enabled/default</code> để trỏ đường dẫn mặc định về <code>/home/judge/onlinejudge</code>
```
server {
        listen 80 default_server;
        listen [::]:80 default_server;
        root /home/judge/onlinejudge/web;
        index index.php;
        server_name _;
        client_max_body_size    128M;
        location / {
                try_files \$uri \$uri/ /index.php?\$args;
        }
        location ~ \.php$ {
                include snippets/fastcgi-php.conf;
                fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        }
}
```
Khởi động lại Nginx và php để hệ thống ghi nhận thay đổi
```
sudo systemctl restart nginx
sudo systemctl restart php7.4-fpm
```
### 5. Tạo database
```
$ mysql -u root -p
mysql> create database onlinejudge;
mysql> quit;
```
Nếu sử dụng tài khoản khác thì tiến hành thay đổi $DBUSER và $DBPASS trong file <code>/onlinejudge/config/db.php</code>, <code>/onlinejudge/judge/src/config.ini</code>, <code>/onlinejudge/polygon/src/config.ini</code>

### 6. Biên dịch dispatcher và polygon
```
cd /home/judge/onlinejudge
sudo echo -e "yes" "\n" "admin" "\n" "123456" "\n" "vietthienbqn1998@gmail.com" | ./yii install
cd /home/judge/onlinejudge/judge
sudo make
sudo ./dispatcher
cd /home/judge/onlinejudge/polygon
sudo make
sudo ./polygon
```
Đến đây, các bước cài đặt đã hoàn tất. Truy cập [http://localhost](http://localhost) để kiểm tra.
