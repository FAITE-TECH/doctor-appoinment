<?php
// index.php removed the testing hub page and now redirects to the frontend landing page.
// This preserves external links to /index.php while removing the 'Testing and Development Hub' content.
header('Location: Frontend/pages/index.html', true, 302);
exit;





