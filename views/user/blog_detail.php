<?php
/**
 * views/user/blog_detail.php — Chi tiết bài viết
 * Route: GET /blog/{slug}
 * Variables: $post, $comments, $related, $readingTime, $pageTitle, $metaDesc
 */
$extraCSS = '<link href="' . BASE_URL . '/assets/css/blog.css" rel="stylesheet">
<meta name="description" content="' . htmlspecialchars($metaDesc ?? '') . '">';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';
?>

<main>
  <!-- ===== BREADCRUMB ===== -->
  <div class="py-3" style="background: var(--bg-light); border-bottom: 1px solid var(--border);">
    <div class="container">
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
          <li class="breadcrumb-item"><a href="<?= BASE_URL ?>">Trang chủ</a></li>
          <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/blog">Blog</a></li>
          <li class="breadcrumb-item active text-truncate" style="max-width: 300px;" aria-current="page">
            <?= htmlspecialchars($post['title']) ?>
          </li>
        </ol>
      </nav>
    </div>
  </div>

  <!-- ===== MAIN CONTENT ===== -->
  <section class="py-5">
    <div class="container">
      <div class="row g-5">

        <!-- ===== ARTICLE (8 cols) ===== -->
        <div class="col-lg-8">
          <article>

            <!-- Ảnh cover full width -->
            <?php if (!empty($post['image'])): ?>
            <img
              src="<?= BASE_URL . '/public/uploads/' . htmlspecialchars($post['image']) ?>"
              alt="<?= htmlspecialchars($post['title']) ?>"
              class="img-fluid rounded-3 w-100 mb-4"
              style="max-height: 420px; object-fit: cover;"
            >
            <?php endif; ?>

            <!-- Header bài viết -->
            <header class="mb-4">
              <h1 class="h2 fw-bold mb-3" style="color: var(--text-main); line-height: 1.35;">
                <?= htmlspecialchars($post['title']) ?>
              </h1>

              <div class="d-flex flex-wrap align-items-center gap-3 text-muted small">
                <?php if (!empty($post['author_name'])): ?>
                <span class="d-flex align-items-center gap-1">
                  <?php if (!empty($post['author_avatar'])): ?>
                    <img src="<?= BASE_URL . '/public/uploads/' . htmlspecialchars($post['author_avatar']) ?>"
                         alt="<?= htmlspecialchars($post['author_name']) ?>"
                         class="rounded-circle"
                         style="width: 28px; height: 28px; object-fit: cover;">
                  <?php else: ?>
                    <i class="fa-solid fa-circle-user"></i>
                  <?php endif; ?>
                  <?= htmlspecialchars($post['author_name']) ?>
                </span>
                <?php endif; ?>

                <span>
                  <i class="fa-regular fa-calendar me-1"></i>
                  <time datetime="<?= htmlspecialchars($post['created_at']) ?>">
                    <?= date('d/m/Y', strtotime($post['created_at'])) ?>
                  </time>
                </span>

                <span>
                  <i class="fa-regular fa-clock me-1"></i>
                  <?= $readingTime ?? 1 ?> phút đọc
                </span>

                <span>
                  <i class="fa-regular fa-comment me-1"></i>
                  <?= count($comments) ?> bình luận
                </span>
              </div>
            </header>

            <!-- ===== NỘI DUNG BÀI VIẾT ===== -->
            <!-- Nội dung từ editor được render HTML — đã sanitize XSS tại controller -->
            <div class="post-content mb-5" style="line-height: 1.85; color: var(--text-main);">
              <?= $post['content'] // Đã qua sanitize ở controller; không escape ở đây ?>
            </div>

            <hr style="border-color: var(--border);">

            <!-- ===== COMMENTS ===== -->
            <section class="mt-5" id="comments">
              <h2 class="h5 fw-bold mb-4" style="color: var(--text-main);">
                Bình luận (<?= count($comments) ?>)
              </h2>

              <?php if (empty($comments)): ?>
                <p class="text-muted">Chưa có bình luận nào. Hãy là người đầu tiên!</p>
              <?php else: ?>
                <div class="d-flex flex-column gap-3 mb-4">
                  <?php foreach ($comments as $comment): ?>
                  <div class="d-flex gap-3 p-3 rounded-3"
                       style="background: var(--bg-light); border: 1px solid var(--border);">
                    <!-- Avatar -->
                    <div class="flex-shrink-0">
                      <?php if (!empty($comment['user_avatar'])): ?>
                        <img src="<?= BASE_URL . '/public/uploads/' . htmlspecialchars($comment['user_avatar']) ?>"
                             alt="<?= htmlspecialchars($comment['user_name'] ?? 'Ẩn danh') ?>"
                             class="rounded-circle"
                             style="width: 40px; height: 40px; object-fit: cover;">
                      <?php else: ?>
                        <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold"
                             style="width: 40px; height: 40px; background: var(--primary); font-size: 1rem;">
                          <?= mb_strtoupper(mb_substr($comment['user_name'] ?? 'A', 0, 1)) ?>
                        </div>
                      <?php endif; ?>
                    </div>

                    <!-- Nội dung -->
                    <div>
                      <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="fw-semibold small" style="color: var(--text-main);">
                          <?= htmlspecialchars($comment['user_name'] ?? 'Ẩn danh') ?>
                        </span>
                        <time class="text-muted" style="font-size: 0.75rem;"
                              datetime="<?= htmlspecialchars($comment['created_at']) ?>">
                          <?= timeAgo($comment['created_at']) ?>
                        </time>
                      </div>
                      <p class="mb-0 small" style="color: var(--text-main); white-space: pre-line;">
                        <?= htmlspecialchars($comment['content']) ?>
                      </p>
                    </div>
                  </div>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>

              <!-- ===== FORM COMMENT ===== -->
              <?php if (isLoggedIn()): ?>
              <div class="p-4 rounded-3" style="background: #fff; border: 1px solid var(--border);">
                <h3 class="h6 fw-bold mb-3">Viết bình luận</h3>
                <?= getFlash() ?>
                <form method="POST" action="<?= BASE_URL ?>/blog/<?= htmlspecialchars($post['slug']) ?>">
                  <input type="hidden" name="csrf_token" value="<?= generateToken() ?>">
                  <div class="mb-3">
                    <textarea
                      name="comment_content"
                      class="form-control"
                      rows="4"
                      maxlength="1000"
                      placeholder="Nhập bình luận của bạn..."
                      required
                    ></textarea>
                    <div class="form-text text-end">Tối đa 1000 ký tự</div>
                  </div>
                  <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-paper-plane me-1"></i> Gửi bình luận
                  </button>
                </form>
              </div>
              <?php else: ?>
              <div class="p-4 rounded-3 text-center"
                   style="background: var(--bg-light); border: 1px solid var(--border);">
                <p class="mb-3 text-muted">Đăng nhập để bình luận bài viết này.</p>
                <a href="<?= BASE_URL ?>/login?redirect=<?= urlencode(BASE_URL . '/blog/' . $post['slug']) ?>"
                   class="btn btn-primary">
                  <i class="fa-solid fa-right-to-bracket me-1"></i> Đăng nhập
                </a>
              </div>
              <?php endif; ?>
            </section>

          </article>
        </div>

        <!-- ===== SIDEBAR (4 cols) ===== -->
        <aside class="col-lg-4">

          <!-- Bài viết liên quan -->
          <?php if (!empty($related)): ?>
          <div class="p-3 rounded-3 mb-4" style="border: 1px solid var(--border); background: #fff;">
            <h3 class="h6 fw-bold mb-3" style="color: var(--text-main);">Bài viết liên quan</h3>
            <div class="d-flex flex-column gap-3">
              <?php foreach ($related as $rel): ?>
              <div class="d-flex gap-2">
                <img
                  src="<?= !empty($rel['image']) ? BASE_URL . '/public/uploads/' . htmlspecialchars($rel['image']) : BASE_URL . '/assets/img/blog-placeholder.jpg' ?>"
                  alt="<?= htmlspecialchars($rel['title']) ?>"
                  style="width: 70px; height: 55px; object-fit: cover; border-radius: 8px; flex-shrink: 0;"
                >
                <div>
                  <a href="<?= BASE_URL ?>/blog/<?= htmlspecialchars($rel['slug']) ?>"
                     class="text-decoration-none small fw-medium d-block"
                     style="color: var(--text-main); line-height: 1.35;">
                    <?= htmlspecialchars(mb_substr($rel['title'], 0, 60)) . (mb_strlen($rel['title']) > 60 ? '...' : '') ?>
                  </a>
                  <span class="text-muted" style="font-size: 0.75rem;">
                    <?= date('d/m/Y', strtotime($rel['created_at'])) ?>
                  </span>
                </div>
              </div>
              <?php endforeach; ?>
            </div>
          </div>
          <?php endif; ?>

          <!-- Chia sẻ -->
          <div class="p-3 rounded-3" style="border: 1px solid var(--border); background: #fff;">
            <h3 class="h6 fw-bold mb-3" style="color: var(--text-main);">Chia sẻ bài viết</h3>
            <div class="d-flex gap-2">
              <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode(BASE_URL . '/blog/' . $post['slug']) ?>"
                 target="_blank" rel="noopener"
                 class="btn btn-sm"
                 style="background: #1877f2; color: #fff; border-radius: 8px;">
                <i class="fa-brands fa-facebook-f me-1"></i> Facebook
              </a>
              <a href="https://twitter.com/intent/tweet?url=<?= urlencode(BASE_URL . '/blog/' . $post['slug']) ?>&text=<?= urlencode($post['title']) ?>"
                 target="_blank" rel="noopener"
                 class="btn btn-sm"
                 style="background: #1da1f2; color: #fff; border-radius: 8px;">
                <i class="fa-brands fa-x-twitter me-1"></i> Twitter
              </a>
            </div>
          </div>

        </aside>
      </div>
    </div>
  </section>
</main>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>