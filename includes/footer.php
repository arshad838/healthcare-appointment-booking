<?php
// includes/footer.php
// Common HTML footer and scripts loader.
?>
            </main>
        </div>
    </div>

    <?php if (!isset($hide_nav) || !$hide_nav): ?>
        <?php if (!isset($_SESSION['user_id'])): ?>
            <!-- Footer for Public Pages -->
            <footer class="bg-dark text-white pt-5 pb-3 border-top border-secondary">
                <div class="container">
                    <div class="row g-4">
                        <div class="col-lg-4 col-md-6">
                            <h5 class="font-outfit fw-bold text-white mb-3">
                                <i class="fa-solid fa-heart-pulse text-medical-blue me-2"></i>CareSync
                            </h5>
                            <p class="text-muted text-justify">
                                CareSync is a comprehensive clinic scheduling platform linking patients and certified practitioners. Our mission is to streamline healthcare access, reduce waiting times, and maximize scheduling efficiency.
                            </p>
                            <div class="d-flex gap-3 fs-5 mt-3">
                                <a href="#" class="text-white hover-medical-blue"><i class="fa-brands fa-facebook"></i></a>
                                <a href="#" class="text-white hover-medical-blue"><i class="fa-brands fa-twitter"></i></a>
                                <a href="#" class="text-white hover-medical-blue"><i class="fa-brands fa-linkedin"></i></a>
                                <a href="#" class="text-white hover-medical-blue"><i class="fa-brands fa-instagram"></i></a>
                            </div>
                        </div>
                        <div class="col-lg-2 col-md-6">
                            <h6 class="text-uppercase fw-bold text-white mb-3">Quick Links</h6>
                            <ul class="list-unstyled">
                                <li class="mb-2"><a href="<?php echo BASE_URL; ?>" class="text-muted text-decoration-none">Home</a></li>
                                <li class="mb-2"><a href="<?php echo BASE_URL; ?>#how-it-works" class="text-muted text-decoration-none">How it Works</a></li>
                                <li class="mb-2"><a href="<?php echo BASE_URL; ?>#departments" class="text-muted text-decoration-none">Specialties</a></li>
                                <li class="mb-2"><a href="<?php echo BASE_URL; ?>auth/login.php" class="text-muted text-decoration-none">Portal Login</a></li>
                            </ul>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <h6 class="text-uppercase fw-bold text-white mb-3">For Students (DevOps)</h6>
                            <p class="text-muted">
                                This project is structured for university practical assignments showcasing Docker Compose, Kubernetes Orchestration, CI/CD with Jenkins, and PHP deployment patterns.
                            </p>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <h6 class="text-uppercase fw-bold text-white mb-3">Contact CareSync</h6>
                            <ul class="list-unstyled text-muted">
                                <li class="mb-2"><i class="fa-solid fa-phone me-2 text-medical-blue"></i>+1 (555) 019-9000</li>
                                <li class="mb-2"><i class="fa-solid fa-envelope me-2 text-medical-blue"></i>support@caresync.com</li>
                                <li class="mb-2"><i class="fa-solid fa-location-dot me-2 text-medical-blue"></i>100 DevOps Blvd, AWS Cloud</li>
                            </ul>
                        </div>
                    </div>
                    <hr class="border-secondary my-4">
                    <div class="row align-items-center">
                        <div class="col-md-6 text-center text-md-start">
                            <p class="mb-0 text-muted">&copy; 2026 CareSync Systems Inc. All rights reserved.</p>
                        </div>
                        <div class="col-md-6 text-center text-md-end mt-2 mt-md-0">
                            <p class="mb-0 text-muted fs-8">Designed for DevOps University Project</p>
                        </div>
                    </div>
                </div>
            </footer>
        <?php endif; ?>
    <?php endif; ?>

    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery CDN -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <!-- Chart.js for Admin Analytics -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Custom Application Javascript -->
    <script src="<?php echo BASE_URL; ?>assets/js/script.js"></script>
</body>
</html>
