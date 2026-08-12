<?php
// config.php - Configuration file
define('API_URL', 'https://emkc.org/api/v2/piston/execute');

// Get supported languages from Piston API
function getSupportedLanguages() {
    $ch = curl_init('https://emkc.org/api/v2/piston/runtimes');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    
    if ($response) {
        return json_decode($response, true);
    }
    return [];
}

// Execute code via API
function executeCode($language, $version, $code, $stdin = '') {
    $data = [
        'language' => $language,
        'version' => $version,
        'files' => [
            [
                'content' => $code
            ]
        ],
        'stdin' => $stdin
    ];
    
    $ch = curl_init(API_URL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200 && $response) {
        return json_decode($response, true);
    }
    
    return ['error' => 'Failed to execute code'];
}

// Handle AJAX request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    if ($_POST['action'] === 'execute') {
        $language = $_POST['language'] ?? '';
        $version = $_POST['version'] ?? '';
        $code = $_POST['code'] ?? '';
        $stdin = $_POST['stdin'] ?? '';
        
        $result = executeCode($language, $version, $code, $stdin);
        echo json_encode($result);
        exit;
    }
    
    if ($_POST['action'] === 'get_languages') {
        $languages = getSupportedLanguages();
        echo json_encode($languages);
        exit;
    }
}

