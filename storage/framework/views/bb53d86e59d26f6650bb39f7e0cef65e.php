<!DOCTYPE html>
<html>
<head>
    <title>Scan Document</title>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>
<body>
    <div class="container mx-auto p-8">
        <h1 class="text-2xl font-bold mb-4">Scanner Interface</h1>
        
        <div class="bg-white rounded-lg shadow p-6">
            <p class="mb-4">Document will be saved as: <strong><?php echo e(session('scan_filename')); ?></strong></p>
            
            <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center">
                <p class="text-gray-600">Scanner Ready</p>
                <p class="text-sm text-gray-500 mt-2">Place document in scanner and click "Start Scan"</p>
            </div>
            
            <div class="mt-6 flex gap-4">
                <button onclick="startScan()" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                    Start Scan
                </button>
                <a href="/admin" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                    Cancel
                </a>
            </div>
        </div>
    </div>
    
    <script>
    function startScan() {
        alert('In production, this would trigger the scanner.\n\nFor testing, create a test PDF:');
        
        // For demo - create test PDF using browser
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();
        doc.text("Scanned Document", 10, 10);
        doc.text("Date: " + new Date().toLocaleString(), 10, 20);
        doc.save('<?php echo e(session('scan_filename')); ?>');
        
        // Redirect back
        setTimeout(() => {
            window.location.href = '/admin';
        }, 1000);
    }
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
</body>
</html><?php /**PATH C:\Users\Reymart Cabal\Desktop\Project\Files Organizer\doc-manager\resources\views/scan/form.blade.php ENDPATH**/ ?>