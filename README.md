web online voting system 2082 Nepal(demo for final project)
People can register and log in with passwords that are safely stored using bcrypt hashing (so raw passwords are never saved).
There are two roles: admin and voter. Access to pages is controlled with a require_role() check.
•	CSRF protection: Each form includes a hidden token (csrf_input_field()), stored in the session. When submitted, the system checks it with verify_csrf_token() to prevent fake requests.
•	XSS protection: All output is escaped with htmlspecialchars() so malicious scripts can’t run.
•	Sql injection attack protection: uses prepared statements and parameter binding for all places that accept user input (inserts, deletes, searches, voting), so SQL injection is prevented

Admin can:
•	Manage Elections :create, update, delete elections.
•	Manage Candidates :add candidates (with image upload), delete candidates.
•	Manage Voters: add or remove voters.
•	View Results:see totals and percentages for each election.
Voting Flow
•	Voters log in to their dashboard.
•	They go to the vote page and cast their vote.
•	The system ensures no double voting.
•	Results are shown live with count_votes.php and ajax.js, which refreshes counts periodically.
Results & Live Updates
•	Votes are counted server side.
•	Pages use AJAX to fetch updated totals so results feel live without reloading.

Logging
•	All important actions (like creating elections, deleting candidates, or casting votes) are recorded in audit_log.php for accountability.
Search
•	Admins can search voters or candidates using a simple GET request with a LIKE query in SQL.
Image Uploads
•	Candidate images are uploaded with server side validation (checking file type, size, etc.).

