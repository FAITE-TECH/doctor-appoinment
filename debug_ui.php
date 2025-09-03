<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UI Debug - Admin Controls</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">
    <h1 class="text-3xl font-bold mb-6">UI Debug - Admin Controls Visibility</h1>
    
    <div class="bg-white p-6 rounded-lg shadow-lg mb-6">
        <h2 class="text-xl font-semibold mb-4">Current Authentication Status</h2>
        <div id="authStatus">Checking...</div>
    </div>
    
    <div class="bg-white p-6 rounded-lg shadow-lg mb-6">
        <h2 class="text-xl font-semibold mb-4">Admin Controls Test</h2>
        <div id="adminControls" class="hidden" data-auth="admin">
            <div class="flex justify-center space-x-4">
                <button id="addDoctorBtn" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors">
                    + Add New Doctor
                </button>
                <a href="./Frontend/pages/admin/doctors.html" class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors">
                    Manage Doctors (Admin Panel)
                </a>
            </div>
        </div>
        <div id="controlsStatus">Controls not visible</div>
    </div>
    
    <div class="bg-white p-6 rounded-lg shadow-lg mb-6">
        <h2 class="text-xl font-semibold mb-4">JavaScript Debug Info</h2>
        <div id="debugInfo"></div>
    </div>
    
    <div class="bg-white p-6 rounded-lg shadow-lg">
        <h2 class="text-xl font-semibold mb-4">Manual Test</h2>
        <button onclick="showControls()" class="bg-green-600 text-white px-4 py-2 rounded mr-2">Show Controls</button>
        <button onclick="hideControls()" class="bg-red-600 text-white px-4 py-2 rounded mr-2">Hide Controls</button>
        <button onclick="checkAuth()" class="bg-blue-600 text-white px-4 py-2 rounded">Check Auth</button>
    </div>

    <script>
        // Debug functions
        function showControls() {
            document.getElementById('adminControls').classList.remove('hidden');
            document.getElementById('controlsStatus').innerHTML = '<span style="color: green;">✅ Controls manually shown</span>';
        }
        
        function hideControls() {
            document.getElementById('adminControls').classList.add('hidden');
            document.getElementById('controlsStatus').innerHTML = '<span style="color: red;">❌ Controls manually hidden</span>';
        }
        
        function checkAuth() {
            fetch('/doctor-appoinment/Backend/api/auth.php?action=me')
                .then(response => response.json())
                .then(data => {
                    document.getElementById('authStatus').innerHTML = `
                        <pre>${JSON.stringify(data, null, 2)}</pre>
                    `;
                    
                    if (data.authenticated && data.user.role === 'admin') {
                        document.getElementById('adminControls').classList.remove('hidden');
                        document.getElementById('controlsStatus').innerHTML = '<span style="color: green;">✅ Admin controls should be visible</span>';
                    } else {
                        document.getElementById('adminControls').classList.add('hidden');
                        document.getElementById('controlsStatus').innerHTML = '<span style="color: red;">❌ Not admin - controls hidden</span>';
                    }
                })
                .catch(error => {
                    document.getElementById('authStatus').innerHTML = `<span style="color: red;">Error: ${error.message}</span>`;
                });
        }
        
        // Auto-check on page load
        document.addEventListener('DOMContentLoaded', function() {
            checkAuth();
            
            // Debug info
            const debugInfo = document.getElementById('debugInfo');
            debugInfo.innerHTML = `
                <p><strong>Page loaded:</strong> ${new Date().toLocaleString()}</p>
                <p><strong>Admin controls element exists:</strong> ${document.getElementById('adminControls') ? 'Yes' : 'No'}</p>
                <p><strong>Admin controls visible:</strong> ${!document.getElementById('adminControls').classList.contains('hidden') ? 'Yes' : 'No'}</p>
                <p><strong>Data-auth attribute:</strong> ${document.getElementById('adminControls').getAttribute('data-auth')}</p>
            `;
        });
    </script>
</body>
</html>
