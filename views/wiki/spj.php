<div class="row blog" style="font-size: 14px;">
<h2 class="text-info" style="font-size: 24px;"><span><b>Special Judge</b></span></h2>
<hr>
<p>Khi giải quyết các bài toán thực tế, sẽ có thể phát sinh một số vấn đề như: 
<ul>
    <li>Bài toán có nhiều cách giải quyết khác nhau, do đó không thể có một file kết quả đầu ra chính xác cho mọi trường hợp để đánh giá cách giải quyết đưa ra có chính xác hay không.</li>
    <li>Kết quả đưa ra là một số thập phân, và sai số cho phép trong một khoảng nhất định.</li>
</ul>

Để gỉải quyết các vấn đề trên, ta sử dụng một công cụ đặc biệt, gọi là Special Judge (viết tắt là SPJ). Khi tạo các bài tập dạng như trên, ngơời ra đề cần kèm theo một đoạn mã nguồn để phục vụ việc so sánh đáp án. SPJ sẽ biên dịch mã nguồn thành file thực thi, nhận dữ liệu đầu vào và tiến hành so sánh kết quả với các tiêu chí của đáp án, xem xét tính chính xác của đáp án và đưa ra kết quả phù hợp.</p>

<p>SPJ được viết bằng C/C++, giá trị trả về của nó sẽ xác định kết quả. Trả về thành công (0) có nghĩa là AC và các giá trị khác 0 có nghĩa là WA.
</p>
<p>Tham số biên dịch của SPJ là: <code>g++ -fno-asm -std=c++17 -O2</code>, đã hỗ trợ C++17 và bật tối ưu hóa O2.</p>
<p>Cần đảm bảo độ chính xác của mã nguồn trước khi nạp vào SPJ. <b>Không truy vấn các chức năng hệ thống không liên quan đến câu hỏi.</b> Hệ thống sẽ không đưa ra phản hồi khi SPJ biên dịch hoặc chạy lỗi.</p>

<p>Dưới đây là 2 cách để viết SPJ.</p>
<hr>
<h3 class="text-info" style="font-size: 20px;"><b>Cách 1</b></h3>
<div class="pre"><p>#include &lt;stdio.h&gt
#define AC 0
#define WA 1
const double eps = 1e-4;
int main(int argc,char *args[])
{
    FILE * f_in = fopen(args[1],"r");
    FILE * f_user = fopen(args[2],"r");
    FILE * f_out = fopen(args[3],"r");
    int ret = AC;
    int t;
    double a, x;
    fscanf(f_in, “%d”, &t); 
    while (t--) {
        fscanf(f_out, “%lf”, &a);
        fscanf(f_user, “%lf”, &x); 
        if(fabs(a-x) > eps) {
            ret = WA;
            std::cerr << "The answer is wrong: result = " << a << ", output = "<< x << std::endl;
            break;
        }
    }
    fclose(f_in);
    fclose(f_out);
    fclose(f_user);
    return ret;
}
</p></div>

<hr>

<h3 class="text-info" style="font-size: 20px;"><b>Cách 2</b></h3>

<p>Hệ thống sử dụng SPJ tương tự như Codeforces, cụ thể là thư viện testlib.h</p>

<p>Tham khảo thêm tại: 
    <a href="https://github.com/MikeMirzayanov/testlib">
        https://github.com/MikeMirzayanov/testlib
    </a>
</p>

<div class="pre"><p>#include "testlib.h"
int main(int argc, char* argv[]) 
{
    registerTestlibCmd(argc, argv);
    while(!ans.eof()){
        double pans = ouf.readDouble();
        double jans = ans.readDouble();
        ans.readEoln();
        if (fabs(pans - jans)>0.01)
            quitf(_wa, "The answer is wrong: expected = %f, found = %f", jans, pans);
    }
    quitf(_ok, "The answer is correct.");
    return 0;
}
</p></div>

<hr>
<h3 class="text-info" style="font-size: 20px;"><b>Biên dịch SPJ</b></h3>
<p>
Sử dụng terminal để biên dịch SPJ:   <code>./spj in.txt out.txt ans.txt </code> </p>
<p>Trong số đó, in.txt out.txt ans.txt là các file dữ liệu vào, ra và đáp án được lưu trong cùng một thư mục.</p>
<p>Chương trình sẽ tự động trả về kết quả.</p>
</div>