<!DOCTYPE html>
<html>
<head>
    <title>Chunked Upload</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/min/dropzone.min.css">
</head>
<body>
<form id="my-dropzone" class="dropzone">
    @csrf
    <div class="fallback">
        <input name="file" type="file" multiple />
    </div>
    <button id="start-upload">Start Upload</button>
</form>



    <script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/min/dropzone.min.js"></script>
    <script>
        // Initialize Dropzone.js
        Dropzone.options.myDropzone = {
    paramName: "file",
    chunking: true,
    forceChunking: true,
    chunkSize: 1000000, // Set your desired chunk size
    url: "{{ route('upload.chunk') }}",
    autoProcessQueue: false, // Disable auto upload
    init: function() {
        var myDropzone = this;

        // Function to get uniqueIdentifier
        function getUniqueIdentifier() {
            // Implement logic to generate a unique identifier, e.g., session ID
            return 'your_unique_identifier';
        }

        // Add event handler for the "Start Upload" button
        document.getElementById("start-upload").addEventListener("click", function() {
            // Generate a uniqueIdentifier (e.g., timestamp)
            var uniqueIdentifier = getUniqueIdentifier();
            
            // Iterate through the files and send each chunk with additional data
            myDropzone.files.forEach(function(file, index) {
                // Set the uniqueIdentifier and chunkNumber as additional data
                file.upload.uuid = uniqueIdentifier;
                file.upload.chunkIndex = index + 1; // Add 1 to make the index 1-based

                // Send the chunk
                file.upload.send();
            });
        });

        this.on("success", function(file, response) {
            // Handle successful upload here
            console.log("Upload success", file, response);
            if (response && response.success) {
                // The upload was successful, you can perform further actions
            }
        });

        this.on("error", function(file, errorMessage) {
            // Handle upload error here
            console.error("Upload error", file, errorMessage);
        });

        this.on("complete", function(file) {
            // Handle completion of the upload if needed
        });
    }
};



    </script>
</body>
</html>
