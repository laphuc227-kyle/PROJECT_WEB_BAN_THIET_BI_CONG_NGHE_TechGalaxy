<style>
    :root {
        --primary: #2563eb;
        --primary-dark: #1d4ed8;
        --sidebar-w: 240px;
        --bg-body: #f1f5f9;
        --bg-sidebar: #1e293b;
        --border: #e2e8f0;
        --text-muted: #64748b;
        --card-radius: 14px;
        --shadow: 0 2px 12px rgba(0,0,0,.07);
    }
    * { box-sizing: border-box; }
    body { font-family: 'Inter', sans-serif; background: var(--bg-body); margin: 0; }
    h1,h2,h3,h4,h5,h6 { font-family: 'Space Grotesk', sans-serif; }

    /* ---- Sidebar ---- */
    .admin-sidebar {
        position: fixed;
        top: 0; left: 0;
        width: var(--sidebar-w);
        height: 100vh;
        background: var(--bg-sidebar);
        display: flex;
        flex-direction: column;
        z-index: 1000;
        overflow-y: auto;
    }
    .sidebar-logo {
        padding: 1.4rem 1.5rem;
        font-family: 'Space Grotesk', sans-serif;
        font-size: 1.25rem;
        font-weight: 700;
        color: #fff;
        text-decoration: none;
        border-bottom: 1px solid rgba(255,255,255,.08);
    }
    .sidebar-logo span { color: #60a5fa; }
    .sidebar-nav { padding: .75rem 0; flex: 1; }
    .sidebar-label {
        font-size: .65rem;
        font-weight: 700;
        letter-spacing: .1em;
        text-transform: uppercase;
        color: rgba(255,255,255,.35);
        padding: .75rem 1.5rem .3rem;
    }
    .sidebar-link {
        display: flex;
        align-items: center;
        gap: .75rem;
        padding: .6rem 1.5rem;
        color: rgba(255,255,255,.7);
        text-decoration: none;
        font-size: .88rem;
        font-weight: 500;
        border-left: 3px solid transparent;
        transition: all .18s;
    }
    .sidebar-link i { font-size: 1rem; width: 18px; text-align: center; }
    .sidebar-link:hover { color: #fff; background: rgba(255,255,255,.06); }
    .sidebar-link.active { color: #fff; background: rgba(37,99,235,.25); border-left-color: #60a5fa; }

    /* ---- Content area ---- */
    .admin-content {
        margin-left: var(--sidebar-w);
        min-height: 100vh;
        display: flex;
        flex-direction: column;
    }
    .admin-topbar {
        background: #fff;
        border-bottom: 1px solid var(--border);
        padding: .85rem 1.5rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        position: sticky;
        top: 0;
        z-index: 100;
        box-shadow: 0 1px 4px rgba(0,0,0,.04);
    }
    .admin-topbar-title { font-weight: 600; color: #1e293b; font-size: .95rem; }
    .admin-main { flex: 1; }

    /* ---- Cards ---- */
    .admin-card {
        background: #fff;
        border-radius: var(--card-radius);
        box-shadow: var(--shadow);
        overflow: hidden;
    }
    .admin-card-header {
        padding: .9rem 1.25rem;
        border-bottom: 1px solid var(--border);
        font-weight: 600;
        font-size: .9rem;
        color: #374151;
        background: #f8fafc;
    }

    /* ---- Table ---- */
    .admin-table {
        width: 100%;
        border-collapse: collapse;
        font-size: .875rem;
    }
    .admin-table thead th {
        background: #f8fafc;
        border-bottom: 2px solid var(--border);
        padding: .75rem 1rem;
        font-weight: 600;
        color: #374151;
        white-space: nowrap;
        font-size: .8rem;
        text-transform: uppercase;
        letter-spacing: .04em;
    }
    .admin-table tbody td {
        padding: .8rem 1rem;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
        color: #374151;
    }
    .admin-table tbody tr:hover { background: #f8fafc; }
    .admin-table tbody tr:last-child td { border-bottom: none; }

    /* ---- Forms ---- */
    .form-card {
        background: #fff;
        border-radius: var(--card-radius);
        box-shadow: var(--shadow);
        padding: 2rem;
    }
    .form-label { font-weight: 500; font-size: .88rem; color: #374151; margin-bottom: .35rem; }
    .form-control:focus, .form-select:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(37,99,235,.15);
    }
    .form-control.is-invalid { border-color: #ef4444; }
    .invalid-feedback { color: #ef4444; font-size: .82rem; }

    /* Page title */
    .admin-page-title { font-weight: 700; font-size: 1.25rem; color: #1e293b; }

    /* Stats cards */
    .stat-card {
        background: #fff;
        border-radius: var(--card-radius);
        box-shadow: var(--shadow);
        padding: 1.25rem 1.5rem;
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    .stat-card__icon {
        width: 48px; height: 48px;
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.3rem;
        flex-shrink: 0;
    }
    .stat-card__value { font-family: 'Space Grotesk', sans-serif; font-size: 1.6rem; font-weight: 700; color: #1e293b; }
    .stat-card__label { font-size: .8rem; color: var(--text-muted); }

    /* Responsive */
    @media (max-width: 992px) {
        .admin-sidebar { transform: translateX(-100%); transition: transform .25s; }
        .admin-sidebar.open { transform: translateX(0); }
        .admin-content { margin-left: 0; }
    }
</style>