<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = $_POST['fullName'];
    $qualification = $_POST['qualification'];
    $experience = $_POST['experience'];
    $mobile = $_POST['mobile'];
    $email = $_POST['email'];
    
    // Validate fields (you can add more validation as needed)
    if (empty($fullName) || empty($qualification) || empty($experience) || empty($mobile) || empty($email)) {
        die('Please fill in all required fields.');
    }

    // File upload handling
    if (isset($_FILES['resume']) && $_FILES['resume']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['resume']['tmp_name'];
        $fileName = $_FILES['resume']['name'];
        $fileSize = $_FILES['resume']['size'];
        $fileType = $_FILES['resume']['type'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));

        $allowedfileExtensions = array('pdf', 'doc', 'docx');
        if (in_array($fileExtension, $allowedfileExtensions)) {
            $uploadFileDir = 'uploaded_resumes/';
            $dest_path = $uploadFileDir . $fileName;

            if(move_uploaded_file($fileTmpPath, $dest_path)) {
                $message = "File is successfully uploaded.\n";
            } else {
                $message = "There was some error moving the file to upload directory.\n";
                die($message);
            }
        } else {
            die('Upload failed. Allowed file types: ' . implode(',', $allowedfileExtensions));
        }
    } else {
        die('Please upload your resume.');
    }

    // Email sending
    $to = 'your-email@example.com'; // Replace with your email address
    $subject = 'New Job Application: ' . $fullName;

    $body = "Name: $fullName\n";
    $body .= "Qualification: $qualification\n";
    $body .= "Experience: $experience\n";
    $body .= "Mobile: $mobile\n";
    $body .= "Email: $email\n";
    $body .= "\nResume is attached to this email.\n";

    $headers = "From: $email";

    // Boundary for file attachment
    $boundary = md5("sanwebe");
    $headers .= "\nMIME-Version: 1.0\nContent-Type: multipart/mixed; boundary = $boundary\n\n";
    $body .= "--$boundary\n";
    $body .= "Content-Type: text/plain; charset=UTF-8\n";
    $body .= "Content-Transfer-Encoding: base64\n\n";
    $body .= chunk_split(base64_encode($body));

    // Attachment
    $file = file_get_contents($dest_path);
    $body .= "--$boundary\n";
    $body .= "Content-Type: $fileType; name=\"$fileName\"\n";
    $body .= "Content-Disposition: attachment; filename=\"$fileName\"\n";
    $body .= "Content-Transfer-Encoding: base64\n\n";
    $body .= chunk_split(base64_encode($file));
    $body .= "--$boundary--";

    // Send email
    if (mail($to, $subject, $body, $headers)) {
        echo 'Email sent successfully.';
    } else {
        echo 'Email sending failed.';
    }
} else {
    echo 'Invalid request.';
}
?>
