<?php

use yii\helpers\Html;

$this->title = Yii::t('app', 'Home');
?>
<div class="row blog" style="padding-left: 15px; padding-right: 15px">
    <style>
            * {
                box-sizing:border-box
            }
            h2 {
                text-align: left;
            }
            .slideshow-container {
                max-width: 90%;
                position: relative;
                margin: auto;
            }
            .mySlides {
                display: none;
            }
            .text {
                color: #f2f2f2;
                font-size: 15px;
                padding: 8px 12px;
                position: absolute;
                bottom: 8px;
                width: 100%;
                text-align: center;
            }
            .dot {
                cursor:pointer;
                height: 10px;
                width: 10px;
                margin: 0 2px;
                background-color: #bbb;
                border-radius: 50%;
                display: inline-block;
                transition: background-color 0.6s ease;
            }
            .active, .dot:hover {
                background-color: #717171;
            }
            .fade {
                -webkit-animation-name: fade;
                -webkit-animation-duration: 5s;
                animation-name: fade;
                animation-duration: 10s;
            }

            @-webkit-keyframes fade {
                from {opacity: .4} 
                to {opacity: 1}
            }

            @keyframes fade {
                from {opacity: .4} 
                to {opacity: 1}
            }
    </style>

    <div class="slideshow-container">
        <div class="mySlides fade">
            <img src="<?= Yii::getAlias('@web') ?>/images/pic1.jpg" style="width:100%">
        </div>

        <div class="mySlides fade">
            <img src="<?= Yii::getAlias('@web') ?>/images/pic2.jpg" style="width:100%">
        </div>

        <div class="mySlides fade">
            <img src="<?= Yii::getAlias('@web') ?>/images/pic3.jpg" style="width:100%">
        </div>

        <div class="mySlides fade">
            <img src="<?= Yii::getAlias('@web') ?>/images/pic4.jpg" style="width:100%">
        </div>

        <div class="mySlides fade">
            <img src="<?= Yii::getAlias('@web') ?>/images/pic5.jpg" style="width:100%">
        </div>

    </div>
    <br>

    <div style="text-align:center">
        <span class="dot" onclick="currentSlide(0)"></span> 
        <span class="dot" onclick="currentSlide(1)"></span> 
        <span class="dot" onclick="currentSlide(2)"></span> 
        <span class="dot" onclick="currentSlide(3)"></span> 
        <span class="dot" onclick="currentSlide(4)"></span> 
    </div>

    <script>

        var slideIndex;
        function showSlides() {
            var i;
            var slides = document.getElementsByClassName("mySlides");
            var dots = document.getElementsByClassName("dot");
            for (i = 0; i < slides.length; i++) {
                slides[i].style.display = "none";  
            }
            for (i = 0; i < dots.length; i++) {
                dots[i].className = dots[i].className.replace(" active", "");
            }
            slides[slideIndex].style.display = "block";  
            dots[slideIndex].className += " active";
            slideIndex++;
            if (slideIndex > slides.length - 1) {
                slideIndex = 0
            }    
            setTimeout(showSlides, 10000);
        }
        showSlides(slideIndex = 0);
        function currentSlide(n) {
        showSlides(slideIndex = n);
        }
    </script>

    <div class="col-md-12">
        <h2 class="text-info" style="font-size: 24px;"><span><b>Chào mừng bạn đến với Greenhat Online Judge</b></span></h2>
            <p style="text-align: justify; font-size: 14px;">Greenhat Online Judge là hệ thống chấm điểm lập trình trực tuyến được xây dựng bởi các cựu thành viên đội tuyển OLP-ICPC - Khoa Công nghệ thông tin - Trường Đại học Kỹ thuật Hậu cần CAND, phát triển dựa trên nền tảng mã nguồn mở <a href="http://www.hustoj.org/">HUSTOJ.</a></p>
            <p style="text-align: justify; font-size: 14px;">Greenhat Online Judge được tạo ra với mục đích xây dựng một giải pháp chấm điểm lập trình trực tuyến hoàn toàn tự động, hỗ trợ việc luyện tập lập trình cho sinh viên. Các thành viên có thể sử dụng chức năng chấm điểm lập trình của hệ thống để đánh giá lời giải của mình đúng hay sai thông qua các bộ test đã được chuẩn bị từ trước. Qua đó, các sinh viên có thể thực hành và tương tác trực tiếp, đánh giá được lời giải của mình có đủ chính xác hay không. Đồng thời, sinh viên sẽ có thể tích lũy được rất nhiều kinh nghiệm và kiến thức về lập trình.</p>
            <p style="text-align: justify; font-size: 14px;">Hệ thống hỗ trợ việc tạo các bài tập để luyện tập, tổ chức các kỳ thi lập trình theo nhiều thể thức khác nhau (ICPC/OI-IOI/Single,...), đáp ứng đầy đủ các nhu cầu của giáo viên và sinh viên.</p>
            <p style="text-align: justify; font-size: 14px;">Nếu đây là lần đầu tiên sử dụng hệ thống, hãy <a href="/site/signup">đăng ký</a> tài khoản. Sau đó, thử bài tập <a href="p/1000">A+B Problems.</a></p>
    </div>
