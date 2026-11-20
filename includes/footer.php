//University Management System (footer.php)
//Devon Hight
//CST499-Software Engineering Capstone
//Instructor- Charmelia Butler
//04/13/2026


<?php // includes/footer.php ?>
<footer class="site-footer mt-auto py-5">
  <div class="container">
    <div class="row g-4">
      <div class="col-md-4">
        <h5 class="footer-brand"><?= SITE_NAME ?></h5>
        <p class="list-unstyled small">Serving Tennessee students with quality education, innovative programs, and a commitment to student success since 1971.</p>
      </div>
      <div class="col-md-2">
        <h6 class="footer-heading">Academics</h6>
        <ul class="list-unstyled small">
          <li><a href="<?= SITE_URL ?>/degrees.php">Degrees &amp; Programs</a></li>
          <li><a href="<?= SITE_URL ?>/courses.php">Course Catalog</a></li>
          <li><a href="<?= SITE_URL ?>/student/register.php">Registration</a></li>
        </ul>
      </div>
      <div class="col-md-2">
        <h6 class="footer-heading">Student Life</h6>
        <ul class="list-unstyled small">
          <li><a href="<?= SITE_URL ?>/events.php">Events</a></li>
          <li><a href="#">Financial Aid</a></li>
          <li><a href="#">Career Services</a></li>
        </ul>
      </div>
      <div class="col-md-4">
        <h6 class="footer-heading">Contact Us</h6>
        <p class="small list-unstyled">
          <i class="bi bi-geo-alt-fill me-1"></i> 1480 Nashville Pike, Gallatin, TN 37066<br>
          <i class="bi bi-telephone-fill me-1"></i> (615) 452-8600<br>
          <i class="bi bi-envelope-fill me-1"></i> info@volstate.edu
        </p>
      </div>
    </div>
    <hr class="footer-divider">
    <p class="text-center list-unstyled small mb-0">
      &copy; <?= CURRENT_YEAR ?> <?= SITE_NAME ?>. All rights reserved. &nbsp;|&nbsp;
      This site is a <strong>class project</strong> for educational purposes only.
    </p>
  </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= SITE_URL ?>/js/main.js"></script>
</body>
</html>
