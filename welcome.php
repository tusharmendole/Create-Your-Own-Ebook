<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PDF eBook Creator Pro</title>
    <script src="https://unpkg.com/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .toolbar-btn {
            @apply px-3 py-2 rounded-lg transition-all duration-200 text-gray-700 font-medium hover:opacity-80;
        }

        #editor img,
        #preview img {
            max-width: 100%;
            height: auto;
        }

        #editor,
        #preview {
            min-height: 500px;
            max-height: 700px;
        }

        .page-break {
            page-break-after: always;
            border-bottom: 2px dashed #e2e8f0;
            margin: 20px 0;
            padding-bottom: 20px;
        }

        [contenteditable] {
            outline: none;
        }
    </style>
</head>

<body class="bg-gradient-to-br from-purple-100 via-pink-100 to-blue-100 min-h-screen font-[Poppins]">
    <div class="container mx-auto p-4 max-w-6xl">
        <header
            class="text-center mb-8 bg-white p-6 rounded-xl shadow-lg transform hover:scale-105 transition-transform duration-300">
            <h1
                class="text-4xl font-bold bg-gradient-to-r from-purple-600 via-pink-500 to-blue-600 bg-clip-text text-transparent mb-2">
                eBook Creator </h1>
            <p class="text-gray-600">Create stunning multi-page eBooks with comprehensive formatting options</p>
        </header>

        <div class="grid md:grid-cols-2 gap-6">
            <!-- Editor Section -->
            <div class="bg-white p-6 rounded-xl shadow-lg border-t-4 border-purple-500">
                <div class="mb-4 space-y-4">
                    <!-- Text Styling -->
                    <div class="flex flex-wrap gap-2 pb-2 border-b">
                        <button onclick="applyFormat('bold')" class="toolbar-btn bg-purple-100" title="Bold"><i
                                class="bi bi-type-bold"></i></button>
                        <button onclick="applyFormat('italic')" class="toolbar-btn bg-blue-100" title="Italic"><i
                                class="bi bi-type-italic"></i></button>
                        <button onclick="applyFormat('underline')" class="toolbar-btn bg-indigo-100"
                            title="Underline"><i class="bi bi-type-underline"></i></button>
                        <select id="fontSize" onchange="applyFontSize()" class="toolbar-btn bg-green-100">
                            <option value="1">Tiny</option>
                            <option value="2">Small</option>
                            <option value="3">Normal</option>
                            <option value="4" selected>Large</option>
                            <option value="5">Huge</option>
                        </select>
                        <input type="color" id="textColor" onchange="applyTextColor()" class="toolbar-btn w-10 h-10 p-1"
                            title="Text Color">
                    </div>

                    <!-- Horizontal Alignment -->
                    <div class="flex flex-wrap gap-2 pb-2 border-b">
                        <button onclick="applyAlignment('left')" class="toolbar-btn bg-pink-100" title="Align Left"><i
                                class="bi bi-text-left"></i></button>
                        <button onclick="applyAlignment('center')" class="toolbar-btn bg-yellow-100"
                            title="Align Center"><i class="bi bi-text-center"></i></button>
                        <button onclick="applyAlignment('right')" class="toolbar-btn bg-orange-100"
                            title="Align Right"><i class="bi bi-text-right"></i></button>
                        <button onclick="applyAlignment('justify')" class="toolbar-btn bg-red-100" title="Justify"><i
                                class="bi bi-justify"></i></button>
                    </div>

                    <!-- Vertical Alignment -->
                    <div class="flex flex-wrap gap-2 pb-2 border-b">
                        <button onclick="applyVerticalAlign('top')" class="toolbar-btn bg-violet-100"
                            title="Align Top"><i class="bi bi-align-top"></i></button>
                        <button onclick="applyVerticalAlign('middle')" class="toolbar-btn bg-fuchsia-100"
                            title="Align Middle"><i class="bi bi-align-middle"></i></button>
                        <button onclick="applyVerticalAlign('bottom')" class="toolbar-btn bg-rose-100"
                            title="Align Bottom"><i class="bi bi-align-bottom"></i></button>
                    </div>

                    <!-- Media Controls -->
                    <div class="flex flex-wrap gap-2">
                        <input type="file" id="imageInput" accept="image/*" class="hidden"
                            onchange="handleImageUpload(event)">
                        <button onclick="document.getElementById('imageInput').click()" class="toolbar-btn bg-teal-100">
                            <i class="bi bi-upload"></i> Upload Image
                        </button>
                        <button onclick="insertImage()" class="toolbar-btn bg-cyan-100">
                            <i class="bi bi-image"></i> Insert URL Image
                        </button>
                        <button onclick="insertPageBreak()" class="toolbar-btn bg-emerald-100">
                            <i class="bi bi-file-break"></i> New Page
                        </button>
                    </div>
                </div>

                <div id="editor" contenteditable="true"
                    class="min-h-[500px] border border-gray-200 rounded p-4 focus:outline-none focus:ring-2 focus:ring-purple-500 overflow-y-auto bg-white">
                </div>
            </div>

            <!-- Preview Section -->
            <div class="bg-white p-6 rounded-xl shadow-lg border-t-4 border-blue-500">
                <h2 class="text-xl font-semibold mb-4 text-blue-600">Live Preview</h2>
                <div id="preview" class="min-h-[500px] border border-gray-200 rounded p-4 overflow-y-auto bg-white">
                </div>
            </div>
        </div>

        <div class="mt-6 text-center space-x-4">
            <button onclick="generatePDF()"
                class="bg-gradient-to-r from-purple-600 via-pink-500 to-blue-600 text-white px-8 py-3 rounded-lg hover:opacity-90 transition-all transform hover:scale-105 shadow-lg">
                Generate PDF <i class="bi bi-file-earmark-pdf ml-2"></i>
            </button>
            <button onclick="clearContent()"
                class="bg-gradient-to-r from-red-500 to-pink-500 text-white px-8 py-3 rounded-lg hover:opacity-90 transition-all transform hover:scale-105 shadow-lg">
                Clear Content <i class="bi bi-trash ml-2"></i>
            </button>
        </div>
    </div>

    <div style="text-align: center; margin-top: 20px;">
        <a href="logout.php"
            style="background-color: #ff4d4d; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;">Logout</a>
    </div>



    <script>
        const editor = document.getElementById('editor');
        const preview = document.getElementById('preview');

        editor.addEventListener('input', updatePreview);

        function updatePreview() {
            preview.innerHTML = editor.innerHTML;
        }

        // Apply text formatting (Bold, Italic, Underline)
        function applyFormat(command) {
            document.execCommand(command, false, null);
            editor.focus();
        }

        // Apply text size
        function applyFontSize() {
            const size = document.getElementById('fontSize').value;
            document.execCommand('fontSize', false, size);
            editor.focus();
        }

        // Apply text color
        function applyTextColor() {
            const color = document.getElementById('textColor').value;
            document.execCommand('foreColor', false, color);
            editor.focus();
        }

        // Apply horizontal alignment
        function applyAlignment(alignment) {
            document.execCommand('justify' + alignment.charAt(0).toUpperCase() + alignment.slice(1));
            editor.focus();
        }

        // Apply vertical alignment (Wrap content in a span)
        function applyVerticalAlign(position) {
            const selection = window.getSelection();
            if (selection.rangeCount) {
                const range = selection.getRangeAt(0);
                const span = document.createElement('span');
                span.style.verticalAlign = position;
                range.surroundContents(span);
            }
            updatePreview();
        }

        // Insert page break (
        // Insert page break (creates a new page in the editor)
        function insertPageBreak() {
            const pageBreak = document.createElement('div');
            pageBreak.className = 'page-break';
            pageBreak.innerHTML = '<p class="text-center text-gray-500">--- Page Break ---</p>';
            editor.appendChild(pageBreak);
            updatePreview();
        }

        // Handle image upload (allows users to upload an image from their device)
        function handleImageUpload(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.className = 'my-4 mx-auto';
                    img.style.maxWidth = '100%';  // Ensure the image is responsive
                    editor.appendChild(img);
                    updatePreview();
                };
                reader.readAsDataURL(file);
            }
        }

        // Insert an image by URL
        function insertImage() {
            const url = prompt('Enter image URL:');
            if (url) {
                const img = document.createElement('img');
                img.src = url;
                img.className = 'my-4 mx-auto';
                img.style.maxWidth = '100%';  // Ensure the image is responsive
                editor.appendChild(img);
                updatePreview();
            }
        }

        // Generate PDF
        async function generatePDF() {
            const { jsPDF } = window.jspdf;
            const pdf = new jsPDF('p', 'mm', 'a4'); // Use A4 size in mm for better precision

            // Split content into separate pages (based on page breaks)
            const contents = editor.innerHTML.split('<div class="page-break">');

            // Loop through the contents and create each page in the PDF
            for (let i = 0; i < contents.length; i++) {
                if (i > 0) pdf.addPage(); // Add a new page for subsequent content

                // Create a temporary container for capturing the page content
                const tempContainer = document.createElement('div');
                tempContainer.innerHTML = contents[i];
                tempContainer.style.width = '595px';  // A4 width in px (approximately 595px)
                tempContainer.style.padding = '10px'; // Apply padding for consistent spacing
                tempContainer.style.fontFamily = 'Poppins, sans-serif';  // Ensure the correct font family

                // Apply any other CSS styles to match the live preview
                tempContainer.style.fontSize = '14px'; // Example of font size for consistency

                // Append the temporary container to the body (it will be hidden)
                document.body.appendChild(tempContainer);

                // Use html2canvas to capture the content of the temporary container
                const canvas = await html2canvas(tempContainer, { scale: 2 });
                const imgData = canvas.toDataURL('image/jpeg', 1.0); // Capture as high-quality JPEG

                // Add the image data to the PDF (A4 size: 210mm x 297mm, scaling it to fit)
                pdf.addImage(imgData, 'JPEG', 0, 0, 210, 297); // A4 dimensions in mm (210mm x 297mm)

                // Remove the temporary container after use
                document.body.removeChild(tempContainer);
            }

            // Save the generated PDF
            pdf.save('ebook.pdf');
        }


        // Initialize with placeholder content (for example)
        editor.innerHTML = `
        <h1 style="font-size: 24px; color: #6366f1; text-align: center;">Welcome to Enhanced eBook Creator Pro</h1>
        <p style="text-align: justify;">Start creating professional multi-page documents with our comprehensive formatting tools. You can:</p>
        <ul style="margin-left: 20px;">
        <li>Use advanced text formatting</li>
            <li>Apply vertical and horizontal alignment</li>
            <li>Insert images with flexible positioning</li>
            <li>Create multi-page documents with page breaks</li>
            <li>Generate high-quality PDFs</li>
        </ul>
        <div class="page-break"><p class="text-center text-gray-500">--- Page Break ---</p></div>
        <h2 style="color: #8b5cf6;">This is a Second Page Example</h2>
        <p>Each page will be properly formatted in the final PDF output.</p>`;

        updatePreview();
    </script>
</body>

</html>