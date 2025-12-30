diff --git a/estudio-api/api/cors.php b/estudio-api/api/cors.php
new file mode 100644
index 0000000000000000000000000000000000000000..cad127b8853d228bca38b34c045eb2af8982846a
--- /dev/null
+++ b/estudio-api/api/cors.php
@@ -0,0 +1,27 @@
+<?php
+
+function apply_cors(array $methods): void
+{
+    $defaultOrigins = getenv('FRONTEND_ORIGINS') ?: 'http://localhost:4200';
+    $allowedOrigins = array_values(array_filter(array_map('trim', explode(',', $defaultOrigins))));
+
+    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
+    $allowOrigin = '*';
+
+    if (!empty($allowedOrigins)) {
+        $allowOrigin = $allowedOrigins[0];
+        if ($origin !== '' && in_array($origin, $allowedOrigins, true)) {
+            $allowOrigin = $origin;
+        }
+    }
+
+    header('Access-Control-Allow-Origin: ' . $allowOrigin);
+    header('Vary: Origin');
+    header('Access-Control-Allow-Headers: Content-Type, Authorization');
+    header('Access-Control-Allow-Methods: ' . implode(', ', $methods));
+
+    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
+        http_response_code(204);
+        exit;
+    }
+}