</div>
<div class="row blog" style="padding-left: 15px; padding-right: 15px;">
    <div class="blog-main">
        <div class="col-md-4">
            <h2 class="text-info" style="font-size: 24px;"><span><b><?=Yii::t('app','Notification')?></b></span></h2>
            <?php foreach ($news as $v): ?>
                <div class="blog-post">
                    <h4 class="blog-post-title" style="font-size: 18px; padding-left: 15px;"><?= Html::a(Html::encode($v['title']), ['/site/news', 'id' => $v['id']]) ?></h4>
                    <p class="blog-post-meta"><span class="glyphicon glyphicon-time"></span> <?= Yii::$app->formatter->asDate($v['created_at']) ?></p>
                </div>
            <?php endforeach; ?>
            
            <?= \yii\widgets\LinkPager::widget([
                'pagination' => $pages,
                ]); ?>
            <hr>
        </div>

        <div class="col-md-4">
            <h2 class="text-info" style="font-size: 24px;"><span><b><?=Yii::t('app','Recent Contest')?></b></span></h2>
            <div class="sidebar-module">
                <ol class="list-unstyled">
                    <?php foreach ($contests as $contest): ?>
                        <li>
                            <h4 class="blog-post-title" style="font-size: 18px; padding-left: 15px;"><?= Html::a(Html::encode($contest['title']), ['/contest/view', 'id' => $contest['id']]) ?></h4>
                        </li>
                    <?php endforeach; ?>
                </ol>
            </div>
            <hr>
        </div>
        <div class="col-md-4">
            <h2 class="text-info" style="font-size: 24px;"><span><b><?=Yii::t('app','Recent Discussion')?></b></span></h2>
            <div class="sidebar-module">
                <ol class="list-unstyled">
                    <?php foreach ($discusses as $discuss): ?>
                        <li class="index-discuss-item">
                            <div>
                                <?= Html::a(Html::encode($discuss['title']), ['/discuss/view', 'id' => $discuss['id']]) ?>
                            </div>
                            <small class="text-muted">
                                <span class="glyphicon glyphicon-user"></span>
                                <?= Html::a(Html::encode($discuss['nickname']), ['/user/view', 'id' => $discuss['username']]) ?>
                                    &nbsp;•&nbsp;
                                    <span class="glyphicon glyphicon-time"></span> <?= Yii::$app->formatter->asRelativeTime($discuss['created_at']) ?>
                                    &nbsp;•&nbsp;
                                    <?= Html::a(Html::encode($discuss['ptitle']), ['/problem/view', 'id' => $discuss['pid']]) ?>
                            </small>
                        </li>
                    <?php endforeach; ?>
                </ol>
            </div>
            <hr>
        </div>
    </div>
</div>