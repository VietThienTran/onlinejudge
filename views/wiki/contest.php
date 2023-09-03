<div class="table-responsive">

    <h3>Phương thức tính điểm</h3>
    <table class="table table-bordered">
        <thead>
        <tr>
            <th style="min-width: 130px">Loại hình</th>
            <th>Cách tính điểm</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <th>Single</th>
            <td style="text-align:justify;">
                Với mỗi bài, sẽ có một số điểm khởi đầu (N=500). Số điểm này sẽ giảm dần theo thời gian (2 điểm/1 phút). Nếu giải đúng, bạn sẽ nhận được 50% số điểm. Nếu là người giải đúng đầu tiên, bạn sẽ nhận thêm 10% số điểm của bài đó. Với mỗi lần nộp bài sai, bạn sẽ bị trừ 50 điểm cho bài đó.
            </td>
        </tr>
        <tr>
            <th>ICPC</th>
            <td style="text-align:justify;">Thời gian giải cho mỗi bài được tính từ lúc bắt đầu cuộc thi cho đến khi bài đó được giải đúng. Với mỗi lần nộp sai, hệ thống sẽ cộng thêm 20 phút vào thời gian làm bài đó. Nếu kết thúc cuộc thi mà vẫn không giải được thì bài đó sẽ được bỏ qua. Hệ thống sẽ xếp hạng dựa trên số bài làm được của mỗi đội. Đối với các đội có số bài giải được bằng nhau, hệ thống sẽ xếp hạng dựa trên tổng thời gian của mỗi đội.</td>
        </tr>
        <tr>
            <th>Homework</th>
            <td style="text-align:justify;">Hệ thống sẽ xếp hạng dựa trên số bài giải được. Không có thời gian cộng thêm khi giải sai. Bạn có thể xem lỗi của bài nộp.</td>
        </tr>
        <tr>
            <th>OI</th>
            <td style="text-align:justify;">Tất cả các testcase sẽ đều được kiểm tra và điểm sẽ được tính tương ứng theo file cấu hình của bài tập đó. Trước khi kết thúc cuộc thi, thí sinh không thể biết được trạng thái nộp bài của chính mình.</td>
        </tr>
        <tr>
            <th>IOI</th>
            <td style="text-align:justify;">Tất cả các testcase sẽ đều được kiểm tra và điểm sẽ được tính tương ứng theo file cấu hình của bài tập đó.</td>
        </tr>
        </tbody>
    </table>

    <hr>
    <h3>Những điểm khác biệt giữa cuộc thi Online và Offline</h3>
    <p style="text-align:justify;">Cuộc thi Offline là cuộc thi mang tính nội bộ, tham gia thi tại vị trí cố định, được xây dựng với mục đích tổ chức một cuộc thi trực tiếp. Một số điểm khác biệt giữa 2 hình thức này đó là: </p>
    <ul>
        <li style="text-align:justify;">Trong cuộc thi Offline, sẽ có một đường link in mã nguồn, dùng để cung cấp chức năng dịch vụ in mã nguồn cho thí sinh. Tính năng này không có trong các cuộc thi Online.</li>

        <li style="text-align:justify;">Đối với cuộc thi Offline, tài khoản tham gia thi chỉ có thể được cấp bởi quản trị viên hệ thống và các tài khoản cần được tạo theo đợt cho cuộc thi. Người dùng không thể đăng ký tham gia cuộc thi. Đối với các cuộc thi Online, người dùng có thể tự đăng ký trước khi kết thúc cuộc thi.</li>

        <li style="text-align:justify;">Đối với cuộc thi Offline, các tài khoản được đánh dấu sao sẽ không được xếp hạng</li>

        <li style="text-align:justify;">Các tài khoản được tạo theo đợt trong các cuộc thi Offline sẽ bị cấm sửa đổi thông tin cá nhân.</li>
             
        <li style="text-align:justify;">Cuộc thi Offline có hiệu ứng hiển thị bảng xếp hạng sau khi kết thúc cuộc thi (tính năng tham khảo từ <a href="https://www.domjudge.org/"> Domjudge</a>). Thứ hạng và kết quả nộp bài sẽ dần dần được tiết lộ khi cuộn từ dưới lên. Tính năng này không có sẵn trong các cuộc thi Online.</li>
    </ul>

    <hr>
    <h3>Tiêu chí xếp hạng</h3>
    <p>
        Sau khi tham gia mỗi cuộc thi, thí sinh sẽ nhận được một số điểm nhất định, và thứ hạng sẽ được xác định theo số điểm mà bạn đạt được. Xem bảng xếp hạng tại <?= \yii\helpers\Html::a('Rating.', ['/rating'], ['target' => '_blank']) ?>
    </p>

    <p>
        Nếu bạn đã tham gia cuộc thi nhưng chưa giải bài tập nào thì điểm của cuộc thi đó sẽ không được tính.
    </p>
    <hr>

    <h3>Các bậc xếp hạng</h3>
    <p>Điểm ban đầu dành cho lần tham gia đầu tiên: 1149</p>
    <table class="table table-bordered">
        <thead>
        <tr>
            <th style="min-width: 130px">Level</th>
            <th>Points</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <th>Bronze</th>
            <th>Between 0 and 1149</th>
        </tr>
        <tr>
            <th>Silver</th>
            <th>Between 1150 and 1399</th>
        </tr>
        <tr>
            <th>Gold</th>
            <th>Between 1400 and 1649</th>
        </tr>
        <tr>
            <th>Platinum</th>
            <th>Between 1650 and 1899</th>
        </tr>
        <tr>
            <th>Diamond</th>
            <th>Between 1900 and 2149</th>
        </tr>
        <tr>
            <th>Challenger</th>
            <th>Between 2150 and 2399</th>
        </tr>
        <tr>
            <th>Master</th>
            <th>2400 and above</th>
        </tr>
        </tbody>
    </table>
    <hr>
    <h3>Cách tính điểm sau khi tham gia cuộc thi</h3>
    <p>Hệ thống sử dụng thuật toán đánh giá điểm Elo, tham khảo tại: 
        <a href="https://en.wikipedia.org/wiki/Elo_rating_system" target="_blank">
            https://en.wikipedia.org/wiki/Elo_rating_system
        </a>
    </p>
</div>