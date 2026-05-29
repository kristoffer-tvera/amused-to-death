<?php
http_response_code(401);
header('Content-Type: application/json; charset=UTF-8');
echo json_encode(['error' => 'Unauthorized'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
