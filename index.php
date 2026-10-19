<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="theme-color" content="#2E7D52">
  <meta name="description" content="KAK Management System - BPJS Ketenagakerjaan">
  <title>KAK Management System</title>
  <!-- Google Fonts: Inter -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <!-- Google Charts -->
  <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
  <script>
    google.charts.load('current', {'packages':['corechart']});
  </script>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
  <div id="app"></div>
  
  <!-- LANDING PAGE (DITAMPILKAN SEBELUM LOGIN) -->
  <div id="landingPage" class="page active">
    <!-- NAVBAR -->
    <header class="landing-header">
      <nav class="landing-navbar">
        <div class="landing-nav-brand">
          <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/c/c3/BPJS_Ketenagakerjaan_logo.svg/3840px-BPJS_Ketenagakerjaan_logo.svg.png" alt="BPJS Ketenagakerjaan Logo" class="landing-bpjs-logo">
          <div class="landing-brand-divider"></div>
          <div class="landing-brand-text">
            <div class="brand-title-group">
              <svg class="brand-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14 2 14 8 20 8"></polyline>
                <path d="M9 15l2 2 4-4"></path>
              </svg>
              <span class="brand-name">KAK Management System</span>
            </div>
            <span class="brand-subtext">Deputi Pengadaan</span>
          </div>
        </div>

        <!-- Desktop Menu -->
        <div class="landing-nav-menu">
          <a href="#beranda" class="landing-nav-link">Beranda</a>
          <a href="#tujuan" class="landing-nav-link">Tujuan</a>
          <a href="#ruang-lingkup" class="landing-nav-link">Ruang Lingkup</a>
        </div>

        <!-- Right Side Button -->
        <div class="landing-nav-actions">
          <button type="button" class="btn-landing-login" onclick="showPage('login')">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
              <polyline points="10 17 15 12 10 7"></polyline>
              <line x1="15" y1="12" x2="3" y2="12"></line>
            </svg>
            <span>Login ke Sistem</span>
          </button>
          
          <!-- Hamburger Button (Mobile) -->
          <button type="button" class="hamburger-toggle" onclick="toggleMobileMenu()" aria-label="Toggle Menu">
            <svg class="hamburger-icon-open" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <line x1="3" y1="12" x2="21" y2="12"></line>
              <line x1="3" y1="6" x2="21" y2="6"></line>
              <line x1="3" y1="18" x2="21" y2="18"></line>
            </svg>
            <svg class="hamburger-icon-close" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none;">
              <line x1="18" y1="6" x2="6" y2="18"></line>
              <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
          </button>
        </div>
      </nav>

      <!-- Mobile Hamburger Dropdown Menu -->
      <div id="mobileMenu" class="mobile-menu-dropdown">
        <a href="#beranda" onclick="closeMobileMenu()" class="mobile-nav-link">Beranda</a>
        <a href="#tujuan" onclick="closeMobileMenu()" class="mobile-nav-link">Tujuan</a>
        <a href="#ruang-lingkup" onclick="closeMobileMenu()" class="mobile-nav-link">Ruang Lingkup</a>
        <button type="button" class="mobile-login-btn" onclick="showPage('login'); closeMobileMenu();">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
            <polyline points="10 17 15 12 10 7"></polyline>
            <line x1="15" y1="12" x2="3" y2="12"></line>
          </svg>
          <span>Login ke Sistem</span>
        </button>
      </div>
    </header>

    <main class="landing-content">
      <!-- 3. HERO SECTION / BERANDA -->
      <section id="beranda" class="hero-section">
        <div class="landing-container hero-container">
          <div class="hero-text-col">
            <div class="hero-badge">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"></circle>
                <polyline points="12 6 12 12 16 14"></polyline>
              </svg>
              <span>SISTEM INFORMASI REVIEW KAK</span>
            </div>
            <h1 class="hero-title">Kelola Review Kerangka Acuan Kerja Secara Terstruktur</h1>
            <p class="hero-description">
              KAK Management System membantu Deputi Pengadaan mengelola data pengadaan, dokumen KAK, proses review, status, dan progres secara lebih terarah dan terdokumentasi.
            </p>
            <p class="hero-support-text">
              Untuk proses pengadaan yang lebih transparan, akuntabel, dan mudah dipantau.
            </p>
            <div class="hero-cta-group">
              <button type="button" class="btn-hero-primary" onclick="showPage('login')">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
                  <polyline points="10 17 15 12 10 7"></polyline>
                  <line x1="15" y1="12" x2="3" y2="12"></line>
                </svg>
                <span>Login ke Sistem</span>
              </button>
              <span class="hero-login-subtext">Silakan masuk menggunakan akun yang telah terdaftar.</span>
            </div>
          </div>

          <div class="hero-visual-col">
            <div class="mockup-window">
              <div class="mockup-header">
                <div class="mockup-dots">
                  <span class="dot dot-red"></span>
                  <span class="dot dot-yellow"></span>
                  <span class="dot dot-green"></span>
                </div>
                <div class="mockup-title">Review KAK Preview</div>
              </div>
              <div class="mockup-body">
                <!-- Mockup Card 1: Dokumen KAK -->
                <div class="mockup-card">
                  <div class="mockup-card-header">
                    <div class="mockup-card-icon">
                      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                      </svg>
                    </div>
                    <div>
                      <div class="mockup-card-title">Dokumen KAK Pengadaan TI 2026</div>
                      <div class="mockup-card-subtitle">Deputi Pengadaan • ID: KAK-2026-004</div>
                    </div>
                  </div>
                </div>

                <!-- Mockup Card 2: Checklist & Progress -->
                <div class="mockup-card">
                  <div class="mockup-meta-row">
                    <span class="mockup-label">Progres Review</span>
                    <span class="mockup-badge-success">100% Selesai</span>
                  </div>
                  <div class="mockup-progress-bar">
                    <div class="mockup-progress-fill" style="width: 100%;"></div>
                  </div>
                  <div class="mockup-checklist">
                    <div class="check-item"><span class="check-icon">✓</span> Latar Belakang & Purpose</div>
                    <div class="check-item"><span class="check-icon">✓</span> Spesifikasi Teknis</div>
                    <div class="check-item"><span class="check-icon">✓</span> HPS & Rencana Anggaran</div>
                  </div>
                </div>

                <!-- Mockup Card 3: Status & Reviewer Note -->
                <div class="mockup-card">
                  <div class="mockup-meta-row">
                    <span class="mockup-label">Status Review</span>
                    <span class="status-pill status-approved">Disetujui</span>
                  </div>
                  <div class="mockup-note">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                    </svg>
                    <span><strong>Catatan Reviewer:</strong> Dokumen KAK telah memenuhi standar regulasi pengadaan.</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- 4. SECTION TUJUAN SISTEM -->
      <section id="tujuan" class="section-tujuan">
        <div class="landing-container">
          <div class="section-header">
            <span class="section-tag">TUJUAN SISTEM</span>
            <h2 class="section-title">Tujuan Sistem</h2>
            <p class="section-description">
              KAK Management System dikembangkan untuk membantu Deputi Pengadaan mengelola proses review Kerangka Acuan Kerja secara terpusat, terstruktur, dan terdokumentasi.
            </p>
          </div>

          <div class="goals-grid">
            <!-- Goal 1 -->
            <div class="goal-card">
              <div class="goal-icon icon-bg-green">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <ellipse cx="12" cy="5" rx="9" ry="3"></ellipse>
                  <path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"></path>
                  <path d="M21 19c0 1.66-4 3-9 3s-9-1.34-9-3"></path>
                  <path d="M3 5v14"></path>
                  <path d="M21 5v14"></path>
                </svg>
              </div>
              <h3 class="goal-card-title">Mengelola Data Pengadaan Secara Terpusat</h3>
              <p class="goal-card-text">
                Menyimpan dan mengelola data pengadaan dalam satu sistem sehingga informasi lebih mudah ditemukan dan dipantau.
              </p>
            </div>

            <!-- Goal 2 -->
            <div class="goal-card">
              <div class="goal-icon icon-bg-emerald">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                  <polyline points="14 2 14 8 20 8"></polyline>
                  <path d="M9 15l2 2 4-4"></path>
                </svg>
              </div>
              <h3 class="goal-card-title">Memfasilitasi Review KAK</h3>
              <p class="goal-card-text">
                Mendukung proses review KAK secara terstruktur berdasarkan bagian-bagian yang perlu diperiksa.
              </p>
            </div>

            <!-- Goal 3 -->
            <div class="goal-card">
              <div class="goal-icon icon-bg-green">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                  <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
              </div>
              <h3 class="goal-card-title">Menentukan Status Review</h3>
              <p class="goal-card-text">
                Membantu menentukan status review secara otomatis berdasarkan hasil pemeriksaan yang dilakukan.
              </p>
            </div>

            <!-- Goal 4 -->
            <div class="goal-card">
              <div class="goal-icon icon-bg-emerald">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <line x1="18" y1="20" x2="18" y2="10"></line>
                  <line x1="12" y1="20" x2="12" y2="4"></line>
                  <line x1="6" y1="20" x2="6" y2="14"></line>
                </svg>
              </div>
              <h3 class="goal-card-title">Menghitung Progres Review</h3>
              <p class="goal-card-text">
                Menampilkan persentase progres review agar perkembangan setiap pengadaan dapat dipantau dengan mudah.
              </p>
            </div>

            <!-- Goal 5 -->
            <div class="goal-card">
              <div class="goal-icon icon-bg-green">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                  <polyline points="14 2 14 8 20 8"></polyline>
                  <line x1="16" y1="13" x2="8" y2="13"></line>
                  <line x1="16" y1="17" x2="8" y2="17"></line>
                  <polyline points="10 9 9 9 8 9"></polyline>
                </svg>
              </div>
              <h3 class="goal-card-title">Menyediakan Laporan PDF</h3>
              <p class="goal-card-text">
                Menghasilkan laporan review yang terstruktur dalam format PDF untuk kebutuhan dokumentasi dan pelaporan.
              </p>
            </div>

            <!-- Goal 6 -->
            <div class="goal-card">
              <div class="goal-icon icon-bg-emerald">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <circle cx="12" cy="12" r="10"></circle>
                  <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
              </div>
              <h3 class="goal-card-title">Mencatat Aktivitas Sistem</h3>
              <p class="goal-card-text">
                Menyediakan log aktivitas pengguna untuk mendukung monitoring, transparansi, dan kebutuhan audit sistem.
              </p>
            </div>
          </div>
        </div>
      </section>

      <!-- 5. SECTION RUANG LINGKUP SISTEM -->
      <section id="ruang-lingkup" class="section-ruang-lingkup">
        <div class="landing-container">
          <div class="section-header">
            <span class="section-tag">RUANG LINGKUP</span>
            <h2 class="section-title">Ruang Lingkup Sistem</h2>
            <p class="section-description">
              KAK Management System mencakup proses pengelolaan pengadaan dan review KAK dari tahap input hingga pelaporan.
            </p>
          </div>

          <div class="scope-grid">
            <div class="scope-item">
              <div class="scope-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                  <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
              </div>
              <span>Manajemen data pengadaan.</span>
            </div>

            <div class="scope-item">
              <div class="scope-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                  <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
              </div>
              <span>Pengelolaan link dokumen KAK.</span>
            </div>

            <div class="scope-item">
              <div class="scope-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                  <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
              </div>
              <span>Proses review KAK per bagian.</span>
            </div>

            <div class="scope-item">
              <div class="scope-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                  <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
              </div>
              <span>Pemberian catatan review.</span>
            </div>

            <div class="scope-item">
              <div class="scope-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                  <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
              </div>
              <span>Penentuan status review secara otomatis.</span>
            </div>

            <div class="scope-item">
              <div class="scope-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                  <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
              </div>
              <span>Perhitungan progres review.</span>
            </div>

            <div class="scope-item">
              <div class="scope-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                  <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
              </div>
              <span>Manajemen user Admin / Notulis dan Reviewer.</span>
            </div>

            <div class="scope-item">
              <div class="scope-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                  <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
              </div>
              <span>Pengaturan role dan hak akses pengguna.</span>
            </div>

            <div class="scope-item">
              <div class="scope-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                  <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
              </div>
              <span>Pembuatan laporan dalam format PDF.</span>
            </div>

            <div class="scope-item">
              <div class="scope-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                  <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
              </div>
              <span>Pencatatan log aktivitas sistem.</span>
            </div>
          </div>

          <div class="scope-info-box">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="10"></circle>
              <line x1="12" y1="16" x2="12" y2="12"></line>
              <line x1="12" y1="8" x2="12.01" y2="8"></line>
            </svg>
            <span>Penggunaan sistem disesuaikan dengan role dan kewenangan masing-masing pengguna.</span>
          </div>

          <!-- 6. INFORMASI ROLE PENGGUNA -->
          <div class="roles-section">
            <h3 class="roles-title">Pengguna Sistem</h3>
            <div class="roles-grid">
              <!-- Admin / Notulis -->
              <div class="role-card">
                <div class="role-card-header">
                  <div class="role-icon-wrapper">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                      <circle cx="8.5" cy="7" r="4"></circle>
                      <polyline points="17 11 19 13 23 9"></polyline>
                    </svg>
                  </div>
                  <div>
                    <h4 class="role-name">Admin / Notulis</h4>
                    <span class="role-tag">Pengelola Sistem</span>
                  </div>
                </div>
                <ul class="role-features">
                  <li><span class="bullet">•</span> Mengelola data pengadaan.</li>
                  <li><span class="bullet">•</span> Mengelola link dokumen KAK.</li>
                  <li><span class="bullet">•</span> Mengelola user dan role.</li>
                  <li><span class="bullet">•</span> Mengakses laporan PDF.</li>
                  <li><span class="bullet">•</span> Melihat log aktivitas sistem.</li>
                </ul>
              </div>

              <!-- Reviewer -->
              <div class="role-card">
                <div class="role-card-header">
                  <div class="role-icon-wrapper">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                      <circle cx="9" cy="7" r="4"></circle>
                      <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                      <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                  </div>
                  <div>
                    <h4 class="role-name">Reviewer</h4>
                    <span class="role-tag">Pemeriksa KAK</span>
                  </div>
                </div>
                <ul class="role-features">
                  <li><span class="bullet">•</span> Melihat daftar pengadaan.</li>
                  <li><span class="bullet">•</span> Melakukan review KAK per bagian.</li>
                  <li><span class="bullet">•</span> Memberikan catatan review.</li>
                  <li><span class="bullet">•</span> Memilih reviewer yang terlibat.</li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- 7. CALL TO ACTION SEBELUM LOGIN -->
      <section class="section-cta">
        <div class="landing-container cta-container">
          <h2 class="cta-title">Siap Mengakses KAK Management System?</h2>
          <p class="cta-description">
            Masuk ke dalam sistem untuk mengelola data pengadaan, melakukan review KAK, memantau progres, dan mengakses laporan sesuai dengan kewenangan Anda.
          </p>
          <div class="cta-action-group">
            <button type="button" class="btn-cta-login" onclick="showPage('login')">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
                <polyline points="10 17 15 12 10 7"></polyline>
                <line x1="15" y1="12" x2="3" y2="12"></line>
              </svg>
              <span>Login ke Sistem</span>
            </button>
            <span class="cta-subtext">Akses sistem menggunakan akun yang telah terdaftar.</span>
          </div>
        </div>
      </section>
    </main>

    <!-- 8. FOOTER -->
    <footer class="landing-footer">
      <div class="landing-container footer-content">
        <!-- Footer Column 1: Info -->
        <div class="footer-col footer-col-info">
          <div class="footer-brand">
            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/c/c3/BPJS_Ketenagakerjaan_logo.svg/3840px-BPJS_Ketenagakerjaan_logo.svg.png" alt="BPJS Ketenagakerjaan Logo" class="footer-bpjs-logo">
            <div>
              <h3 class="footer-brand-title">KAK Management System</h3>
              <p class="footer-brand-subtitle">Sistem Review Kerangka Acuan Kerja Deputi Pengadaan</p>
            </div>
          </div>
          <p class="footer-description">
            Platform untuk mendukung pengelolaan data pengadaan, proses review KAK, monitoring progres, laporan, dan pencatatan aktivitas sistem.
          </p>
        </div>

        <!-- Footer Column 2: Navigation Links -->
        <div class="footer-col">
          <h4 class="footer-col-title">Navigasi</h4>
          <ul class="footer-links">
            <li><a href="#beranda">Beranda</a></li>
            <li><a href="#tujuan">Tujuan</a></li>
            <li><a href="#ruang-lingkup">Ruang Lingkup</a></li>
            <li><a href="#" onclick="showPage('login'); return false;">Login ke Sistem</a></li>
          </ul>
        </div>

        <!-- Footer Column 3: Organization Info -->
        <div class="footer-col">
          <h4 class="footer-col-title">Organisasi</h4>
          <p class="footer-org-name">BPJS Ketenagakerjaan</p>
          <p class="footer-dept-name">Deputi Pengadaan</p>
          <p class="footer-address">
            Graha BPJAMSOSTEK, Jl. Gatot Subroto No.79, RT.8/RW.2, Karet Semanggi, Kecamatan Setiabudi, Kota Jakarta Selatan, Daerah Khusus Ibukota Jakarta 12930
          </p>
        </div>
      </div>

      <!-- Footer Bottom Bar -->
      <div class="footer-bottom">
        <div class="landing-container">
          <p>© 2026 KAK Management System. Seluruh hak cipta dilindungi.</p>
        </div>
      </div>
    </footer>
  </div>

  <!-- LOGIN PAGE -->
  <div id="loginPage" class="page">
    <div class="login-container">
      <div class="login-card">
        <div class="login-header">
          <div class="login-logo">
            <img id="brandLogo" class="brand-logo" alt="BPJS Ketenagakerjaan" src="https://upload.wikimedia.org/wikipedia/commons/thumb/c/c3/BPJS_Ketenagakerjaan_logo.svg/3840px-BPJS_Ketenagakerjaan_logo.svg.png">
          </div>
          <h1>KAK Management System</h1>
          <p>BPJS Ketenagakerjaan • Deputi Pengadaan</p>
          <p class="login-role-help">Silakan masukkan username dan password sesuai role akun Anda: <strong>Admin/Notulis</strong> atau <strong>Reviewer</strong>.</p>
        </div>
        
        <form id="loginForm" onsubmit="handleLogin(event)">
          <div class="form-group">
            <label for="username">Username</label>
            <input 
              type="text" 
              id="username" 
              name="username" 
              placeholder="Masukkan username" 
              required
            >
          </div>
          
          <div class="form-group">
            <label for="password">Password</label>
            <input 
              type="password" 
              id="password" 
              name="password" 
              placeholder="Masukkan password" 
              required
            >
          </div>
          
          <div id="loginError" class="alert alert-danger" style="display: none;"></div>
          
          <button type="submit" class="btn btn-primary btn-block">Login ke Sistem</button>
        </form>

        <div style="text-align: center; margin-top: 20px;">
          <a href="#" onclick="showPage('landing'); return false;" style="color: var(--primary-medium); font-size: 13px; text-decoration: none; font-weight: 500; display: inline-flex; align-items: center; gap: 6px;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <line x1="19" y1="12" x2="5" y2="12"></line>
              <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            Kembali ke Landing Page
          </a>
        </div>
      </div>
    </div>
  </div>
  
  <!-- ADMIN DASHBOARD -->
  <div id="adminPage" class="page">
    <nav class="navbar">
      <div class="navbar-container">
        <div class="navbar-brand">
          <h2>KAK Management System</h2>
        </div>
        <div class="navbar-menu">
          <span id="userGreeting" class="user-greeting"></span>
          <button class="btn btn-danger btn-sm" onclick="handleLogout()">Logout</button>
        </div>
      </div>
    </nav>
    
    <div class="admin-container">
      <div class="sidebar">
        <ul class="menu">
          <li><a href="#" onclick="showAdminTab('dashboard')" class="menu-item active">Dashboard</a></li>
          <li><a href="#" onclick="showAdminTab('pengadaan')" class="menu-item">Kelola Pengadaan</a></li>
          <li><a href="#" onclick="showAdminTab('logActivity')" class="menu-item">Log Aktivitas</a></li>
          <li><a href="#" onclick="showAdminTab('reviewer')" class="menu-item">Kelola Reviewer dan Admin/Notulis</a></li>
        </ul>
      </div>
      
      <div class="main-content">
        <!-- TAB 0: DASHBOARD -->
        <div id="dashboardTab" class="tab">
          <div class="tab-header">
            <h3>Dashboard</h3>
          </div>
          
          <!-- Summary Cards -->
          <div class="dashboard-summary">
            <div class="summary-card card-total">
              <div class="card-icon icon-total">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
              </div>
              <div class="card-content">
                <div class="card-label">Total Pengadaan</div>
                <div class="card-value" id="totalPengadaan">0</div>
              </div>
            </div>
            
            <div class="summary-card card-pending">
              <div class="card-icon icon-pending">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
              </div>
              <div class="card-content">
                <div class="card-label">Belum Direview</div>
                <div class="card-value" id="belumReview">0</div>
              </div>
            </div>
            
            <div class="summary-card card-revision">
              <div class="card-icon icon-revision">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
              </div>
              <div class="card-content">
                <div class="card-label">Perlu Revisi</div>
                <div class="card-value" id="perluRevisi">0</div>
              </div>
            </div>
            
            <div class="summary-card card-approved">
              <div class="card-icon icon-approved">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
              </div>
              <div class="card-content">
                <div class="card-label">Disetujui</div>
                <div class="card-value" id="disetujui">0</div>
              </div>
            </div>
          </div>
          
          <!-- Charts Row 1: Pie + Monthly Column -->
          <div class="dashboard-charts-row">
            <div class="chart-container">
              <h4>Status Review Distribution</h4>
              <div id="chartStatusReview" style="width: 100%; height: 350px;"></div>
            </div>
            
            <div class="chart-container">
              <h4>Pengadaan per Bulan</h4>
              <div id="chartPengadaanPerBulan" style="width: 100%; height: 350px;"></div>
            </div>
          </div>
          
          <!-- Charts Row 2: Reviewer and Review Progress -->
          <div class="dashboard-charts-row">
            <div class="chart-container">
              <h4>Review per Reviewer <small>(ID Pengadaan)</small></h4>
              <div id="chartReviewPerReviewer" style="width: 100%; height: 350px;"></div>
            </div>
            <div class="chart-container">
              <h4>Progres Review berdasarkan Pelaksanaan</h4>
              <div id="chartTrenProgresReview" style="width: 100%; height: 350px;"></div>
            </div>
          </div>

          <!-- Action Required List -->
          <div class="dashboard-action-panel">
            <div class="dashboard-panel-heading">
              <div>
                <span class="dashboard-panel-kicker">MONITORING</span>
                <h4>Daftar Pengadaan yang Membutuhkan Tindakan</h4>
              </div>
              <button type="button" class="btn btn-secondary btn-sm" onclick="showAdminTab('pengadaan')">Lihat Pengadaan</button>
            </div>
            <div class="table-responsive dashboard-action-table-wrap">
              <table class="table dashboard-action-table">
                <thead>
                  <tr>
                    <th>ID Pengadaan</th>
                    <th>Nama Kegiatan</th>
                    <th>Status</th>
                    <th>Terakhir Diperbarui</th>
                  </tr>
                </thead>
                <tbody id="actionRequiredList">
                  <tr><td colspan="4" class="dashboard-empty-state">Memuat data pengadaan...</td></tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
        
        <!-- TAB 1: PENGADAAN -->
        <div id="pengadaanTab" class="tab">
          <div class="tab-header">
            <h3>Kelola Data Pengadaan</h3>
            <div class="tab-header-actions">
              <div class="filter-wrap">
                <button type="button" class="btn btn-secondary btn-sm filter-toggle" onclick="toggleFilterPanel('pengadaanFilterPanel')">Filter</button>
                <div id="pengadaanFilterPanel" class="filter-panel">
                  <div class="filter-panel-title">Filter Pengadaan</div>
                  <div class="filter-grid">
                    <div class="filter-field">
                      <label for="pengadaanStatusFilter">Status</label>
                      <select id="pengadaanStatusFilter" onchange="loadPengadaanList()">
                        <option value="ALL">Semua Status</option>
                        <option value="BELUM_REVIEW">Belum Direview</option>
                        <option value="DISETUJUI">Disetujui</option>
                        <option value="PERLU_REVISI">Perlu Revisi</option>
                      </select>
                    </div>
                    <div class="filter-field">
                      <label for="pengadaanKodeFilter">Kode Kantor</label>
                      <select id="pengadaanKodeFilter" onchange="loadPengadaanList()">
                        <option value="">Semua Kode Kantor</option>
                      </select>
                    </div>
                    <div class="filter-field">
                      <label for="pengadaanUnitFilter">Unit Kerja</label>
                      <select id="pengadaanUnitFilter" onchange="loadPengadaanList()">
                        <option value="">Semua Unit Kerja</option>
                      </select>
                    </div>
                    <div class="filter-field">
                      <label for="pengadaanPelaksanaanFilter">Pelaksanaan</label>
                      <select id="pengadaanPelaksanaanFilter" onchange="loadPengadaanList()">
                        <option value="">Semua Pelaksanaan</option>
                      </select>
                    </div>
                  </div>
                </div>
              </div>
              <button class="btn btn-info" onclick="downloadComprehensivePDF()">Unduh PDF Keseluruhan</button>
              <button class="btn btn-success" onclick="showPengadaanForm()">+ Tambah Pengadaan Baru</button>
            </div>
          </div>
          
          <div id="pengadaanFormContainer" style="display: none;" class="form-container">
            <div class="card">
              <div class="card-header"><span id="formStepTitle">Form Pengadaan</span></div>
              <div class="card-body">
                <!-- STEP 1: DATA PENGADAAN -->
                <div id="pengadaanStep">
                  <form id="pengadaanForm" onsubmit="nextToKAK(event)">
                    <div class="form-row">
                      <div class="form-group">
                        <label for="kodKantor">Kode Kantor</label>
                        <input type="text" id="kodKantor" name="kodKantor" placeholder="Contoh: KC-001" required>
                      </div>
                      <div class="form-group">
                        <label for="unitKerja">Unit Kerja</label>
                        <input type="text" id="unitKerja" name="unitKerja" placeholder="Contoh: IT Division" required>
                      </div>
                    </div>
                    
                    <div class="form-group">
                      <label for="nmKegiatan">Nama Kegiatan</label>
                      <input type="text" id="nmKegiatan" name="nmKegiatan" placeholder="Masukkan nama kegiatan" required>
                    </div>
                    
                    <div class="form-row">
                      <div class="form-group">
                        <label for="anggaran">Anggaran (Rp)</label>
                        <input type="number" id="anggaran" name="anggaran" placeholder="Contoh: 50000000" required>
                      </div>
                      <div class="form-group">
                        <label for="pelaksanaan">Periode Pelaksanaan</label>
                        <select id="pelaksanaan" name="pelaksanaan" required>
                          <option value="">Pilih bulan pelaksanaan</option>
                          <option value="Januari">Januari</option>
                          <option value="Februari">Februari</option>
                          <option value="Maret">Maret</option>
                          <option value="April">April</option>
                          <option value="Mei">Mei</option>
                          <option value="Juni">Juni</option>
                          <option value="Juli">Juli</option>
                          <option value="Agustus">Agustus</option>
                          <option value="September">September</option>
                          <option value="Oktober">Oktober</option>
                          <option value="November">November</option>
                          <option value="Desember">Desember</option>
                        </select>
                      </div>
                    </div>
                    
                    <div class="form-actions">
                      <button type="button" class="btn btn-secondary" onclick="hidePengadaanForm()">Batal</button>
                      <button type="submit" class="btn btn-primary">Selanjutnya</button>
                    </div>
                  </form>
                </div>
                
                <!-- STEP 2: DATA KAK -->
                <div id="kakStep" style="display: none;">
                  <form id="kakFormStep" onsubmit="savePengadaanWithKAK(event)">
                    <div id="kakFieldsContainerStep"></div>
                    <div class="form-actions">
                      <button type="button" class="btn btn-secondary" onclick="backToPengadaan()">Batal</button>
                      <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>
          
          <div id="pengadaanListContainer" class="table-responsive">
            <table class="table">
              <thead>
                <tr>
                  <th>ID Pengadaan</th>
                  <th>Kode Kantor</th>
                  <th>Unit Kerja</th>
                  <th>Nama Kegiatan</th>
                  <th>Anggaran</th>
                  <th>Pelaksanaan</th>
                  <th>Status KAK</th>
                  <th>Status Review</th>
                  <th>Tanggal Input</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody id="pengadaanList"></tbody>
            </table>
          </div>
        </div>
        
        <!-- TAB 2: KELOLA REVIEWER DAN ADMIN/NOTULIS -->
        <div id="reviewerTab" class="tab">
          <div class="tab-header">
            <h3>Kelola Reviewer dan Admin/Notulis</h3>
            <button class="btn btn-success" onclick="showReviewerForm()">+ Tambah Reviewer dan Admin/Notulis</button>
          </div>
          
          <div id="reviewerFormContainer" style="display: none;" class="form-container">
            <div class="card">
              <div class="card-header"><span id="reviewerFormTitle">Form Reviewer dan Admin/Notulis</span></div>
              <div class="card-body">
                <form id="reviewerForm" onsubmit="saveReviewer(event)">
                  <input type="hidden" id="editReviewerId" value="">
                  <div class="form-group">
                    <label for="roleReviewer">Role</label>
                    <select id="roleReviewer" name="roleReviewer" required>
                      <option value="Reviewer">Reviewer</option>
                      <option value="Admin">Admin/Notulis</option>
                    </select>
                  </div>
                  <div class="form-group">
                    <label for="namaReviewer">Nama</label>
                    <input type="text" id="namaReviewer" name="namaReviewer" required>
                  </div>
                  <div class="form-group">
                    <label for="usernameReviewer">Username</label>
                    <input type="text" id="usernameReviewer" name="usernameReviewer" required>
                  </div>
                  <div class="form-group">
                    <label for="passwordReviewer">Password</label>
                    <input type="password" id="passwordReviewer" name="passwordReviewer" required>
                  </div>
                  <div class="form-group">
                    <label for="activeReviewer">Status</label>
                    <select id="activeReviewer" name="activeReviewer" required>
                      <option value="true">Aktif</option>
                      <option value="false">Non-Aktif</option>
                    </select>
                  </div>
                  <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <button type="button" class="btn btn-secondary" onclick="hideReviewerForm()">Batal</button>
                  </div>
                </form>
              </div>
            </div>
          </div>
          
          <div id="reviewerListContainer" class="table-responsive">
            <table class="table">
              <thead>
                <tr>
                  <th>Nama</th>
                  <th>Username</th>
                  <th>Role</th>
                  <th>Status</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody id="reviewerList"></tbody>
            </table>
          </div>
        </div>

        <!-- TAB 3: LOG AKTIVITAS -->
        <div id="logActivityTab" class="tab">
          <div class="tab-header">
            <h3>Log Aktivitas</h3>
            <div class="tab-header-actions">
              <div class="search-box">
                <input type="text" id="logSearchInput" placeholder="Search log aktivitas">
              </div>
              <div class="filter-wrap">
                <button type="button" class="btn btn-secondary btn-sm filter-toggle" onclick="toggleFilterPanel('logActivityFilterPanel')">Filter</button>
                <div id="logActivityFilterPanel" class="filter-panel">
                  <div class="filter-panel-title">Filter Log Aktivitas</div>
                  <div class="filter-grid">
                    <div class="filter-field">
                      <label for="logDateFrom">Tanggal Mulai</label>
                      <input type="date" id="logDateFrom" class="filter-input">
                    </div>
                    <div class="filter-field">
                      <label for="logDateTo">Tanggal Selesai</label>
                      <input type="date" id="logDateTo" class="filter-input">
                    </div>
                    <div class="filter-field">
                      <label for="logUserFilter">User</label>
                      <select id="logUserFilter">
                        <option value="">Semua User</option>
                      </select>
                    </div>
                    <div class="filter-field">
                      <label for="logModuleFilter">Modul</label>
                      <select id="logModuleFilter">
                        <option value="">Semua Modul</option>
                      </select>
                    </div>
                    <div class="filter-field">
                      <label for="logActivityFilter">Jenis Aktivitas</label>
                      <select id="logActivityFilter">
                        <option value="">Semua Aktivitas</option>
                      </select>
                    </div>
                  </div>
                </div>
              </div>
              <button class="btn btn-info" onclick="exportLogActivityPDF()">Export PDF</button>
            </div>
          </div>

          <div class="table-responsive">
            <table class="table">
              <thead>
                <tr>
                  <th>Waktu</th>
                  <th>User</th>
                  <th>Role</th>
                  <th>Aktivitas</th>
                  <th>Modul</th>
                  <th>Referensi</th>
                </tr>
              </thead>
              <tbody id="logActivityList"></tbody>
            </table>
          </div>

          <div class="pagination-bar">
            <div class="pagination-info" id="logPageInfo">Page 1</div>
            <div class="pagination-controls">
              <button class="btn btn-secondary btn-sm" id="logPrevBtn" onclick="changeLogPage(-1)">Prev</button>
              <button class="btn btn-secondary btn-sm" id="logNextBtn" onclick="changeLogPage(1)">Next</button>
              <select id="logPageSize" onchange="changeLogPageSize()">
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
              </select>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  
  <!-- REVIEWER DASHBOARD -->
  <div id="reviewerPage" class="page">
    <nav class="navbar">
      <div class="navbar-container">
        <div class="navbar-brand">
          <h2>KAK Management System - Reviewer</h2>
        </div>
        <div class="navbar-menu">
          <span id="reviewerGreeting" class="user-greeting"></span>
          <button class="btn btn-danger btn-sm" onclick="handleLogout()">Logout</button>
        </div>
      </div>
    </nav>
    
    <div class="reviewer-container">
      <div class="sidebar">
        <ul class="menu">
          <li><a href="#" onclick="showReviewerTab('dashboard')" class="menu-item active">Dashboard</a></li>
          <li><a href="#" onclick="showReviewerTab('list')" class="menu-item">Daftar Pengadaan</a></li>
          <li><a href="#" onclick="showReviewerTab('review')" class="menu-item">Lakukan Review</a></li>
        </ul>
      </div>
      
      <div class="main-content">
        <!-- REVIEWER DASHBOARD -->
        <div id="reviewerDashboardTab" class="tab">
          <div class="tab-header">
            <h3>Dashboard</h3>
          </div>
          
          <!-- Summary Cards -->
          <div class="dashboard-summary">
            <div class="summary-card card-total">
              <div class="card-icon icon-total">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
              </div>
              <div class="card-content">
                <div class="card-label">Total Pengadaan</div>
                <div class="card-value" id="reviewerTotalPengadaan">0</div>
              </div>
            </div>
            
            <div class="summary-card card-pending">
              <div class="card-icon icon-pending">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
              </div>
              <div class="card-content">
                <div class="card-label">Belum Direview</div>
                <div class="card-value" id="reviewerBelumReview">0</div>
              </div>
            </div>
            
            <div class="summary-card card-revision">
              <div class="card-icon icon-revision">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
              </div>
              <div class="card-content">
                <div class="card-label">Perlu Revisi</div>
                <div class="card-value" id="reviewerPerluRevisi">0</div>
              </div>
            </div>
            
            <div class="summary-card card-approved">
              <div class="card-icon icon-approved">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
              </div>
              <div class="card-content">
                <div class="card-label">Disetujui</div>
                <div class="card-value" id="reviewerDisetujui">0</div>
              </div>
            </div>
          </div>
          
          <!-- Charts Row 1: Pie + Monthly Column -->
          <div class="dashboard-charts-row">
            <div class="chart-container">
              <h4>Status Review Distribution</h4>
              <div id="reviewerChartStatusReview" style="width: 100%; height: 350px;"></div>
            </div>
            
            <div class="chart-container">
              <h4>Pengadaan per Bulan</h4>
              <div id="reviewerChartPengadaanPerBulan" style="width: 100%; height: 350px;"></div>
            </div>
          </div>
        </div>
        
        <!-- LIST PENGADAAN UNTUK REVIEW -->
        <div id="reviewerListTab" class="tab">
          <div class="tab-header">
            <h3>Daftar Pengadaan untuk Review</h3>
            <div class="tab-header-actions">
              <div class="filter-wrap">
                <button type="button" class="btn btn-secondary btn-sm filter-toggle" onclick="toggleFilterPanel('reviewerFilterPanel')">Filter</button>
                <div id="reviewerFilterPanel" class="filter-panel">
                  <div class="filter-panel-title">Filter Daftar Pengadaan</div>
                  <div class="filter-grid">
                    <div class="filter-field">
                      <label for="reviewerStatusFilter">Status</label>
                      <select id="reviewerStatusFilter" onchange="loadReviewerPengadaanList()">
                        <option value="ALL">Semua Status</option>
                        <option value="BELUM_REVIEW">Belum Direview</option>
                        <option value="DISETUJUI">Disetujui</option>
                        <option value="PERLU_REVISI">Perlu Revisi</option>
                      </select>
                    </div>
                    <div class="filter-field">
                      <label for="reviewerKodeFilter">Kode Kantor</label>
                      <select id="reviewerKodeFilter" onchange="loadReviewerPengadaanList()">
                        <option value="">Semua Kode Kantor</option>
                      </select>
                    </div>
                    <div class="filter-field">
                      <label for="reviewerUnitFilter">Unit Kerja</label>
                      <select id="reviewerUnitFilter" onchange="loadReviewerPengadaanList()">
                        <option value="">Semua Unit Kerja</option>
                      </select>
                    </div>
                    <div class="filter-field">
                      <label for="reviewerPelaksanaanFilter">Pelaksanaan</label>
                      <select id="reviewerPelaksanaanFilter" onchange="loadReviewerPengadaanList()">
                        <option value="">Semua Pelaksanaan</option>
                      </select>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <div id="reviewerPengadaanList"></div>
        </div>
        
        <!-- FORM REVIEW -->
        <div id="reviewerReviewTab" class="tab">
          <div class="tab-header">
            <h3>Form Review KAK</h3>
          </div>
          
          <div class="card">
            <div class="card-body">
              <div class="form-group">
                <label for="reviewPengadaanSelect">Pilih Pengadaan</label>
                <select id="reviewPengadaanSelect" onchange="loadReviewForm()" required>
                  <option value="">-- Pilih Pengadaan --</option>
                </select>
              </div>
              
              <div id="reviewDataContainer" style="display: none;">
                <!-- Pengadaan Data -->
                <div class="card" style="margin-top: 20px;">
                  <div class="card-header">Data Pengadaan (Read-Only)</div>
                  <div class="card-body">
                    <div id="reviewPengadaanData"></div>
                  </div>
                </div>
                
                <!-- KAK Data -->
                <div class="card" style="margin-top: 20px;">
                  <div class="card-header">Isi KAK (Read-Only)</div>
                  <div class="card-body">
                    <div id="reviewKAKData"></div>
                  </div>
                </div>
                
                <!-- Review Form -->
                <div class="card" style="margin-top: 20px;">
                  <div class="card-header">Form Review</div>
                  <div class="card-body">
                    <form id="reviewSubmitForm" onsubmit="submitReview(event)">
                      <div id="reviewFormFields"></div>
                      <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Submit Review</button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  
  <script src="assets/js/script.js?v=20260217"></script>
</body>
</html>
