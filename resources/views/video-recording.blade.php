<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Screen Recordings</title>
</head>
<body>
<h1>All Videos</h1>

<div class="video-container">
    @foreach ($screenRecordings as $screenRecording)
        <div class="video">
            <h2>{{ $screenRecording->video_title }}</h2>
            <p>{{ $screenRecording->video_description }}</p>
            <video width="500" height="300" controls>
                <source src="{{  $screenRecording->video_url }}" type="video/mp4">
                Your browser does not support the video tag.
            </video>
        </div>
    @endforeach
</div>
</body>
</html>