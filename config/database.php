@@ .. @@
     $conn->exec($sql);
     
+    // Work Experience table
+    $sql = "CREATE TABLE IF NOT EXISTS work_experience (
+        id INT AUTO_INCREMENT PRIMARY KEY,
+        user_id INT NOT NULL,
+        job_title VARCHAR(255) NOT NULL,
+        company VARCHAR(255) NOT NULL,
+        location VARCHAR(255),
+        start_date DATE NOT NULL,
+        end_date DATE,
+        description TEXT,
+        is_current BOOLEAN DEFAULT FALSE,
+        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
+        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
+    )";
+    $conn->exec($sql);
+    
+    // Education table
+    $sql = "CREATE TABLE IF NOT EXISTS education (
+        id INT AUTO_INCREMENT PRIMARY KEY,
+        user_id INT NOT NULL,
+        degree VARCHAR(255) NOT NULL,
+        institution VARCHAR(255) NOT NULL,
+        location VARCHAR(255),
+        start_date DATE NOT NULL,
+        end_date DATE,
+        description TEXT,
+        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
+        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
+    )";
+    $conn->exec($sql);
+    
+    // User Skills table
+    $sql = "CREATE TABLE IF NOT EXISTS user_skills (
+        id INT AUTO_INCREMENT PRIMARY KEY,
+        user_id INT NOT NULL,
+        skill_name VARCHAR(255) NOT NULL,
+        skill_level ENUM('beginner', 'intermediate', 'advanced', 'expert') DEFAULT 'intermediate',
+        category VARCHAR(100),
+        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
+        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
+    )";
+    $conn->exec($sql);
+    
     // Companies table
     $sql = "CREATE TABLE IF NOT EXISTS companies (