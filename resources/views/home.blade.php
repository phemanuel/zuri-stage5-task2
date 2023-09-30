<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title>Upload Video</title>
</head>
<body>
<div id="alertMessage" style="display: none;">
        <p id="alertText"></p>
    </div>
    <form  action="{{ route('recording.save') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <table>
<tr>
    <td>
      <label for="Video Title">Video Title</label>
        <input type="text" name="video_title">  
    </td>
</tr>
<tr>
    <td>
    <label for="Video Description">Description</label>        
        <textarea name="video_description" id="" cols="20" rows="2"></textarea>
    </td>
</tr>
    <tr>
    <td>
    <label for="Video File">Video File</label>
    <input type="file" name="file" id="fileInput" onchange="checkFileSize()">
    </td>
</tr>
<tr>
    <td>
    <input type="submit" name="" value="Upload Video">
    </td>
</tr>
        </table>
    </form>

    
</body>
</html>
<script>
        function checkFileSize() {
            const fileInput = document.getElementById('fileInput');
            const maxFileSize = 20000; // 20MB in KB
            const alertMessage = document.getElementById('alertMessage');
            const alertText = document.getElementById('alertText');

            if (fileInput.files.length > 0) {
                const fileSize = fileInput.files[0].size / 1024; // Convert to KB
                if (fileSize > maxFileSize) {
                    alertText.textContent = 'File is too large. Maximum allowed size is 20MB.';
                    alertMessage.style.display = 'block';
                    fileInput.value = ''; // Clear the input field
                } else {
                    alertMessage.style.display = 'none'; // Hide the alert message
                }
            } else {
                alertMessage.style.display = 'none'; // Hide the alert message
            }
        }
    </script>