VEEL AUTO — SIMPLE PHP CRUD

No Node, npm, pnpm or React install step is needed.

Run it through XAMPP at `http://localhost/Taxirit/`.
- login.php = login
- register.php = chauffeur registratie
- index.php = live dashboard
- admin.php = alleen voor admin-rollen, ritten toewijzen en beheren
- chauffeur.php = alleen voor chauffeur-rollen
- ritten.php = admin/chauffeur rittenbeheer
- logout.php = uitloggen
- db.php = MySQL connection, tables and initial users
- database.sql = optional manual database setup script
- styles.css = styling

Start Apache and MySQL in XAMPP first. `db.php` creates the database and tables automatically. The default local MySQL settings are user `root` with an empty password on port `3306`; change them in `db.php` if needed.

Nieuwe registraties krijgen automatisch de rol `driver`. Alleen een bestaande admin kan adminrechten geven.

Initial accounts (change these passwords in the database after testing):
- Admin: `admin@veelauto.nl` / `admin123`
- Chauffeur: `ahmed@veelauto.nl` / `driver123`
