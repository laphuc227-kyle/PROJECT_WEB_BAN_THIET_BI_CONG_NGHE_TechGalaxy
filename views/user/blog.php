<?php
/**
 * views/user/blog.php — Danh sách bài viết Blog
 * Route: GET /blog
 */
$extraCSS = '<link href="' . BASE_URL . '/assets/css/blog.css" rel="stylesheet">';
$pageTitle = $pageTitle ?? 'Blog — TechGalaxy';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';
?>

<main>
  <!-- ===== HERO BREADCRUMB ===== -->
  <section class="blog-hero py-4" style="background: var(--bg-light); border-bottom: 1px solid var(--border);">
    <div class="container">
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1">
          <li class="breadcrumb-item"><a href="<?= BASE_URL ?>">Trang chủ</a></li>
          <li class="breadcrumb-item active" aria-current="page">Blog</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 fw-bold" style="color: var(--text-main);">Tin tức & Công nghệ</h1>
    </div>
  </section>

  <!-- ===== BLOG CONTENT ===== -->
  <section class="py-5">
    <div class="container">
      <div class="row g-4">

        <!-- ===== POSTS GRID (9 cols) ===== -->
        <div class="col-lg-9">

          <?php if (empty($posts)): ?>
            <div class="text-center py-5 text-muted">
              <i class="fa-regular fa-newspaper fa-3x mb-3 d-block"></i>
              <p>Chưa có bài viết nào.</p>
            </div>
          <?php else: ?>

            <div class="row g-4">
              <?php foreach ($posts as $post): ?>
              <div class="col-md-6 col-lg-4">
                <article class="blog-card h-100 rounded-3 overflow-hidden"
                         style="border: 1px solid var(--border); box-shadow: var(--shadow); background: #fff; transition: transform .2s;">
                  <!-- Ảnh thumbnail -->
                  <a href="<?= BASE_URL ?>/blog/<?= htmlspecialchars($post['slug']) ?>">
                    <img
                      src="<?= !empty($post['image']) ? BASE_URL . '/public/uploads/' . htmlspecialchars($post['image']) : BASE_URL . '/assets/img/blog-placeholder.jpg' ?>"
                      alt="<?= htmlspecialchars($post['title']) ?>"
                      class="img-fluid w-100"
                      style="height: 200px; object-fit: cover;"
                      loading="lazy"
                    >
                  </a>

                  <div class="p-3 d-flex flex-column h-100">
                    <!-- Meta -->
                    <div class="d-flex align-items-center gap-2 mb-2 text-muted small">
                      <i class="fa-regular fa-calendar"></i>
                      <time datetime="<?= htmlspecialchars($post['created_at']) ?>">
                        <?= date('d/m/Y', strtotime($post['created_at'])) ?>
                      </time>
                      <?php if (!empty($post['author_name'])): ?>
                        <span>·</span>
                        <span><?= htmlspecialchars($post['author_name']) ?></span>
                      <?php endif; ?>
                    </div>

                    <!-- Tiêu đề -->
                    <h2 class="h6 fw-semibold mb-2" style="color: var(--text-main);">
                      <a href="<?= BASE_URL ?>/blog/<?= htmlspecialchars($post['slug']) ?>"
                         class="text-decoration-none stretched-link"
                         style="color: inherit;">
                        <?= htmlspecialchars($post['title']) ?>
                      </a>
                    </h2>

                    <!-- Excerpt -->
                    <p class="text-muted small flex-grow-1 mb-3">
                      <?= htmlspecialchars($post['excerpt'] ?? '') ?>
                    </p>

                    <!-- Read more -->
                    <span class="text-primary small fw-medium">
                      Đọc thêm <i class="fa-solid fa-arrow-right ms-1"></i>
                    </span>
                  </div>
                </article>
              </div>
              <?php endforeach; ?>
            </div>

            <!-- ===== PAGINATION ===== -->
            <?php if ($totalPages > 1): ?>
            <nav class="mt-5" aria-label="Phân trang bài viết">
              <ul class="pagination justify-content-center gap-1">
                <!-- Prev -->
                <li class="page-item <?= $currentPage <= 1 ? 'disabled' : '' ?>">
                  <a class="page-link rounded-2"
                     href="<?= BASE_URL ?>/blog?page=<?= $currentPage - 1 ?>">
                    <i class="fa-solid fa-chevron-left"></i>
                  </a>
                </li>

                <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                <li class="page-item <?= $p === $currentPage ? 'active' : '' ?>">
                  <a class="page-link rounded-2"
                     href="<?= BASE_URL ?>/blog?page=<?= $p ?>"><?= $p ?></a>
                </li>
                <?php endfor; ?>

                <!-- Next -->
                <li class="page-item <?= $currentPage >= $totalPages ? 'disabled' : '' ?>">
                  <a class="page-link rounded-2"
                     href="<?= BASE_URL ?>/blog?page=<?= $currentPage + 1 ?>">
                    <i class="fa-solid fa-chevron-right"></i>
                  </a>
                </li>
              </ul>
            </nav>
            <?php endif; ?>

          <?php endif; ?>
        </div>

        <!-- ===== SIDEBAR (3 cols) ===== -->
        <aside class="col-lg-3">

          <!-- Tìm kiếm -->
          <div class="mb-4 p-3 rounded-3" style="border: 1px solid var(--border); background: #fff;">
            <h3 class="h6 fw-bold mb-3" style="color: var(--text-main);">Tìm kiếm</h3>
            <form action="<?= BASE_URL ?>/blog/search" method="GET">
              <div class="input-group">
                <input type="text" name="q" class="form-control form-control-sm"
                       placeholder="Tìm bài viết...">
                <button class="btn btn-primary btn-sm" type="submit">
                  <i class="fa-solid fa-magnifying-glass"></i>
                </button>
              </div>
            </form>
          </div>

          <!-- Bài viết mới nhất -->
          <div class="mb-4 p-3 rounded-3" style="border: 1px solid var(--border); background: #fff;">
            <h3 class="h6 fw-bold mb-3" style="color: var(--text-main);">Bài viết mới nhất</h3>
            <?php if (!empty($posts)): ?>
              <?php foreach (array_slice($posts, 0, 4) as $recent): ?>
              <div class="d-flex gap-2 mb-3">
                <img
                  src="<?= !empty($recent['image']) ? BASE_URL . '/public/uploads/' . htmlspecialchars($recent['image']) : BASE_URL . '/assets/img/blog-placeholder.jpg' ?>"
                  alt="<?= htmlspecialchars($recent['title']) ?>"
                  style="width: 60px; height: 50px; object-fit: cover; border-radius: 6px; flex-shrink: 0;"
                >
                <div>
                  <a href="<?= BASE_URL ?>/blog/<?= htmlspecialchars($recent['slug']) ?>"
                     class="text-decoration-none small fw-medium"
                     style="color: var(--text-main); line-height: 1.3; display: block;">
                    <?= htmlspecialchars(mb_substr($recent['title'], 0, 55)) . (mb_strlen($recent['title']) > 55 ? '...' : '') ?>
                  </a>
                  <span class="text-muted" style="font-size: 0.75rem;">
                    <?= date('d/m/Y', strtotime($recent['created_at'])) ?>
                  </span>
                </div>
              </div>
              <?php endforeach; ?>
            <?php else: ?>
              <p class="text-muted small">Chưa có bài viết.</p>
            <?php endif; ?>
          </div>

        </aside>
      </div>
    </div>
  </section>
</main>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>