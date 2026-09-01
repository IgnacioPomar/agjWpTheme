<?php
header ("HTTP/1.1 404 Not Found");
header ("Content-Type: text/html; charset=UTF-8");
?>
<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">
<html>
<head>
    <title>404 Not Found</title>
</head>
<body>
    <h1>Not Found</h1>
    <p>The requested URL <?php

echo htmlspecialchars ($_SERVER ['REQUEST_URI']);
				?> was not found on this server.</p>
    <hr>
    <address><?php

echo $_SERVER ['SERVER_SOFTWARE'] ?? 'Apache';
				?> Server at <?php

echo $_SERVER ['SERVER_NAME'];
				?> Port <?php

echo $_SERVER ['SERVER_PORT'];
				?></address>
</body>
</html>