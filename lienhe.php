<?php
$page_title = "Liên hệ";
require_once('includes/header.php'); // Dùng header chung
?>
<link rel="stylesheet" href="css/lienhe.css"> <style>
    /* CSS cho thông báo gửi liên hệ */
    .contact-message { padding: 10px; margin: 15px 0; border-radius: 5px; text-align: center; }
    .contact-message.success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    .contact-message.error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
</style>

<section class="contact-section">
    <div class="container">
        <div class="section-title">
            <h2>Liên hệ với chúng tôi</h2>
            <p>Gửi câu hỏi hoặc góp ý của bạn.</p>
             <?php if (isset($_SESSION['contact_message'])): ?>
                <p class="contact-message <?php echo isset($_SESSION['contact_error']) ? 'error' : 'success'; ?>">
                    <?php echo $_SESSION['contact_message']; ?>
                </p>
                <?php unset($_SESSION['contact_message'], $_SESSION['contact_error']); ?>
            <?php endif; ?>
        </div>
        <div class="contact-wrapper">
            <div class="contact-form">
                <h3>Gửi tin nhắn</h3>
                <form action="actions/handle_contact.php" method="POST">
                    <div class="form-group">
                        <label for="name">Họ và tên</label>
                        <input type="text" id="name" name="name" placeholder="Nhập họ và tên" required value="<?php echo htmlspecialchars($current_user['full_name'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" placeholder="Nhập địa chỉ email" required value="<?php echo htmlspecialchars($current_user['email'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="subject">Chủ đề</label>
                        <input type="text" id="subject" name="subject" placeholder="Nhập chủ đề" required>
                    </div>
                    <div class="form-group">
                        <label for="message">Nội dung</label>
                        <textarea id="message" name="message" rows="6" placeholder="Nội dung tin nhắn..." required></textarea>
                    </div>
                    <button type="submit" class="submit-btn">Gửi đi</button>
                </form>
            </div>

           <div class="contact-info">
                <h3>Thông tin liên lạc</h3>
                <div class="map-container">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d7652.099744278562!2d108.24938660797174!3d15.974115649664068!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3142108997dc971f%3A0x1295cb3d313469c9!2zVHLGsOG7nW5nIMSQ4bqhaSBo4buNYyBDw7RuZyBuZ2jhu4cgVGjDtG5nIHRpbiB2w6AgVHJ1eeG7gW4gdGjDtG5nIFZp4buHdCAtIEjDoG4sIMSQ4bqhaSBo4buNYyDEkMOgIE7hurVuZw!5e1!3m2!1svi!2s!4v1761166546499!5m2!1svi!2s" width="100%" height="250" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
            </div>
        </div>
    </div>
</section>

<?php require_once('includes/footer.php'); ?>