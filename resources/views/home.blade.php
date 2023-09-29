<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title>Upload Video</title>
</head>
<body>
    <form  action="{{ route('record.save') }}" method="POST" enctype="multipart/form-data">
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
    <input type="file" name="file">
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
