<div class="row blog" style="font-size: 14px;">
<h2 class="text-info" style="font-size: 24px;"><b>Trình chấm chuẩn OI</b></h2>
<hr>
<h3 class="text-info" style="font-size: 20px;"><b>Kích hoạt chế độ OI</b></h3>
<p>1. Trong phần System Management, mục Setting, chọn "Yes" trong tùy chọn "OI Mode" </p>
<p>2. Sau khi khởi động hệ thống chấm, thêm tùy chọn <code>-o</code> để kích hoạt chế độ OI. </p>
<p>   Cụ thể hơn, truy cập vào thư mục <code>/home/judge/onlinejudge/judge</code> và chạy lệnh <code>sudo ./dispatcher -o</code>.
Nếu tiến trình đang chạy, cần ngắt tiến trình trước bằng lệnh <code>sudo pkill -9 dispatcher</code>, sau đó chạy lại lệnh <code>sudo ./dispatcher -o</code> để kích hoạt lại chế độ OI. </p>
<hr>

<h3 class="text-info" style="font-size: 20px;"><b>Cấu hình Subtask</b></h3>
<p>Ví dụ về file cấu hình Subtask như sau: </p>
<pre>
data[0-10] 10
data[11-13] 10
[14-20] 55
s[21] 5
test[] 20
</pre>
<p>Trong file cấu hình, mỗi dòng đại diện cho một subtask. Tiền tố nằm trước dấu ngoặc vuông (ứng với tiền tố của testcase, có thể không có tiền tố). Trong ngoặc vuông là số thứ tự của testcase (số thứ tự là một số nguyên không âm) và có một khoảng trắng sau dấu ngoặc vuông. </p>
<p>Với ví dụ trên, có 5 subtask:</p>
<ul>
  <li>Subtask 1: testcase là <code>data0.in, data1.in, ..., data10.in</code>, tổng điểm là 10</li>
  <li>Subtask 2: testcase là <code>data11.in, data12.in, data13.in</code>, tổng điểm là 10</li>
  <li>Subtask 3: testcase là <code>14.in, 15.in, ..., 20.in</code>，tổng điểm là 55</li>
  <li>Subtask 4: testcase là <code>s21.in</code>, tổng điểm là 5</li>
  <li>Subtask 5: testcase là <code>test.in</code>, tổng điểm là 20</li>
</ul>
<p>Một số lưu ý: </p>
<ol>
<li>Phần mở rộng của file input là <code>.in</code>, file output có thể là <code>.out</code> hoặc <code>.ans</code>. Trong file cấu hình của subtask, không cần i cấu hình tên hậu tố.</li>
<li>Với mỗi dòng cấu hình suubtask bắt buộc phải có dấu ngoặc vuông <code>[ ]</code>. Có thể điến một số, một khoảng số hoặc để trống.</li>
<li>Nếu không có số trong dấu ngoặc vuông, thì hệ thống sẽ nhận dạng subtask trùng với tiền tố trước dấu ngoặc vuông.</li>
<li>Khi hệ thống nhận được file cấu hình, nó sẽ chỉ đánh giá các testcase xuất hiện trong file cấu hình. Nếu testcase được mô tả trong file cấu hình nhiều hơn điểm thực tế, kết quả sẽ trả về là <code>No Test Data</code>.</li>
<li>File cấu hình subtask được lưu trong cùng thư mục với testcase, tên file là <code>config</code>.</li>
</ol>