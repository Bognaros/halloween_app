Updated secure version with:
- Host login (login.php/logout.php)
- Host-only start_vote.php (requires login)
- One-vote-per-user enforced by voter_uid stored in PHP session
- QR code image displayed via Google Chart API
Installation: extract to C:\xampp\htdocs\halloween_app_secure and start Apache. Visit http://localhost/halloween_app_secure/
Change admin credentials in login.php before event (ADMIN_USER and ADMIN_PASS).