// Default code templates
$templates = [
    'python' => "print('Hello, World!')\n\n# Your code here",
    'javascript' => "console.log('Hello, World!');\n\n// Your code here",
    'java' => "public class Main {\n    public static void main(String[] args) {\n        System.out.println(\"Hello, World!\");\n    }\n}",
    'cpp' => "#include <iostream>\nusing namespace std;\n\nint main() {\n    cout << \"Hello, World!\" << endl;\n    return 0;\n}",
    'c' => "#include <stdio.h>\n\nint main() {\n    printf(\"Hello, World!\\n\");\n    return 0;\n}",
    'php' => "<?php\necho \"Hello, World!\\n\";\n?>",
    'ruby' => "puts 'Hello, World!'\n\n# Your code here",
    'go' => "package main\n\nimport \"fmt\"\n\nfunc main() {\n    fmt.Println(\"Hello, World!\")\n}",
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Code Compiler</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header h1 {
            font-size: 28px;
            font-weight: 600;
        }
        
        .controls {
            background: #f8f9fa;
            padding: 20px 30px;
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .control-group {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        label {
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }
        
        select, input {
            padding: 8px 12px;
            border: 2px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            outline: none;
            transition: border-color 0.3s;
        }
        
        select:focus, input:focus {
            border-color: #667eea;
        }
        
        select {
            min-width: 150px;
            cursor: pointer;
        }
        
        .btn {
            padding: 10px 24px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
        }
        
        .btn:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
        }
        
        .editor-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            padding: 30px;
        }
        
        .editor-section {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        
        .section-title {
            font-size: 16px;
            font-weight: 600;
            color: #333;
            padding-bottom: 10px;
            border-bottom: 2px solid #667eea;
        }
        
        textarea {
            width: 100%;
            padding: 15px;
            border: 2px solid #ddd;
            border-radius: 6px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            line-height: 1.6;
            resize: vertical;
            outline: none;
            transition: border-color 0.3s;
        }
        
        textarea:focus {
            border-color: #667eea;
        }
        
        #code {
            min-height: 400px;
        }
        
        #stdin {
            min-height: 100px;
        }
        
        #output {
            min-height: 300px;
            background: #1e1e1e;
            color: #d4d4d4;
            font-family: 'Courier New', monospace;
        }
        
        .loading {
            display: none;
            text-align: center;
            padding: 20px;
            color: #667eea;
            font-weight: 600;
        }
        
        .loading.active {
            display: block;
        }
        
        .spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #667eea;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 10px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .error {
            background: #fee;
            color: #c33;
            padding: 15px;
            border-radius: 6px;
            margin: 20px 30px;
            border-left: 4px solid #c33;
        }
        
        @media (max-width: 968px) {
            .editor-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🚀 Online Code Compiler</h1>
            <div style="font-size: 14px; opacity: 0.9;">Multi-Language Support</div>
        </div>
        
        <div class="controls">
            <div class="control-group">
                <label for="language">Language:</label>
                <select id="language" onchange="onLanguageChange()">
                    <option value="">Loading...</option>
                </select>
            </div>
            
            <div class="control-group">
                <label for="version">Version:</label>
                <select id="version">
                    <option value="">Select version</option>
                </select>
            </div>
            
            <button class="btn btn-primary" onclick="runCode()" id="runBtn">
                ▶ Run Code
            </button>
            
            <button class="btn btn-secondary" onclick="clearAll()">
                🗑️ Clear
            </button>
        </div>
        
        <div class="editor-container">
            <div class="editor-section">
                <div class="section-title">📝 Source Code</div>
                <textarea id="code" placeholder="Write your code here..."><?php echo htmlspecialchars($templates['python']); ?></textarea>
                
                <div class="section-title" style="margin-top: 20px;">⌨️ Standard Input (stdin)</div>
                <textarea id="stdin" placeholder="Enter input for your program (optional)..."></textarea>
            </div>
            
            <div class="editor-section">
                <div class="section-title">📤 Output</div>
                <div class="loading" id="loading">
                    <div class="spinner"></div>
                    <div>Executing code...</div>
                </div>
                <textarea id="output" readonly placeholder="Output will appear here..."></textarea>
            </div>
        </div>

        <footer style="background: #2d3748; color: white; padding: 20px 30px; text-align: center;">
            <p style="margin: 0 0 8px 0; font-size: 14px;">
                <strong><?php echo "Online Compiler"; ?></strong> - Powered by Rahul Debnath
            </p>
            <p style="margin: 0; font-size: 12px; opacity: 0.8;">
                © <?php echo "2026" . ' ' . "Your Company - rahul debnath"; ?>. All rights reserved. | 
                <a href="#" style="color: #63b3ed; text-decoration: none;">Contact Support</a>
            </p>
        </footer>
    </div>

    <script>
        let languages = [];
        const templates = <?php echo json_encode($templates); ?>;
        
        // Load supported languages on page load
        async function loadLanguages() {
            try {
                const formData = new FormData();
                formData.append('action', 'get_languages');
                
                const response = await fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                });
                
                languages = await response.json();
                
                const languageSelect = document.getElementById('language');
                languageSelect.innerHTML = '<option value="">Select Language</option>';
                
                // Get unique languages
                const uniqueLanguages = [...new Set(languages.map(l => l.language))];
                uniqueLanguages.sort().forEach(lang => {
                    const option = document.createElement('option');
                    option.value = lang;
                    option.textContent = lang.charAt(0).toUpperCase() + lang.slice(1);
                    languageSelect.appendChild(option);
                });
                
                // Set default to Python
                languageSelect.value = 'python';
                onLanguageChange();
            } catch (error) {
                document.getElementById('output').value = 'Error loading languages: ' + error.message;
            }
        }
        
        // Update versions when language changes
        function onLanguageChange() {
            const language = document.getElementById('language').value;
            const versionSelect = document.getElementById('version');
            
            versionSelect.innerHTML = '<option value="">Select version</option>';
            
            if (language) {
                const versions = languages.filter(l => l.language === language);
                versions.forEach(v => {
                    const option = document.createElement('option');
                    option.value = v.version;
                    option.textContent = v.version;
                    versionSelect.appendChild(option);
                });
                
                if (versions.length > 0) {
                    versionSelect.value = versions[0].version;
                }
                
                // Load template if available
                if (templates[language]) {
                    document.getElementById('code').value = templates[language];
                }
            }
        }
        
        // Run code
        async function runCode() {
            const language = document.getElementById('language').value;
            const version = document.getElementById('version').value;
            const code = document.getElementById('code').value;
            const stdin = document.getElementById('stdin').value;
            
            if (!language || !version) {
                alert('Please select a language and version');
                return;
            }
            
            if (!code.trim()) {
                alert('Please enter some code');
                return;
            }
            
            const runBtn = document.getElementById('runBtn');
            const loading = document.getElementById('loading');
            const output = document.getElementById('output');
            
            runBtn.disabled = true;
            loading.classList.add('active');
            output.value = '';
            
            try {
                const formData = new FormData();
                formData.append('action', 'execute');
                formData.append('language', language);
                formData.append('version', version);
                formData.append('code', code);
                formData.append('stdin', stdin);
                
                const response = await fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.error) {
                    output.value = 'Error: ' + result.error;
                } else {
                    let outputText = '';
                    
                    if (result.run && result.run.output) {
                        outputText += result.run.output;
                    }
                    
                    if (result.run && result.run.stderr) {
                        outputText += '\n--- STDERR ---\n' + result.run.stderr;
                    }
                    
                    if (result.compile && result.compile.output) {
                        outputText += '\n--- COMPILE OUTPUT ---\n' + result.compile.output;
                    }
                    
                    output.value = outputText || 'No output';
                }
            } catch (error) {
                output.value = 'Error: ' + error.message;
            } finally {
                runBtn.disabled = false;
                loading.classList.remove('active');
            }
        }
        
        // Clear all fields
        function clearAll() {
            document.getElementById('code').value = '';
            document.getElementById('stdin').value = '';
            document.getElementById('output').value = '';
        }
        
        // Load languages on page load
        loadLanguages();
    </script>

</body>
</html>